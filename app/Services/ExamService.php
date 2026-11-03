<?php

namespace App\Services;

use App\Models\Exam;
use App\Models\Question;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Cache;
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
     * Số phiên bản của cache danh sách đề. Tăng số này thì mọi trang đã cache đều không còn được đọc.
     */
    private const VERSION_KEY = 'exams.available.version';

    /**
     * Danh sách đề cho student, bỏ các đề chưa có câu hỏi. Web và API cùng dùng, cache 10 phút cho mỗi trang.
     *
     * @return LengthAwarePaginator<int, Exam>
     */
    public function paginateAvailable(): LengthAwarePaginator
    {
        $key = 'exams.available.v'.Cache::get(self::VERSION_KEY, 0).'.page.'.Paginator::resolveCurrentPage();

        return Cache::remember($key, now()->addMinutes(10), fn () => Exam::has('questions')
            ->withCount('questions')
            ->latest('id')
            ->paginate(10))
            // Paginator giữ đường dẫn lúc được tạo: trang cache từ /exams phải đổi link sang /api/exams khi API gọi.
            ->withPath(Paginator::resolveCurrentPath());
    }

    /**
     * Gọi từ ExamListCacheObserver khi đề hoặc câu hỏi thay đổi.
     */
    public static function forgetAvailableCache(): void
    {
        Cache::forever(self::VERSION_KEY, Cache::get(self::VERSION_KEY, 0) + 1);
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
