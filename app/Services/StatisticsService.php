<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Thống kê tính bằng aggregation của MongoDB trên `attempts`, chỉ đọc snapshot nên đề đã sửa hoặc xóa vẫn đúng.
 */
class StatisticsService
{
    /**
     * Tính theo tỷ lệ vì mỗi đề, thậm chí mỗi bài của cùng một đề, có thể có số câu khác nhau.
     */
    private const PERCENT = ['$multiply' => [['$divide' => ['$score', '$total']], 100]];

    /**
     * Tỷ lệ đúng trung bình của mỗi đề, cao nhất trước.
     *
     * @return Collection<int, array{_id: int, exam_name: string, attempts: int, avg_percent: float}>
     */
    public function averageByExam(): Collection
    {
        return $this->aggregate([
            ['$match' => ['status' => 'submitted']],
            // Sắp theo giờ nộp để $last lấy tên đề ở bài nộp gần nhất.
            ['$sort' => ['submitted_at' => 1]],
            ['$group' => [
                '_id' => '$exam_id',
                'exam_name' => ['$last' => '$exam_name'],
                'attempts' => ['$sum' => 1],
                'avg_percent' => ['$avg' => self::PERCENT],
            ]],
            ['$sort' => ['avg_percent' => -1, '_id' => 1]],
        ]);
    }

    /**
     * 10 học sinh có tỷ lệ đúng trung bình cao nhất. Bằng nhau thì ai nộp nhiều bài hơn xếp trước.
     *
     * @return Collection<int, array{_id: int, attempts: int, avg_percent: float, name: string}>
     */
    public function topStudents(): Collection
    {
        $rows = $this->aggregate([
            ['$match' => ['status' => 'submitted']],
            ['$group' => [
                '_id' => '$user_id',
                'attempts' => ['$sum' => 1],
                'avg_percent' => ['$avg' => self::PERCENT],
            ]],
            ['$sort' => ['avg_percent' => -1, 'attempts' => -1, '_id' => 1]],
            ['$limit' => 10],
        ]);

        // Một query lấy tên cho cả 10 dòng, không query theo từng dòng.
        $names = User::whereIn('id', $rows->pluck('_id'))->pluck('name', 'id');

        return $rows->map(fn (array $row) => [...$row, 'name' => $names[$row['_id']] ?? 'Tài khoản đã xóa']);
    }

    /**
     * 10 câu bị sai nhiều nhất. Câu bỏ trống tính là sai, giống lúc chấm điểm.
     *
     * @return Collection<int, array{_id: int, content: string, exam_name: string, wrong: int, appearances: int}>
     */
    public function mostWrongQuestions(): Collection
    {
        return $this->aggregate([
            ['$match' => ['status' => 'submitted']],
            ['$sort' => ['submitted_at' => 1]],
            ['$unwind' => '$questions'],
            ['$group' => [
                '_id' => '$questions.question_id',
                'content' => ['$last' => '$questions.content'],
                'exam_name' => ['$last' => '$exam_name'],
                'wrong' => ['$sum' => ['$cond' => ['$questions.is_correct', 0, 1]]],
                'appearances' => ['$sum' => 1],
            ]],
            ['$match' => ['wrong' => ['$gt' => 0]]],
            ['$sort' => ['wrong' => -1, '_id' => 1]],
            ['$limit' => 10],
        ]);
    }

    /**
     * Gọi thẳng collection để nhận mảng PHP thường. Attempt::raw() sẽ biến mỗi dòng thành model Attempt.
     *
     * @param  list<array<string, mixed>>  $pipeline
     */
    private function aggregate(array $pipeline): Collection
    {
        $cursor = DB::connection('mongodb')->getCollection('attempts')->aggregate($pipeline, [
            'typeMap' => ['root' => 'array', 'document' => 'array', 'array' => 'array'],
        ]);

        return collect($cursor);
    }
}
