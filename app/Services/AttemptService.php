<?php

namespace App\Services;

use App\Events\AttemptStarted;
use App\Events\AttemptSubmitted;
use App\Models\Attempt;
use App\Models\Exam;
use App\Models\Option;
use App\Models\Question;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use MongoDB\BSON\ObjectId;
use MongoDB\BSON\UTCDateTime;
use MongoDB\Driver\Exception\BulkWriteException;

class AttemptService
{
    /**
     * Trả về bài đang làm nếu còn hạn, nếu không thì tạo lượt mới với câu hỏi đã trộn. Thời hạn do server tính.
     */
    public function start(User $user, Exam $exam): Attempt
    {
        $open = $this->findOpen($user, $exam);

        if ($open && ! $open->isExpired()) {
            return $open;
        }

        // Bài hết giờ bị bỏ dở: chấm theo đáp án đã lưu để đóng bài, rồi mới mở bài mới.
        if ($open) {
            $this->submit($open, []);
        }

        $exam->load('questions.options');

        abort_if($exam->questions->isEmpty(), 422, 'Đề này chưa có câu hỏi.');

        try {
            $attempt = $this->create($user, $exam);
        } catch (BulkWriteException $e) {
            // 11000 là lỗi trùng unique index: một request khác vừa tạo bài cho đề này, dùng luôn bài đó.
            if ($e->getCode() !== 11000) {
                throw $e;
            }

            return $this->findOpen($user, $exam);
        }

        AttemptStarted::dispatch($attempt);

        return $attempt;
    }

    private function findOpen(User $user, Exam $exam): ?Attempt
    {
        return Attempt::where('user_id', $user->id)
            ->where('exam_id', $exam->id)
            ->where('status', 'in_progress')
            ->first();
    }

    private function create(User $user, Exam $exam): Attempt
    {
        $startedAt = now();

        return Attempt::create([
            'user_id' => $user->id,
            'exam_id' => $exam->id,
            'exam_name' => $exam->name,
            'duration_minutes' => $exam->duration_minutes,
            'status' => 'in_progress',
            'started_at' => $startedAt,
            'deadline_at' => $startedAt->copy()->addMinutes($exam->duration_minutes),
            'submitted_at' => null,
            'score' => null,
            'total' => null,
            'questions' => $exam->questions->shuffle()->map(fn (Question $question) => [
                'question_id' => $question->id,
                'content' => $question->content,
                'options' => $question->options->map(fn (Option $option) => [
                    'option_id' => $option->id,
                    'content' => $option->content,
                    'is_correct' => $option->is_correct,
                ])->all(),
                'selected_option_id' => null,
                'is_correct' => null,
            ])->all(),
        ]);
    }

    /**
     * Chấm các bài đã hết giờ mà học sinh không quay lại nộp, theo đáp án đã lưu. Trả về số bài đã chấm.
     */
    public function closeExpired(): int
    {
        // shortcut: lấy hết vào bộ nhớ, đủ dùng vì command chạy mỗi phút nên mỗi lần chỉ có ít bài; nhiều bài thì đổi sang lazyById().
        // Không dùng each(): each() phân trang theo offset, đổi status trong lúc lặp sẽ làm bỏ sót bài.
        $expired = Attempt::where('status', 'in_progress')->where('deadline_at', '<', now())->get();

        $closed = 0;

        foreach ($expired as $attempt) {
            try {
                $this->submit($attempt, []);
                $closed++;
            } catch (ValidationException) {
                // Học sinh vừa tự nộp cùng lúc, bài đã được chấm.
            }
        }

        return $closed;
    }

    /**
     * Lưu đáp án của một câu ngay khi học sinh chọn. option_id null nghĩa là bỏ chọn.
     */
    public function saveAnswer(Attempt $attempt, int $questionId, ?int $optionId): void
    {
        $question = collect($attempt->questions)->firstWhere('question_id', $questionId);

        if ($question === null) {
            $this->reject('Câu hỏi không thuộc bài này.');
        }

        if ($optionId !== null && ! collect($question['options'])->contains('option_id', $optionId)) {
            $this->reject('Đáp án không thuộc câu hỏi này.');
        }

        // Điều kiện status và deadline nằm trong cùng lệnh ghi, nên không ghi được vào bài đã nộp hoặc đã hết giờ.
        // Dùng matchedCount vì chọn lại đúng đáp án cũ thì không có gì thay đổi (modifiedCount = 0).
        $result = DB::connection('mongodb')->getCollection('attempts')->updateOne(
            [
                '_id' => new ObjectId($attempt->getKey()),
                'status' => 'in_progress',
                'deadline_at' => ['$gte' => new UTCDateTime(now())],
                'questions.question_id' => $questionId,
            ],
            ['$set' => ['questions.$.selected_option_id' => $optionId]],
        );

        if ($result->getMatchedCount() === 0) {
            $this->reject('Bài đã nộp hoặc đã hết giờ, không lưu được đáp án.');
        }
    }

    /**
     * Chấm bài theo snapshot. Client chỉ gửi lựa chọn, kết quả đúng sai do server tính.
     *
     * @param  array<int|string, int|string|null>  $answers  question_id => option_id
     */
    public function submit(Attempt $attempt, array $answers): Attempt
    {
        if ($attempt->status !== 'in_progress') {
            $this->reject('Bài này đã được nộp.');
        }

        $saved = array_column($attempt->questions, 'selected_option_id', 'question_id');

        // Hết giờ thì chỉ chấm đáp án đã autosave. Còn hạn thì đáp án gửi lên đè lên đáp án đã lưu, câu không gửi giữ đáp án đã lưu.
        $answers = $attempt->isExpired() ? $saved : $answers + $saved;

        $questions = array_map(
            fn (array $question) => $this->grade($question, $answers[$question['question_id']] ?? null),
            $attempt->questions,
        );

        // Lọc theo status để hai request nộp cùng lúc chỉ một request ghi được.
        $updated = Attempt::whereKey($attempt->getKey())
            ->where('status', 'in_progress')
            ->update([
                'status' => 'submitted',
                'submitted_at' => $attempt->fromDateTime(now()),
                'score' => count(array_filter(array_column($questions, 'is_correct'))),
                'total' => count($questions),
                'questions' => $questions,
            ]);

        if ($updated === 0) {
            $this->reject('Bài này đã được nộp.');
        }

        // Đọc lại để có điểm vừa lưu. Lệnh update ở trên không phát model event.
        $submitted = $attempt->fresh();

        AttemptSubmitted::dispatch($submitted);

        return $submitted;
    }

    /**
     * Đáp án chỉ được tính nếu thuộc đúng câu hỏi đó trong snapshot.
     *
     * @param  array<string, mixed>  $question
     * @return array<string, mixed>
     */
    private function grade(array $question, int|string|null $optionId): array
    {
        $selected = collect($question['options'])->firstWhere('option_id', (int) $optionId);

        return [
            ...$question,
            'selected_option_id' => $selected['option_id'] ?? null,
            'is_correct' => $selected['is_correct'] ?? false,
        ];
    }

    /**
     * Web tự quay lại trang kèm lỗi, API tự nhận 422.
     */
    private function reject(string $message): never
    {
        throw ValidationException::withMessages(['attempt' => $message]);
    }
}
