<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Throwable;

class CheckConnections extends Command
{
    protected $signature = 'app:check-connections';

    protected $description = 'Kiểm tra kết nối MySQL và MongoDB';

    public function handle(): int
    {
        $checks = [
            'MySQL' => fn () => DB::connection('mysql')->getPdo(),
            'MongoDB' => fn () => DB::connection('mongodb')->ping(),
        ];

        $failed = false;

        foreach ($checks as $name => $check) {
            try {
                $check();
                $this->info("{$name}: OK");
            } catch (Throwable $exception) {
                $failed = true;
                $this->error("{$name}: LỖI - {$exception->getMessage()}");
            }
        }

        return $failed ? self::FAILURE : self::SUCCESS;
    }
}
