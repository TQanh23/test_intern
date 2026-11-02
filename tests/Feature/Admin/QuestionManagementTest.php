<?php

namespace Tests\Feature\Admin;

use App\Models\Exam;
use App\Models\Option;
use App\Models\Question;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QuestionManagementTest extends TestCase
{
    use RefreshDatabase;

    private const PAYLOAD = [
        'content' => '1 + 1 = ?',
        'options' => ['1', '2', '3', '4'],
        'correct_option' => 1,
    ];

    private function admin(): User
    {
        return User::factory()->create(['role' => 'admin']);
    }

    private function createExam(): Exam
    {
        return Exam::create(['name' => 'Toán', 'duration_minutes' => 30]);
    }

    private function createQuestion(): Question
    {
        $question = $this->createExam()->questions()->create(['content' => '1 + 1 = ?']);

        foreach (['1', '2', '3', '4'] as $index => $content) {
            $question->options()->create(['content' => $content, 'is_correct' => $index === 1]);
        }

        return $question;
    }

    public function test_create_form_has_no_correct_option_selected(): void
    {
        $exam = $this->createExam();

        $this->actingAs($this->admin())
            ->get("/admin/exams/{$exam->id}/questions/create")
            ->assertOk()
            ->assertDontSee('checked', false);
    }

    public function test_edit_form_shows_options_and_selects_the_correct_one(): void
    {
        $question = $this->createQuestion();

        $response = $this->actingAs($this->admin())->get("/admin/questions/{$question->id}/edit");

        $response->assertOk()->assertSee('1 + 1 = ?');
        $this->assertMatchesRegularExpression('/value="1"[^>]*checked/', $response->getContent());
    }

    public function test_student_cannot_create_question(): void
    {
        $exam = $this->createExam();
        $student = User::factory()->create(['role' => 'student']);

        $this->actingAs($student)
            ->post("/admin/exams/{$exam->id}/questions", self::PAYLOAD)
            ->assertForbidden();

        $this->assertSame(0, Question::count());
    }

    public function test_admin_can_create_question_with_four_options(): void
    {
        $exam = $this->createExam();

        $this->actingAs($this->admin())
            ->post("/admin/exams/{$exam->id}/questions", self::PAYLOAD)
            ->assertRedirect("/admin/exams/{$exam->id}/edit");

        $question = $exam->questions()->firstOrFail();

        $this->assertSame('1 + 1 = ?', $question->content);
        $this->assertSame(4, $question->options()->count());
        $this->assertSame(['2'], $question->options()->where('is_correct', true)->pluck('content')->all());
    }

    public function test_updating_question_changes_correct_option_and_keeps_question_id(): void
    {
        $question = $this->createQuestion();

        $this->actingAs($this->admin())
            ->put("/admin/questions/{$question->id}", [
                'content' => '2 + 2 = ?',
                'options' => ['1', '2', '3', '4'],
                'correct_option' => 3,
            ])
            ->assertRedirect("/admin/exams/{$question->exam_id}/edit");

        $this->assertSame('2 + 2 = ?', $question->fresh()->content);
        $this->assertSame(4, $question->options()->count());
        $this->assertSame(['4'], $question->options()->where('is_correct', true)->pluck('content')->all());
    }

    public function test_invalid_question_is_rejected_and_old_data_is_kept(): void
    {
        $question = $this->createQuestion();

        $this->actingAs($this->admin())
            ->put("/admin/questions/{$question->id}", [
                'content' => 'Câu mới',
                'options' => ['1', '2', '3'],
                'correct_option' => 5,
            ])
            ->assertSessionHasErrors(['options', 'correct_option']);

        $this->assertSame('1 + 1 = ?', $question->fresh()->content);
        $this->assertSame(4, $question->options()->count());
    }

    public function test_every_option_must_have_content(): void
    {
        $exam = $this->createExam();

        $this->actingAs($this->admin())
            ->post("/admin/exams/{$exam->id}/questions", [...self::PAYLOAD, 'options' => ['1', '', '3', '4']])
            ->assertSessionHasErrors(['options.1']);

        $this->assertSame(0, Question::count());
    }

    public function test_deleting_question_removes_its_options(): void
    {
        $question = $this->createQuestion();

        $this->actingAs($this->admin())
            ->delete("/admin/questions/{$question->id}")
            ->assertRedirect("/admin/exams/{$question->exam_id}/edit");

        $this->assertSame(0, Question::count());
        $this->assertSame(0, Option::count());
    }
}
