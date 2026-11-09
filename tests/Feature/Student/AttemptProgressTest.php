<?php

namespace Tests\Feature\Student;

use App\Models\ActivityLog;
use App\Models\Attempt;
use App\Models\Exam;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AttemptProgressTest extends TestCase
{
    use RefreshDatabase;

    private User $student;

    private Attempt $attempt;

    protected function setUp(): void
    {
        parent::setUp();

        // Chặn việc xóa nhầm dữ liệu dev nếu phpunit.xml thiếu MONGODB_DATABASE.
        $this->assertSame('exam_testing', DB::connection('mongodb')->getDatabase()->getDatabaseName());

        Attempt::truncate();
        ActivityLog::truncate();

        $this->student = User::factory()->create(['role' => 'student']);

        $exam = Exam::create(['name' => 'Toán', 'duration_minutes' => 30]);
        foreach (range(1, 3) as $number) {
            $question = $exam->questions()->create(['content' => "Câu {$number}"]);
            $question->options()->create(['content' => "Sai {$number}", 'is_correct' => false]);
            $question->options()->create(['content' => "Đúng {$number}", 'is_correct' => true]);
        }

        $this->actingAs($this->student)->post("/exams/{$exam->id}/attempts");
        $this->attempt = Attempt::where('exam_id', $exam->id)->firstOrFail();
    }

    public function test_in_progress_page_shows_progress_bar(): void
    {
        $this->actingAs($this->student)->get("/attempts/{$this->attempt->id}")
            ->assertOk()
            ->assertSee('role="progressbar"', false)
            ->assertSee('aria-valuemax="3"', false);
    }

    public function test_question_badge_shows_position(): void
    {
        $this->actingAs($this->student)->get("/attempts/{$this->attempt->id}")
            ->assertOk()
            ->assertSee('Câu 1 / 3');
    }
}
