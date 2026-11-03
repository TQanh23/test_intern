<?php

namespace App\Console\Commands;

use App\Services\AttemptService;
use Illuminate\Console\Command;

/**
 * Chạy mỗi phút qua container `scheduler` (xem routes/console.php và docker-compose.yml).
 */
class CloseExpiredAttempts extends Command
{
    protected $signature = 'app:close-expired-attempts';

    protected $description = 'Chấm các bài đã hết giờ mà học sinh chưa nộp, theo đáp án đã lưu';

    public function handle(AttemptService $attemptService): int
    {
        $this->info("Đã chấm {$attemptService->closeExpired()} bài.");

        return self::SUCCESS;
    }
}
