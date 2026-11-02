<?php

namespace Tests\Feature;

use App\Models\Exam;
use App\Models\Option;
use App\Models\Question;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExamSchemaTest extends TestCase
{
    use RefreshDatabase;

    private function createOption(): Option
    {
        return Exam::create(['name' => 'Math', 'duration_minutes' => 45])
            ->questions()->create(['content' => '1 + 1 = ?'])
            ->options()->create(['content' => '2', 'is_correct' => true]);
    }

    public function test_new_users_are_students_and_cannot_mass_assign_role(): void
    {
        $user = User::create([
            'name' => 'Student',
            'email' => 'student@example.com',
            'password' => 'password',
            'role' => 'admin',
        ]);

        $this->assertSame('student', $user->fresh()->role);
    }

    public function test_relations_work_in_both_directions(): void
    {
        $option = $this->createOption();
        $question = $option->question;
        $exam = $question->exam;

        $this->assertSame('Math', $exam->name);
        $this->assertTrue($exam->questions->first()->is($question));
        $this->assertTrue($question->options->first()->is($option));
    }

    public function test_deleting_an_exam_removes_its_questions_and_options(): void
    {
        $this->createOption()->question->exam->delete();

        $this->assertSame(0, Question::count());
        $this->assertSame(0, Option::count());
    }

    public function test_is_correct_is_cast_to_boolean(): void
    {
        $this->assertTrue($this->createOption()->fresh()->is_correct);
    }
}
