<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\Attempt;
use App\Models\Exam;
use App\Models\Option;
use App\Models\User;
use App\Services\AttemptService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class CloseExpiredAttemptsTest extends TestCase
{
    use RefreshDatabase;

    private AttemptService $attempts;

    private Exam $exam;

    protected function setUp(): void
    {
        parent::setUp();

        // Chặn việc xóa nhầm dữ liệu dev nếu phpunit.xml thiếu MONGODB_DATABASE.
        $this->assertSame('exam_testing', DB::connection('mongodb')->getDatabase()->getDatabaseName());

        Attempt::truncate();
        ActivityLog::truncate();

        $this->attempts = app(AttemptService::class);
        $this->exam = Exam::create(['name' => 'Toán', 'duration_minutes' => 30]);

        foreach (range(1, 3) as $number) {
            $question = $this->exam->questions()->create(['content' => "Câu {$number}"]);
            $question->options()->create(['content' => "Sai {$number}", 'is_correct' => false]);
            $question->options()->create(['content' => "Đúng {$number}", 'is_correct' => true]);
        }
    }

    private function startFor(User $student): Attempt
    {
        return $this->attempts->start($student, $this->exam);
    }

    public function test_command_grades_expired_attempts_with_saved_answers(): void
    {
        $attempt = $this->startFor(User::factory()->create(['role' => 'student']));
        $option = Option::where('is_correct', true)->firstOrFail();
        $this->attempts->saveAnswer($attempt, $option->question_id, $option->id);

        $this->travelTo($attempt->deadline_at->copy()->addMinute());

        $this->artisan('app:close-expired-attempts')
            ->expectsOutput('Đã chấm 1 bài.')
            ->assertSuccessful();

        $attempt->refresh();

        $this->assertSame('submitted', $attempt->status);
        $this->assertSame(1, $attempt->score);
        $this->assertSame(3, $attempt->total);
        $this->assertSame(1, ActivityLog::where('action', 'attempt.submitted')->count());
    }

    public function test_command_leaves_open_and_submitted_attempts_alone(): void
    {
        $open = $this->startFor(User::factory()->create(['role' => 'student']));
        $submitted = $this->startFor(User::factory()->create(['role' => 'student']));
        $this->attempts->submit($submitted, Option::where('is_correct', true)->pluck('id', 'question_id')->all());

        $this->artisan('app:close-expired-attempts')
            ->expectsOutput('Đã chấm 0 bài.')
            ->assertSuccessful();

        $this->assertSame('in_progress', $open->refresh()->status);
        $this->assertSame(3, $submitted->refresh()->score);
    }

    public function test_command_is_scheduled_every_minute(): void
    {
        $this->artisan('schedule:list')
            ->expectsOutputToContain('app:close-expired-attempts')
            ->assertSuccessful();
    }
}
