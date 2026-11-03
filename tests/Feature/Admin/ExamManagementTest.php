<?php

namespace Tests\Feature\Admin;

use App\Models\Exam;
use App\Models\Option;
use App\Models\Question;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExamManagementTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['role' => 'admin']);
    }

    private function createExam(string $name = 'Toán'): Exam
    {
        return Exam::create(['name' => $name, 'duration_minutes' => 30]);
    }

    /**
     * AppServiceProvider bật preventLazyLoading, nên nếu controller quên eager load thì trang sẽ throw.
     * Cần từ 2 câu trở lên, vì Laravel không chặn lazy loading trên một model đơn lẻ.
     */
    public function test_exam_edit_page_eager_loads_questions_and_options(): void
    {
        $exam = $this->createExam();

        foreach (range(1, 3) as $number) {
            $question = $exam->questions()->create(['content' => "Câu {$number}"]);
            $question->options()->create(['content' => "Đúng {$number}", 'is_correct' => true]);
            $question->options()->create(['content' => "Sai {$number}", 'is_correct' => false]);
        }

        $this->actingAs($this->admin())->get("/admin/exams/{$exam->id}/edit")
            ->assertOk()
            ->assertSeeInOrder(['Đúng 1', 'Sai 1', 'Đúng 2', 'Sai 2', 'Đúng 3', 'Sai 3']);
    }

    public function test_admin_can_open_exam_forms(): void
    {
        $exam = $this->createExam('Toán cơ bản');
        $exam->questions()->create(['content' => '1 + 1 = ?'])
            ->options()->create(['content' => 'Hai', 'is_correct' => true]);

        $admin = $this->admin();

        $this->actingAs($admin)->get('/admin/exams/create')->assertOk();
        $this->actingAs($admin)->get("/admin/exams/{$exam->id}/edit")
            ->assertOk()
            ->assertSee('Toán cơ bản')
            ->assertSee('1 + 1 = ?')
            ->assertSee('Hai');
    }

    public function test_student_cannot_create_exam(): void
    {
        $student = User::factory()->create(['role' => 'student']);

        $this->actingAs($student)
            ->post('/admin/exams', ['name' => 'Toán', 'duration_minutes' => 30])
            ->assertForbidden();

        $this->assertSame(0, Exam::count());
    }

    public function test_admin_can_create_exam(): void
    {
        $this->actingAs($this->admin())
            ->post('/admin/exams', ['name' => 'Toán', 'duration_minutes' => 30])
            ->assertRedirect();

        $this->assertDatabaseHas('exams', ['name' => 'Toán', 'duration_minutes' => 30]);
    }

    public function test_admin_can_update_exam(): void
    {
        $exam = $this->createExam();

        $this->actingAs($this->admin())
            ->put("/admin/exams/{$exam->id}", ['name' => 'Toán nâng cao', 'duration_minutes' => 45])
            ->assertRedirect();

        $this->assertDatabaseHas('exams', ['id' => $exam->id, 'name' => 'Toán nâng cao', 'duration_minutes' => 45]);
    }

    public function test_exam_requires_a_name_and_a_positive_duration(): void
    {
        $this->actingAs($this->admin())
            ->post('/admin/exams', ['name' => '', 'duration_minutes' => 0])
            ->assertSessionHasErrors(['name', 'duration_minutes']);

        $this->assertSame(0, Exam::count());
    }

    public function test_deleting_exam_removes_its_questions_and_options(): void
    {
        $exam = $this->createExam();
        $exam->questions()->create(['content' => '1 + 1 = ?'])
            ->options()->create(['content' => '2', 'is_correct' => true]);

        $this->actingAs($this->admin())
            ->delete("/admin/exams/{$exam->id}")
            ->assertRedirect('/admin/exams');

        $this->assertSame(0, Exam::count());
        $this->assertSame(0, Question::count());
        $this->assertSame(0, Option::count());
    }

    public function test_search_only_returns_matching_exams(): void
    {
        $this->createExam('Toán cơ bản');
        $this->createExam('Tiếng Anh');

        $this->actingAs($this->admin())
            ->get('/admin/exams?search=Toán')
            ->assertOk()
            ->assertSee('Toán cơ bản')
            ->assertDontSee('Tiếng Anh');
    }

    public function test_exam_list_has_ten_per_page_and_keeps_the_search_term(): void
    {
        foreach (range(1, 11) as $number) {
            $this->createExam("Toán {$number}");
        }

        $response = $this->actingAs($this->admin())->get('/admin/exams?search=Toán');

        $this->assertCount(10, $response->viewData('exams'));
        $response->assertSee('search=To', false);
        $response->assertSee('page=2', false);
    }
}
