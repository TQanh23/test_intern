<?php

namespace App\Services;

use App\Models\Exam;
use App\Models\Question;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class ExamService
{
    /**
     * Danh sách đề, tìm theo tên, kèm số câu hỏi của mỗi đề.
     *
     * @return LengthAwarePaginator<int, Exam>
     */
    public function paginate(string $search = ''): LengthAwarePaginator
    {
        return Exam::withCount('questions')
            ->when($search !== '', fn ($query) => $query->where('name', 'like', "%{$search}%"))
            ->latest('id')
            ->paginate(10)
            ->withQueryString();
    }

    /**
     * Danh sách đề cho student, bỏ các đề chưa có câu hỏi.
     *
     * @return LengthAwarePaginator<int, Exam>
     */
    public function paginateAvailable(): LengthAwarePaginator
    {
        return Exam::has('questions')
            ->withCount('questions')
            ->latest('id')
            ->paginate(10);
    }

    /**
     * @param  array{content: string, options: list<string>, correct_option: int|string}  $data
     */
    public function createQuestion(Exam $exam, array $data): Question
    {
        return DB::transaction(function () use ($exam, $data) {
            $question = $exam->questions()->create(['content' => $data['content']]);

            $this->createOptions($question, $data);

            return $question;
        });
    }

    /**
     * Xóa đáp án cũ rồi tạo lại. Bài đã làm không ảnh hưởng vì attempts chấm theo snapshot.
     *
     * @param  array{content: string, options: list<string>, correct_option: int|string}  $data
     */
    public function updateQuestion(Question $question, array $data): void
    {
        DB::transaction(function () use ($question, $data) {
            $question->update(['content' => $data['content']]);
            $question->options()->delete();

            $this->createOptions($question, $data);
        });
    }

    /**
     * @param  array{options: list<string>, correct_option: int|string}  $data
     */
    private function createOptions(Question $question, array $data): void
    {
        foreach ($data['options'] as $index => $content) {
            $question->options()->create([
                'content' => $content,
                'is_correct' => $index === (int) $data['correct_option'],
            ]);
        }
    }
}
