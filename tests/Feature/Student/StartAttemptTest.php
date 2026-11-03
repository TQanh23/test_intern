<?php

namespace Tests\Feature\Student;

use App\Models\ActivityLog;
use App\Models\Attempt;
use App\Models\Exam;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class StartAttemptTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Chặn việc xóa nhầm dữ liệu dev nếu phpunit.xml thiếu MONGODB_DATABASE.
        $this->assertSame('exam_testing', DB::connection('mongodb')->getDatabase()->getDatabaseName());

        Attempt::truncate();
        ActivityLog::truncate();
    }

    private function student(): User
    {
        return User::factory()->create(['role' => 'student']);
    }

    private function createExam(int $questionCount = 2, string $name = 'Toán'): Exam
    {
        $exam = Exam::create(['name' => $name, 'duration_minutes' => 30]);

        foreach (range(1, $questionCount) as $number) {
            $question = $exam->questions()->create(['content' => "Câu {$number}"]);
            $question->options()->create(['content' => "Đúng {$number}", 'is_correct' => true]);
            $question->options()->create(['content' => "Sai {$number}", 'is_correct' => false]);
        }

        return $exam;
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get('/exams')->assertRedirect('/login');
    }

    public function test_admin_cannot_start_an_exam(): void
    {
        $exam = $this->createExam();
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->post("/exams/{$exam->id}/attempts")->assertForbidden();

        $this->assertSame(0, Attempt::count());
    }

    public function test_exam_list_hides_exams_without_questions(): void
    {
        $this->createExam(name: 'Đề có câu');
        Exam::create(['name' => 'Đề rỗng', 'duration_minutes' => 30]);

        $this->actingAs($this->student())->get('/exams')
            ->assertOk()
            ->assertSee('Đề có câu')
            ->assertDontSee('Đề rỗng');
    }

    public function test_starting_an_exam_creates_one_attempt(): void
    {
        $exam = $this->createExam();
        $student = $this->student();

        $response = $this->actingAs($student)->post("/exams/{$exam->id}/attempts");

        $attempt = Attempt::firstOrFail();

        $response->assertRedirect("/attempts/{$attempt->id}");
        $this->assertSame(1, Attempt::count());
        $this->assertSame($student->id, $attempt->user_id);
        $this->assertSame($exam->id, $attempt->exam_id);
        $this->assertSame('Toán', $attempt->exam_name);
        $this->assertSame(30, $attempt->duration_minutes);
        $this->assertSame('in_progress', $attempt->status);
        $this->assertSame($attempt->started_at->copy()->addMinutes(30)->timestamp, $attempt->deadline_at->timestamp);
        $this->assertNull($attempt->submitted_at);
        $this->assertNull($attempt->score);
    }

    public function test_attempt_keeps_a_full_snapshot_of_the_exam(): void
    {
        $exam = $this->createExam();

        $this->actingAs($this->student())->post("/exams/{$exam->id}/attempts");

        $questions = collect(Attempt::firstOrFail()->questions)->keyBy('question_id');
        $question = $exam->questions()->with('options')->firstOrFail();
        $snapshot = $questions[$question->id];

        $this->assertCount(2, $questions);
        $this->assertSame($question->content, $snapshot['content']);
        $this->assertNull($snapshot['selected_option_id']);
        $this->assertNull($snapshot['is_correct']);
        $this->assertCount(2, $snapshot['options']);

        foreach ($question->options as $index => $option) {
            $this->assertSame($option->id, $snapshot['options'][$index]['option_id']);
            $this->assertSame($option->content, $snapshot['options'][$index]['content']);
            $this->assertSame($option->is_correct, $snapshot['options'][$index]['is_correct']);
        }
    }

    public function test_attempt_page_hides_answers_and_reloading_adds_no_attempt(): void
    {
        $exam = $this->createExam();
        $student = $this->student();

        $this->actingAs($student)->post("/exams/{$exam->id}/attempts");
        $attempt = Attempt::firstOrFail();

        foreach (range(1, 2) as $reload) {
            $this->actingAs($student)->get("/attempts/{$attempt->id}")
                ->assertOk()
                ->assertSee('Câu 1')
                ->assertSee('Đúng 1')
                ->assertDontSee('is_correct', false)
                ->assertDontSee('Đáp án đúng');
        }

        $this->assertSame(1, Attempt::count());
    }

    public function test_another_student_cannot_open_the_attempt(): void
    {
        $exam = $this->createExam();

        $this->actingAs($this->student())->post("/exams/{$exam->id}/attempts");

        $this->actingAs($this->student())
            ->get('/attempts/'.Attempt::firstOrFail()->id)
            ->assertForbidden();
    }

    public function test_unknown_attempt_returns_404(): void
    {
        $this->actingAs($this->student())
            ->get('/attempts/507f1f77bcf86cd799439011')
            ->assertNotFound();
    }

    public function test_exam_without_questions_cannot_be_started(): void
    {
        $exam = Exam::create(['name' => 'Đề rỗng', 'duration_minutes' => 30]);

        $this->actingAs($this->student())
            ->post("/exams/{$exam->id}/attempts")
            ->assertUnprocessable();

        $this->assertSame(0, Attempt::count());
    }

    public function test_attempt_page_survives_deleting_the_exam(): void
    {
        $exam = $this->createExam(name: 'Đề sẽ bị xóa');
        $student = $this->student();

        $this->actingAs($student)->post("/exams/{$exam->id}/attempts");
        $exam->delete();

        $this->actingAs($student)->get('/attempts/'.Attempt::firstOrFail()->id)
            ->assertOk()
            ->assertSee('Đề sẽ bị xóa')
            ->assertSee('Câu 1');
    }
}
