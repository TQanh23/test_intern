<?php

namespace Tests\Feature\Student;

use App\Models\ActivityLog;
use App\Models\Attempt;
use App\Models\Exam;
use App\Models\Option;
use App\Models\User;
use App\Services\AttemptService;
use Carbon\CarbonInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class SubmitAttemptTest extends TestCase
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
        $this->exam = Exam::create(['name' => 'Toán', 'duration_minutes' => 30]);

        foreach (range(1, 3) as $number) {
            $question = $this->exam->questions()->create(['content' => "Câu {$number}"]);
            $question->options()->create(['content' => "Sai {$number}", 'is_correct' => false]);
            $question->options()->create(['content' => "Đúng {$number}", 'is_correct' => true]);
        }
    }

    private function startAttempt(): Attempt
    {
        $this->actingAs($this->student)->post("/exams/{$this->exam->id}/attempts");

        return Attempt::firstOrFail();
    }

    /**
     * @return array<int, int> question_id => option_id
     */
    private function answers(bool $correct): array
    {
        return Option::where('is_correct', $correct)->pluck('id', 'question_id')->all();
    }

    private function submit(Attempt $attempt, array $data, ?User $user = null)
    {
        return $this->actingAs($user ?? $this->student)
            ->from("/attempts/{$attempt->id}")
            ->post("/attempts/{$attempt->id}/submit", $data);
    }

    public function test_all_correct_answers_get_full_score(): void
    {
        $attempt = $this->startAttempt();

        $this->submit($attempt, ['answers' => $this->answers(true)])
            ->assertRedirect("/attempts/{$attempt->id}");

        $attempt->refresh();

        $this->assertSame('submitted', $attempt->status);
        $this->assertSame(3, $attempt->score);
        $this->assertSame(3, $attempt->total);
        $this->assertInstanceOf(CarbonInterface::class, $attempt->submitted_at);
    }

    public function test_wrong_and_blank_answers_score_nothing(): void
    {
        $attempt = $this->startAttempt();
        [$first, $second] = $this->exam->questions()->pluck('id')->all();

        $this->submit($attempt, ['answers' => [
            $first => $this->answers(true)[$first],
            $second => $this->answers(false)[$second],
        ]]);

        $attempt->refresh();

        $this->assertSame(1, $attempt->score);
        $this->assertSame(3, $attempt->total);
    }

    public function test_each_question_stores_the_selected_option_and_result(): void
    {
        $attempt = $this->startAttempt();
        [$first, $second, $third] = $this->exam->questions()->pluck('id')->all();
        $correct = $this->answers(true);
        $wrong = $this->answers(false);

        $this->submit($attempt, ['answers' => [$first => $correct[$first], $second => $wrong[$second]]]);

        $questions = collect($attempt->refresh()->questions)->keyBy('question_id');

        $this->assertSame($correct[$first], $questions[$first]['selected_option_id']);
        $this->assertTrue($questions[$first]['is_correct']);
        $this->assertSame($wrong[$second], $questions[$second]['selected_option_id']);
        $this->assertFalse($questions[$second]['is_correct']);
        $this->assertNull($questions[$third]['selected_option_id']);
        $this->assertFalse($questions[$third]['is_correct']);
    }

    public function test_client_cannot_send_its_own_score(): void
    {
        $attempt = $this->startAttempt();

        $this->submit($attempt, [
            'answers' => $this->answers(false),
            'score' => 999,
            'total' => 1,
            'is_correct' => true,
            'status' => 'submitted',
        ]);

        $attempt->refresh();

        $this->assertSame(0, $attempt->score);
        $this->assertSame(3, $attempt->total);
    }

    public function test_correct_option_of_another_question_does_not_count(): void
    {
        $attempt = $this->startAttempt();
        [$first, $second] = $this->exam->questions()->pluck('id')->all();

        $this->submit($attempt, ['answers' => [$first => $this->answers(true)[$second]]]);

        $attempt->refresh();

        $this->assertSame(0, $attempt->score);
        $this->assertNull(collect($attempt->questions)->firstWhere('question_id', $first)['selected_option_id']);
    }

    public function test_submitting_after_the_deadline_grades_the_saved_answers(): void
    {
        $attempt = $this->startAttempt();
        $first = $this->exam->questions()->orderBy('id')->value('id');

        $this->actingAs($this->student)->patchJson("/attempts/{$attempt->id}/answers", [
            'question_id' => $first,
            'option_id' => $this->answers(true)[$first],
        ])->assertNoContent();

        $this->travelTo($attempt->deadline_at->copy()->addSecond());

        // Đáp án gửi kèm lúc quá giờ bị bỏ qua, chỉ câu đã lưu trước hạn được tính.
        $this->submit($attempt, ['answers' => $this->answers(true)])
            ->assertSessionHasNoErrors();

        $attempt->refresh();

        $this->assertSame('submitted', $attempt->status);
        $this->assertSame(1, $attempt->score);
        $this->assertSame(3, $attempt->total);
    }

    public function test_submitting_exactly_at_the_deadline_is_accepted(): void
    {
        $attempt = $this->startAttempt();

        $this->travelTo($attempt->deadline_at);

        $this->submit($attempt, ['answers' => $this->answers(true)])
            ->assertSessionHasNoErrors();

        $this->assertSame(3, $attempt->refresh()->score);
    }

    public function test_submitting_twice_keeps_the_first_result(): void
    {
        $attempt = $this->startAttempt();

        $this->submit($attempt, ['answers' => $this->answers(false)]);

        $this->submit($attempt, ['answers' => $this->answers(true)])
            ->assertSessionHasErrors('attempt');

        $this->assertSame(0, $attempt->refresh()->score);
    }

    public function test_concurrent_request_cannot_overwrite_a_submitted_attempt(): void
    {
        $attempt = $this->startAttempt();

        // Request thứ hai đã đọc attempt lúc còn in_progress, trước khi request đầu ghi xong.
        $staleAttempt = Attempt::findOrFail($attempt->id);

        $this->submit($attempt, ['answers' => $this->answers(false)]);

        try {
            app(AttemptService::class)->submit($staleAttempt, $this->answers(true));
            $this->fail('Request thứ hai phải bị từ chối.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('attempt', $exception->errors());
        }

        $this->assertSame(0, $attempt->refresh()->score);
    }

    public function test_grading_uses_the_snapshot_not_the_current_exam(): void
    {
        $attempt = $this->startAttempt();
        $correct = $this->answers(true);

        Option::query()->update(['is_correct' => false]);

        $this->submit($attempt, ['answers' => $correct]);

        $this->assertSame(3, $attempt->refresh()->score);
    }

    public function test_only_the_owner_can_submit(): void
    {
        $attempt = $this->startAttempt();

        $this->submit($attempt, [], User::factory()->create(['role' => 'student']))->assertForbidden();
        $this->submit($attempt, [], User::factory()->create(['role' => 'admin']))->assertForbidden();

        $this->assertSame('in_progress', $attempt->refresh()->status);
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $attempt = $this->startAttempt();

        auth()->logout();

        $this->post("/attempts/{$attempt->id}/submit")->assertRedirect('/login');
    }

    public function test_answers_must_be_integers(): void
    {
        $attempt = $this->startAttempt();
        $questionId = $this->exam->questions()->value('id');

        $this->submit($attempt, ['answers' => [$questionId => 'abc']])
            ->assertSessionHasErrors("answers.{$questionId}");

        $this->assertSame('in_progress', $attempt->refresh()->status);
    }

    public function test_page_shows_the_score_after_submitting(): void
    {
        $attempt = $this->startAttempt();

        $this->submit($attempt, ['answers' => $this->answers(true)]);

        $this->get("/attempts/{$attempt->id}")
            ->assertOk()
            ->assertSee('3/3')
            ->assertDontSee('Nộp bài');
    }

    public function test_page_shows_only_a_grade_button_when_time_is_up(): void
    {
        $attempt = $this->startAttempt();

        $this->travelTo($attempt->deadline_at->copy()->addSecond());

        $this->get("/attempts/{$attempt->id}")
            ->assertOk()
            ->assertSee('Đã hết giờ')
            ->assertSee('Chấm bài')
            ->assertDontSee('type="radio"', false)
            ->assertDontSee('Nộp bài')
            ->assertDontSee('Đúng 1')
            ->assertDontSee('Đáp án đúng');
    }

    public function test_result_labels_correct_wrong_and_blank_questions(): void
    {
        $attempt = $this->startAttempt();
        [$first, $second] = $this->exam->questions()->pluck('id')->all();

        $this->submit($attempt, ['answers' => [
            $first => $this->answers(true)[$first],
            $second => $this->answers(false)[$second],
        ]]);

        $response = $this->get("/attempts/{$attempt->id}")
            ->assertOk()
            ->assertSee('1/3')
            ->assertSee('Câu đúng')
            ->assertSee('Câu sai')
            ->assertSee('Chưa trả lời');

        $this->assertSame(3, substr_count($response->getContent(), 'Đáp án đúng'));
        $this->assertSame(2, substr_count($response->getContent(), 'Bạn chọn'));
    }

    public function test_result_marks_the_selected_wrong_option_and_the_correct_option(): void
    {
        $attempt = $this->startAttempt();
        $first = $this->exam->questions()->value('id');

        $this->submit($attempt, ['answers' => [$first => $this->answers(false)[$first]]]);

        $this->get("/attempts/{$attempt->id}")
            ->assertSeeInOrder(['Sai 1', 'Bạn chọn', 'Đúng 1', 'Đáp án đúng']);
    }

    public function test_result_uses_the_snapshot_after_the_exam_changes(): void
    {
        $attempt = $this->startAttempt();

        $this->submit($attempt, ['answers' => $this->answers(true)]);

        Option::query()->update(['content' => 'Đã sửa', 'is_correct' => false]);
        $this->exam->delete();

        $this->get("/attempts/{$attempt->id}")
            ->assertOk()
            ->assertSee('3/3')
            ->assertSeeInOrder(['Đúng 1', 'Đáp án đúng'])
            ->assertDontSee('Đã sửa');
    }

    public function test_another_student_cannot_open_the_result(): void
    {
        $attempt = $this->startAttempt();

        $this->submit($attempt, ['answers' => $this->answers(true)]);

        $this->actingAs(User::factory()->create(['role' => 'student']))
            ->get("/attempts/{$attempt->id}")
            ->assertForbidden();
    }
}
