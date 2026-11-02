<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Tạo tài khoản mẫu, mật khẩu `password` lấy từ UserFactory.
     */
    public function run(): void
    {
        $accounts = [
            ['name' => 'Admin', 'email' => 'admin@example.com', 'role' => 'admin'],
            ['name' => 'Student', 'email' => 'student@example.com', 'role' => 'student'],
        ];

        foreach ($accounts as $account) {
            if (! User::where('email', $account['email'])->exists()) {
                // Factory gán được role dù role không nằm trong $fillable.
                User::factory()->create($account);
            }
        }
    }
}
