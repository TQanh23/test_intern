<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Các trang chỉ là khung HTML, dữ liệu do Alpine gọi API. Test kiểm tra quyền vào trang và component được gắn đúng.
 */
class PackagePagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get('/packages')->assertRedirect('/login');
        $this->get('/packages/mindset-for-ielts-level-1')->assertRedirect('/login');
    }

    public function test_student_and_admin_can_open_package_pages(): void
    {
        foreach (['student', 'admin'] as $role) {
            $user = User::factory()->create(['role' => $role]);

            $this->actingAs($user)->get('/packages')
                ->assertOk()
                ->assertSee('x-data="packageList"', false);

            $this->actingAs($user)->get('/packages/mindset-for-ielts-level-1')
                ->assertOk()
                ->assertSee("packageDetail('mindset-for-ielts-level-1')", false);
        }
    }

    public function test_navigation_links_to_packages(): void
    {
        $this->actingAs(User::factory()->create())->get('/dashboard')
            ->assertSee(route('packages.index'));
    }

    public function test_only_admin_can_open_preview_statistics(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'student']))
            ->get('/admin/preview-statistics')
            ->assertForbidden();

        $this->actingAs(User::factory()->create(['role' => 'admin']))
            ->get('/admin/preview-statistics')
            ->assertOk()
            ->assertSee('x-data="previewStats"', false);
    }
}
