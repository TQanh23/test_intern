<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Tạo tài khoản mẫu, mật khẩu `password`.
     * Không dùng UserFactory vì factory cần Faker, mà Faker là package dev, không có trong image staging.
     */
    public function run(): void
    {
        $accounts = [
            ['name' => 'Admin', 'email' => 'admin@example.com', 'role' => 'admin'],
            ['name' => 'Student', 'email' => 'student@example.com', 'role' => 'student'],
        ];

        foreach ($accounts as $account) {
            if (! User::where('email', $account['email'])->exists()) {
                // forceCreate gán được role dù role không nằm trong $fillable.
                User::forceCreate($account + [
                    'password' => Hash::make('password'),
                    'email_verified_at' => now(),
                ]);
            }
        }
    }
}
