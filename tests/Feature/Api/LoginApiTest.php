<?php

namespace Tests\Feature\Api;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\PersonalAccessToken;
use Tests\TestCase;

class LoginApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Chặn việc xóa nhầm dữ liệu dev nếu phpunit.xml thiếu MONGODB_DATABASE.
        $this->assertSame('exam_testing', DB::connection('mongodb')->getDatabase()->getDatabaseName());

        ActivityLog::truncate();
    }

    public function test_correct_credentials_return_a_token_of_that_user(): void
    {
        $user = User::factory()->create(['role' => 'student']);

        $token = $this->postJson('/api/login', ['email' => $user->email, 'password' => 'password'])
            ->assertOk()
            ->json('token');

        // Không thử token bằng cách gọi API khác trong cùng test: guard web đã đăng nhập sẵn trong bộ nhớ, request sẽ pass dù token sai.
        $this->assertTrue(PersonalAccessToken::findToken($token)->tokenable->is($user));
        $this->assertSame(1, ActivityLog::where('action', 'auth.login')->count());
    }

    public function test_wrong_password_returns_422_without_token(): void
    {
        $user = User::factory()->create(['role' => 'student']);

        $this->postJson('/api/login', ['email' => $user->email, 'password' => 'wrong'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('email');

        $this->assertDatabaseCount('personal_access_tokens', 0);
    }
}
