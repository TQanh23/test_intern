<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WelcomePageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    public function test_guest_sees_login_and_register_links(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee(route('login'))
            ->assertSee(route('register'))
            ->assertSee('ExamPro');
    }

    public function test_logged_in_user_sees_dashboard_link(): void
    {
        $user = User::factory()->create(['role' => 'student']);

        $this->actingAs($user)->get('/')
            ->assertSee(route('dashboard'))
            ->assertDontSee(route('register'));
    }
}
