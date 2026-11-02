<?php

use App\Http\Controllers\Admin\ExamController;
use App\Http\Controllers\Admin\QuestionController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Student\AttemptController;
use App\Http\Controllers\Student\ExamController as StudentExamController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', function () {
        return view('admin.dashboard');
    })->name('dashboard');

    Route::resource('exams', ExamController::class)->except('show');
    Route::resource('exams.questions', QuestionController::class)->shallow()->except(['index', 'show']);
});

Route::middleware(['auth', 'student'])->group(function () {
    Route::get('/exams', [StudentExamController::class, 'index'])->name('exams.index');
    Route::post('/exams/{exam}/attempts', [AttemptController::class, 'store'])->name('attempts.store');
    Route::get('/attempts/{attempt}', [AttemptController::class, 'show'])
        ->middleware('can:view,attempt')
        ->name('attempts.show');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
