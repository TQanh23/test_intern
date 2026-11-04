<?php

use App\Http\Controllers\Api\AttemptController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DocumentPreviewController;
use App\Http\Controllers\Api\ExamController;
use App\Http\Controllers\Api\PackageController;
use App\Http\Controllers\Api\PreviewStatisticsController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login']);

// Dùng lại middleware `student` và policy của web, chỉ đổi cách đăng nhập sang token Sanctum.
Route::middleware(['auth:sanctum', 'student'])->group(function () {
    Route::get('/exams', [ExamController::class, 'index']);
    Route::post('/attempts/{attempt}/submit', [AttemptController::class, 'submit'])
        ->middleware('can:view,attempt');
});

// Gói IELTS: mọi role đã đăng nhập đều xem được.
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/packages', [PackageController::class, 'index']);
    Route::get('/packages/{package:slug}', [PackageController::class, 'show']);
    Route::post('/documents/{document}/preview', [DocumentPreviewController::class, 'store'])
        ->middleware('throttle:preview');
    Route::get('/admin/preview-stats', PreviewStatisticsController::class)->middleware('admin');
});

// Không cần token: chữ ký trong URL thay cho đăng nhập, để pdf.js và thẻ <audio> mở thẳng được link.
// Vẫn giới hạn 60 lần/phút để một link còn hạn không bị tải liên tục.
Route::get('/documents/{document}/preview-file', [DocumentPreviewController::class, 'file'])
    ->middleware(['signed', 'throttle:60,1'])
    ->name('documents.preview-file');
