<?php

namespace Tests\Feature\Student;

use App\Models\ActivityLog;
use App\Models\Attempt;
use App\Models\Exam;
use App\Models\Option;
use App\Models\User;
use App\Services\AttemptService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use MongoDB\Driver\Exception\BulkWriteException;
use Tests\TestCase;

class AutosaveTest extends TestCase
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
        ActivityLog::truncate();

        $this->student = User::factory()->create(['role' => 'student']);
        $this->exam = $this->createExam('Toán', 10);
    }

    private function createExam(string $name, int $questionCount = 3): Exam
    {
        $exam = Exam::create(['name' => $name, 'duration_minutes' => 30]);

        foreach (range(1, $questionCount) as $number) {
            $question = $exam->questions()->create(['content' => "Câu {$number}"]);
            $question->options()->create(['content' => "Sai {$number}", 'is_correct' => false]);
            $question->options()->create(['content' => "Đúng {$number}", 'is_correct' => true]);
        }

        return $exam;
    }

    private function start(?Exam $exam = null): Attempt
    {
        $exam ??= $this->exam;

        $this->actingAs($this->student)->post("/exams/{$exam->id}/attempts");

        return Attempt::where('exam_id', $exam->id)->latest('_id')->firstOrFail();
    }

    private function save(Attempt $attempt, mixed $questionId, mixed $optionId, ?User $user = null)
    {
        return $this->actingAs($user ?? $this->student)
            ->patchJson("/attempts/{$attempt->id}/answers", ['question_id' => $questionId, 'option_id' => $optionId]);
    }

    private function correctOption(int $questionId): int
    {
        return Option::where('question_id', $questionId)->where('is_correct', true)->value('id');
    }

    private function savedOption(Attempt $attempt, int $questionId): ?int
    {
        return collect($attempt->refresh()->questions)->firstWhere('question_id', $questionId)['selected_option_id'];
    }

    private function firstQuestionId(): int
    {
        return $this->exam->questions()->orderBy('id')->value('id');
    }

    public function test_choosing_an_answer_saves_it_in_the_snapshot(): void
    {
        $attempt = $this->start();
        $question = $this->firstQuestionId();

        $this->save($attempt, $question, $this->correctOption($question))->assertNoContent();

        $this->assertSame($this->correctOption($question), $this->savedOption($attempt, $question));
        $this->assertSame('in_progress', $attempt->status);
    }

    public function test_answer_can_be_changed_and_cleared(): void
    {
        $attempt = $this->start();
        $question = $this->firstQuestionId();
        $wrong = Option::where('question_id', $question)->where('is_correct', false)->value('id');

        $this->save($attempt, $question, $this->correctOption($question))->assertNoContent();
        $this->save($attempt, $question, $wrong)->assertNoContent();
        $this->assertSame($wrong, $this->savedOption($attempt, $question));

        $this->save($attempt, $question, null)->assertNoContent();
        $this->assertNull($this->savedOption($attempt, $question));
    }

    public function test_resaving_the_same_answer_succeeds(): void
    {
        $attempt = $this->start();
        $question = $this->firstQuestionId();

        $this->save($attempt, $question, $this->correctOption($question))->assertNoContent();
        $this->save($attempt, $question, $this->correctOption($question))->assertNoContent();
    }

    public function test_reloading_keeps_the_saved_answer_checked(): void
    {
        $attempt = $this->start();
        $question = $this->firstQuestionId();
        $option = $this->correctOption($question);

        $this->save($attempt, $question, $option);

        $this->actingAs($this->student)->get("/attempts/{$attempt->id}")
            ->assertOk()
            ->assertSee("value=\"{$option}\" checked", false);
    }

    public function test_option_of_another_question_is_rejected(): void
    {
        $attempt = $this->start();
        [$first, $second] = $this->exam->questions()->orderBy('id')->pluck('id')->all();

        $this->save($attempt, $first, $this->correctOption($second))
            ->assertUnprocessable()
            ->assertJsonValidationErrors('attempt');

        $this->assertNull($this->savedOption($attempt, $first));
    }

    public function test_unknown_question_is_rejected(): void
    {
        $attempt = $this->start();
        $other = $this->createExam('Anh', 1)->questions()->value('id');

        $this->save($attempt, $other, $this->correctOption($other))
            ->assertUnprocessable()
            ->assertJsonValidationErrors('attempt');
    }

    public function test_saving_after_deadline_is_rejected(): void
    {
        $attempt = $this->start();
        $question = $this->firstQuestionId();

        $this->travelTo($attempt->deadline_at->copy()->addSecond());

        $this->save($attempt, $question, $this->correctOption($question))
            ->assertUnprocessable()
            ->assertJsonValidationErrors('attempt');

        $this->assertNull($this->savedOption($attempt, $question));
    }

    public function test_saving_after_submit_is_rejected(): void
    {
        $attempt = $this->start();
        $question = $this->firstQuestionId();

        $this->actingAs($this->student)->post("/attempts/{$attempt->id}/submit");

        $this->save($attempt, $question, $this->correctOption($question))
            ->assertUnprocessable()
            ->assertJsonValidationErrors('attempt');

        $this->assertNull($this->savedOption($attempt, $question));
    }

    public function test_other_student_cannot_save(): void
    {
        $attempt = $this->start();
        $question = $this->firstQuestionId();

        $this->save($attempt, $question, $this->correctOption($question), User::factory()->create(['role' => 'student']))
            ->assertForbidden();

        $this->assertNull($this->savedOption($attempt, $question));
    }

    public function test_question_id_is_required(): void
    {
        $attempt = $this->start();

        $this->save($attempt, null, null)
            ->assertUnprocessable()
            ->assertJsonValidationErrors('question_id');
    }

    public function test_starting_again_returns_the_open_attempt(): void
    {
        $first = $this->start();

        $this->actingAs($this->student)->post("/exams/{$this->exam->id}/attempts")
            ->assertRedirect("/attempts/{$first->id}");

        $this->assertSame(1, Attempt::count());
        $this->assertSame(1, ActivityLog::where('action', 'attempt.started')->count());
    }

    public function test_new_attempt_after_submit_has_a_different_order(): void
    {
        $first = $this->start();
        $this->actingAs($this->student)->post("/attempts/{$first->id}/submit");

        $second = $this->start();

        $this->assertNotSame($first->id, $second->id);
        $this->assertNotSame(
            array_column($first->questions, 'question_id'),
            array_column($second->questions, 'question_id'),
        );
    }

    public function test_starting_after_expiry_grades_the_old_attempt_and_opens_a_new_one(): void
    {
        $old = $this->start();
        $question = $this->firstQuestionId();
        $this->save($old, $question, $this->correctOption($question));

        $this->travelTo($old->deadline_at->copy()->addMinute());

        $new = $this->start();
        $old->refresh();

        $this->assertNotSame($old->id, $new->id);
        $this->assertSame('submitted', $old->status);
        $this->assertSame(1, $old->score);
        $this->assertSame(10, $old->total);
        $this->assertSame('in_progress', $new->status);
    }

    public function test_database_rejects_a_second_open_attempt(): void
    {
        $data = ['user_id' => 1, 'exam_id' => 1, 'status' => 'in_progress'];

        Attempt::create(['user_id' => 1, 'exam_id' => 1, 'status' => 'submitted']);
        Attempt::create($data);

        $this->expectException(BulkWriteException::class);

        Attempt::create($data);
    }

    public function test_concurrent_start_returns_the_attempt_created_by_the_other_request(): void
    {
        // Giả lập request thứ hai tạo bài ngay sau khi Service đã kiểm tra mà chưa kịp tạo.
        Attempt::creating(function (Attempt $attempt) {
            Attempt::withoutEvents(fn () => Attempt::create([
                'user_id' => $attempt->user_id,
                'exam_id' => $attempt->exam_id,
                'status' => 'in_progress',
                'deadline_at' => $attempt->deadline_at,
            ]));
        });

        $returned = app(AttemptService::class)->start($this->student, $this->exam);

        $this->assertSame(1, Attempt::count());
        $this->assertSame(Attempt::firstOrFail()->id, $returned->id);
    }

    public function test_different_exams_can_be_open_at_the_same_time(): void
    {
        $this->start();
        $this->start($this->createExam('Anh'));

        $this->assertSame(2, Attempt::where('status', 'in_progress')->count());
    }

    public function test_clock_uses_server_time_and_does_not_reset_on_reload(): void
    {
        // Khóa thời gian để vài ms chạy code không làm kết quả thành 1199.
        $this->freezeSecond();
        $attempt = $this->start();

        $this->travel(10)->minutes();

        foreach (range(1, 2) as $reload) {
            $this->actingAs($this->student)->get("/attempts/{$attempt->id}")
                ->assertOk()
                ->assertSee('left: 1200,', false);
        }
    }
}
