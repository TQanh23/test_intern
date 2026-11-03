<?php

namespace Tests\Feature\Api;

use App\Models\ActivityLog;
use App\Models\Attempt;
use App\Models\Exam;
use App\Models\Option;
use App\Models\User;
use App\Services\AttemptService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class SubmitAttemptApiTest extends TestCase
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

        // API không có endpoint bắt đầu bài, nên tạo attempt bằng Service như trang web.
        $this->attempt = app(AttemptService::class)->start($this->student, $exam);
    }

    /**
     * @return array<int, int> question_id => option_id
     */
    private function correctAnswers(): array
    {
        return Option::where('is_correct', true)->pluck('id', 'question_id')->all();
    }

    private function submit(array $data = [], ?string $attemptId = null)
    {
        return $this->postJson('/api/attempts/'.($attemptId ?? $this->attempt->id).'/submit', $data);
    }

    public function test_submit_returns_the_graded_attempt(): void
    {
        Sanctum::actingAs($this->student);

        $this->submit(['answers' => $this->correctAnswers()])
            ->assertOk()
            ->assertJsonPath('data.id', $this->attempt->id)
            ->assertJsonPath('data.status', 'submitted')
            ->assertJsonPath('data.score', 3)
            ->assertJsonPath('data.total', 3)
            ->assertJsonPath('data.questions.0.is_correct', true)
            ->assertJsonStructure(['data' => ['exam_id', 'exam_name', 'started_at', 'deadline_at', 'submitted_at']]);

        $this->assertSame(1, ActivityLog::where('action', 'attempt.submitted')->count());
    }

    public function test_client_cannot_send_its_own_score(): void
    {
        Sanctum::actingAs($this->student);

        $this->submit(['answers' => [], 'score' => 99, 'status' => 'submitted'])
            ->assertOk()
            ->assertJsonPath('data.score', 0);
    }

    public function test_second_submit_returns_422(): void
    {
        Sanctum::actingAs($this->student);

        $this->submit()->assertOk();

        $this->submit()
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['attempt' => 'Bài này đã được nộp.']);
    }

    public function test_expired_submit_grades_the_saved_answers_and_ignores_new_ones(): void
    {
        Sanctum::actingAs($this->student);
        $this->travel(31)->minutes();

        // Attempt chưa autosave câu nào, nên đáp án đúng gửi kèm lúc quá giờ không được tính.
        $this->submit(['answers' => $this->correctAnswers()])
            ->assertOk()
            ->assertJsonPath('data.status', 'submitted')
            ->assertJsonPath('data.score', 0);
    }

    public function test_invalid_answers_returns_422(): void
    {
        Sanctum::actingAs($this->student);

        $this->submit(['answers' => 'abc'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('answers');
    }

    public function test_other_student_gets_403(): void
    {
        Sanctum::actingAs(User::factory()->create(['role' => 'student']));

        $this->submit()->assertForbidden();
        $this->assertSame('in_progress', $this->attempt->fresh()->status);
    }

    public function test_guest_gets_401(): void
    {
        $this->submit()->assertUnauthorized();
    }

    public function test_unknown_attempt_gets_404(): void
    {
        Sanctum::actingAs($this->student);

        $this->submit([], '000000000000000000000000')->assertNotFound();
        $this->submit([], 'abc')->assertNotFound();
    }
}
