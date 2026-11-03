<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Chấm các bài bị bỏ dở sau khi hết giờ. Container `scheduler` chạy `schedule:work` để gọi lệnh này mỗi phút.
Schedule::command('app:close-expired-attempts')->everyMinute();
