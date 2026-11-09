<?php

namespace Tests\Feature\Admin;

use App\Models\Exam;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminLayoutTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['role' => 'admin']);
    }

    public function test_admin_pages_use_the_sidebar(): void
    {
        $this->actingAs($this->admin())
            ->get(route('admin.exams.index'))
            ->assertOk()
            ->assertSee(route('admin.statistics'))
            ->assertSee(route('admin.preview-statistics'))
            ->assertSee(route('packages.index'));
    }

    public function test_question_form_still_has_no_checked_attribute(): void
    {
        $exam = Exam::create(['name' => 'Toán', 'duration_minutes' => 30]);

        $this->actingAs($this->admin())
            ->get(route('admin.exams.questions.create', $exam))
            ->assertOk()
            ->assertDontSee('checked', false);
    }
}
