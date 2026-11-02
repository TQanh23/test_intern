<?php

namespace App\Services;

use App\Models\Attempt;
use App\Models\Exam;
use App\Models\Option;
use App\Models\Question;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class AttemptService
{
    /**
     * Tạo lượt làm bài mới với câu hỏi đã trộn. Thời hạn do server tính.
     */
    public function start(User $user, Exam $exam): Attempt
    {
        $exam->load('questions.options');

        abort_if($exam->questions->isEmpty(), 422, 'Đề này chưa có câu hỏi.');

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
     * Chấm bài theo snapshot. Client chỉ gửi lựa chọn, kết quả đúng sai do server tính.
     *
     * @param  array<int|string, int|string|null>  $answers  question_id => option_id
     */
    public function submit(Attempt $attempt, array $answers): void
    {
        if ($attempt->status !== 'in_progress') {
            $this->reject('Bài này đã được nộp.');
        }

        if ($attempt->isExpired()) {
            $this->reject('Đã hết giờ làm bài.');
        }

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
