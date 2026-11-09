<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_dashboard_shows_student_shortcuts(): void
    {
        $student = User::factory()->create(['role' => 'student']);

        $this->actingAs($student)->get('/dashboard')
            ->assertSee('Chào')
            ->assertSee(route('exams.index'))
            ->assertSee(route('packages.index'));
    }

    public function test_admin_dashboard_shows_admin_shortcuts(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->get('/dashboard')
            ->assertSee(route('admin.dashboard'))
            ->assertSee(route('packages.index'))
            ->assertDontSee(route('exams.index'));
    }
}
