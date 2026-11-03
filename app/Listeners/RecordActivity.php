<?php

namespace App\Listeners;

use App\Events\AttemptStarted;
use App\Events\AttemptSubmitted;
use App\Models\ActivityLog;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;

/**
 * Laravel tự đăng ký từng method theo kiểu event ở tham số, không cần khai báo thủ công.
 */
class RecordActivity
{
    public function handleLogin(Login $event): void
    {
        ActivityLog::create(['user_id' => $event->user->id, 'action' => 'auth.login']);
    }

    public function handleLogout(Logout $event): void
    {
        // Event Logout không ép kiểu user, có thể là null nếu gọi logout khi chưa đăng nhập.
        ActivityLog::create(['user_id' => $event->user?->id, 'action' => 'auth.logout']);
    }

    public function handleAttemptStarted(AttemptStarted $event): void
    {
        ActivityLog::create([
            'user_id' => $event->attempt->user_id,
            'action' => 'attempt.started',
            'exam_id' => $event->attempt->exam_id,
            'details' => ['attempt_id' => $event->attempt->id],
        ]);
    }

    public function handleAttemptSubmitted(AttemptSubmitted $event): void
    {
        ActivityLog::create([
            'user_id' => $event->attempt->user_id,
            'action' => 'attempt.submitted',
            'exam_id' => $event->attempt->exam_id,
            'details' => [
                'attempt_id' => $event->attempt->id,
                'score' => $event->attempt->score,
                'total' => $event->attempt->total,
            ],
        ]);
    }
}
