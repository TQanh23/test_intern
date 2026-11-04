<?php

use App\Http\Controllers\Api\AttemptController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ExamController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login']);

// Dùng lại middleware `student` và policy của web, chỉ đổi cách đăng nhập sang token Sanctum.
Route::middleware(['auth:sanctum', 'student'])->group(function () {
    Route::get('/exams', [ExamController::class, 'index']);
    Route::post('/attempts/{attempt}/submit', [AttemptController::class, 'submit'])
        ->middleware('can:view,attempt');
});
