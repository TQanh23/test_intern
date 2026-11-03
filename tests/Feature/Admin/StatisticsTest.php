<?php

namespace Tests\Feature\Admin;

use App\Models\Attempt;
use App\Models\User;
use App\Services\StatisticsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Attempt được tạo thẳng với điểm biết trước, nên kết quả mong đợi tính tay được.
 * exam_id trong các test này không có trong MySQL: thống kê chỉ đọc snapshot, đề đã xóa vẫn tính.
 */
class StatisticsTest extends TestCase
{
    use RefreshDatabase;

    private StatisticsService $statistics;

    protected function setUp(): void
    {
        parent::setUp();

        // Chặn việc xóa nhầm dữ liệu dev nếu phpunit.xml thiếu MONGODB_DATABASE.
        $this->assertSame('exam_testing', DB::connection('mongodb')->getDatabase()->getDatabaseName());

        Attempt::truncate();

        $this->statistics = app(StatisticsService::class);
    }

    /**
     * @param  list<array<string, mixed>>  $questions
     */
    private function submitted(int $userId, int $examId, string $examName, int $score, int $total, array $questions = [], ?Carbon $submittedAt = null): void
    {
        Attempt::create([
            'user_id' => $userId,
            'exam_id' => $examId,
            'exam_name' => $examName,
            'status' => 'submitted',
            'submitted_at' => $submittedAt ?? now(),
            'score' => $score,
            'total' => $total,
            'questions' => $questions,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function question(int $id, bool $correct, ?int $selected = 1): array
    {
        return ['question_id' => $id, 'content' => "Câu {$id}", 'selected_option_id' => $selected, 'is_correct' => $correct];
    }

    public function test_average_by_exam_uses_percent_and_ignores_unsubmitted(): void
    {
        $this->submitted(1, 1, 'Toán', 2, 4);
        $this->submitted(2, 1, 'Toán', 4, 4);
        $this->submitted(1, 2, 'Anh', 1, 2);
        Attempt::create(['user_id' => 3, 'exam_id' => 2, 'exam_name' => 'Anh', 'status' => 'in_progress', 'score' => null, 'total' => null]);

        $rows = $this->statistics->averageByExam();

        $this->assertSame([1, 2], $rows->pluck('_id')->all());
        $this->assertSame(['Toán', 'Anh'], $rows->pluck('exam_name')->all());
        $this->assertSame([2, 1], $rows->pluck('attempts')->all());
        $this->assertEqualsWithDelta([75, 50], $rows->pluck('avg_percent')->all(), 0.001);
    }

    public function test_top_students_are_ranked_by_average_percent_and_limited_to_ten(): void
    {
        $many = User::factory()->create(['name' => 'Nhiều bài']);
        $one = User::factory()->create(['name' => 'Một bài']);

        // Cả hai đều 100%, người nộp nhiều bài hơn xếp trước.
        $this->submitted($one->id, 1, 'Toán', 4, 4);
        $this->submitted($many->id, 1, 'Toán', 4, 4);
        $this->submitted($many->id, 2, 'Anh', 2, 2);

        // HS 1 đạt 90%, HS 2 đạt 80%, ..., HS 9 đạt 10%.
        foreach (range(1, 9) as $number) {
            $student = User::factory()->create(['name' => "HS {$number}"]);
            $this->submitted($student->id, 1, 'Toán', 10 - $number, 10);
        }

        $rows = $this->statistics->topStudents();

        $this->assertSame(
            ['Nhiều bài', 'Một bài', 'HS 1', 'HS 2', 'HS 3', 'HS 4', 'HS 5', 'HS 6', 'HS 7', 'HS 8'],
            $rows->pluck('name')->all(),
        );
        $this->assertSame([2, 1], $rows->take(2)->pluck('attempts')->all());
        $this->assertEqualsWithDelta(20, $rows->last()['avg_percent'], 0.001);
    }

    public function test_deleted_student_is_still_ranked(): void
    {
        $this->submitted(999, 1, 'Toán', 1, 1);

        $this->assertSame(['Tài khoản đã xóa'], $this->statistics->topStudents()->pluck('name')->all());
    }

    public function test_most_wrong_questions_count_blank_as_wrong(): void
    {
        $this->submitted(1, 1, 'Toán', 2, 3, [$this->question(10, false), $this->question(20, true), $this->question(30, true)]);
        $this->submitted(2, 1, 'Toán', 1, 3, [$this->question(10, false, null), $this->question(20, false), $this->question(30, true)]);
        $this->submitted(3, 1, 'Toán', 3, 3, [$this->question(10, true), $this->question(20, true), $this->question(30, true)]);

        $rows = $this->statistics->mostWrongQuestions();

        $this->assertSame([10, 20], $rows->pluck('_id')->all());
        $this->assertSame([2, 1], $rows->pluck('wrong')->all());
        $this->assertSame([3, 3], $rows->pluck('appearances')->all());
        $this->assertSame(['Câu 10', 'Câu 20'], $rows->pluck('content')->all());
    }

    public function test_renamed_exam_shows_the_name_from_the_latest_submission(): void
    {
        // Ghi bản mới trước để chắc kết quả theo submitted_at, không theo thứ tự ghi.
        $this->submitted(1, 1, 'Tên mới', 0, 1, [$this->question(10, false)], now());
        $this->submitted(2, 1, 'Tên cũ', 0, 1, [$this->question(10, false)], now()->subDay());

        $this->assertSame(['Tên mới'], $this->statistics->averageByExam()->pluck('exam_name')->all());
        $this->assertSame(['Tên mới'], $this->statistics->mostWrongQuestions()->pluck('exam_name')->all());
    }

    public function test_admin_sees_statistics_page(): void
    {
        $student = User::factory()->create(['name' => 'Bình']);
        $this->submitted($student->id, 1, 'Toán', 3, 4, [$this->question(10, false)]);

        $this->actingAs(User::factory()->create(['role' => 'admin']))
            ->get('/admin/statistics')
            ->assertOk()
            ->assertSeeInOrder(['Toán', '75%', 'Bình', '75%', 'Câu 10', '1/1']);
    }

    public function test_student_cannot_open_statistics(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'student']))
            ->get('/admin/statistics')
            ->assertForbidden();
    }

    public function test_empty_statistics_page_shows_message(): void
    {
        $response = $this->actingAs(User::factory()->create(['role' => 'admin']))
            ->get('/admin/statistics')
            ->assertOk();

        $this->assertSame(3, substr_count($response->getContent(), 'Chưa có bài nộp nào.'));
    }
}
