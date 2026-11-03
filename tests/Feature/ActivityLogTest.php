<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\Attempt;
use App\Models\Exam;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ActivityLogTest extends TestCase
{
    use RefreshDatabase;

    private User $student;

    private Exam $exam;

    protected function setUp(): void
    {
        parent::setUp();

        // Chặn việc xóa nhầm dữ liệu dev nếu phpunit.xml thiếu MONGODB_DATABASE.
        $this->assertSame('exam_testing', DB::connection('mongodb')->getDatabase()->getDatabaseName());

        Attempt::truncate();

        $this->student = User::factory()->create(['role' => 'student']);
        $this->exam = Exam::create(['name' => 'Toán', 'duration_minutes' => 30]);

        $question = $this->exam->questions()->create(['content' => 'Câu 1']);
        $question->options()->create(['content' => 'Sai', 'is_correct' => false]);
        $question->options()->create(['content' => 'Đúng', 'is_correct' => true]);

        // Xóa sau khi tạo đề để log exam.created của phần chuẩn bị không lẫn vào kết quả.
        ActivityLog::truncate();
    }

    private function startAttempt(): Attempt
    {
        $this->actingAs($this->student)->post("/exams/{$this->exam->id}/attempts");

        return Attempt::firstOrFail();
    }

    private function submit(Attempt $attempt): void
    {
        $optionId = $this->exam->questions()->first()->options()->where('is_correct', true)->value('id');

        $this->actingAs($this->student)
            ->from("/attempts/{$attempt->id}")
            ->post("/attempts/{$attempt->id}/submit", ['answers' => [$attempt->questions[0]['question_id'] => $optionId]]);
    }

    public function test_login_and_logout_are_logged(): void
    {
        $this->post('/login', ['email' => $this->student->email, 'password' => 'password']);
        $this->post('/logout');

        $this->assertSame(['auth.login', 'auth.logout'], ActivityLog::orderBy('_id')->pluck('action')->all());
        $this->assertSame(2, ActivityLog::where('user_id', $this->student->id)->count());
    }

    public function test_starting_an_attempt_is_logged(): void
    {
        $attempt = $this->startAttempt();

        $log = ActivityLog::where('action', 'attempt.started')->sole();

        $this->assertSame($this->student->id, $log->user_id);
        $this->assertSame($this->exam->id, $log->exam_id);
        $this->assertSame($attempt->id, $log->details['attempt_id']);
    }

    public function test_submitting_is_logged_with_the_score(): void
    {
        $attempt = $this->startAttempt();

        $this->submit($attempt);

        $log = ActivityLog::where('action', 'attempt.submitted')->sole();

        $this->assertSame($this->student->id, $log->user_id);
        $this->assertSame($this->exam->id, $log->exam_id);
        $this->assertSame(['attempt_id' => $attempt->id, 'score' => 1, 'total' => 1], $log->details);
    }

    public function test_rejected_submit_writes_no_log(): void
    {
        $submitted = $this->startAttempt();
        $this->submit($submitted);
        $this->submit($submitted);

        $this->assertSame(1, ActivityLog::where('action', 'attempt.submitted')->count());
    }

    public function test_admin_exam_create_update_delete_are_logged(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->post('/admin/exams', ['name' => 'Lý', 'duration_minutes' => 20]);
        $exam = Exam::where('name', 'Lý')->firstOrFail();
        $this->actingAs($admin)->put("/admin/exams/{$exam->id}", ['name' => 'Lý nâng cao', 'duration_minutes' => 20]);
        $this->actingAs($admin)->delete("/admin/exams/{$exam->id}");

        $logs = ActivityLog::orderBy('_id')->get();

        $this->assertSame(['exam.created', 'exam.updated', 'exam.deleted'], $logs->pluck('action')->all());
        $this->assertSame([$admin->id], $logs->pluck('user_id')->unique()->all());
        $this->assertSame([$exam->id], $logs->pluck('exam_id')->unique()->all());
        $this->assertSame(['name' => 'Lý nâng cao'], $logs[1]->details['changes']);
    }
}
