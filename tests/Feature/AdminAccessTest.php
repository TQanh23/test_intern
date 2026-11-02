<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get('/admin')->assertRedirect('/login');
    }

    public function test_student_cannot_open_admin_page(): void
    {
        $student = User::factory()->create(['role' => 'student']);

        $this->actingAs($student)->get('/admin')->assertForbidden();
    }

    public function test_admin_can_open_admin_page(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->get('/admin')->assertOk();
    }

    public function test_registering_with_admin_role_still_creates_a_student(): void
    {
        $this->post('/register', [
            'name' => 'Hacker',
            'email' => 'hacker@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'role' => 'admin',
        ]);

        $this->assertSame('student', User::where('email', 'hacker@example.com')->firstOrFail()->fresh()->role);
    }

    public function test_seeded_admin_can_log_in_and_open_admin_page(): void
    {
        $this->seed();

        $this->post('/login', ['email' => 'admin@example.com', 'password' => 'password']);

        $this->assertAuthenticated();
        $this->get('/admin')->assertOk();
    }

    public function test_only_admin_sees_the_admin_menu_link(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $student = User::factory()->create(['role' => 'student']);

        $this->actingAs($admin)->get('/dashboard')->assertSee(route('admin.dashboard'));
        $this->actingAs($student)->get('/dashboard')->assertDontSee(route('admin.dashboard'));
    }
}
