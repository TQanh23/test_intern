<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class CheckConnectionsTest extends TestCase
{
    private function useWorkingMysql(): void
    {
        // phpunit.xml đặt DB_DATABASE=:memory:, nên test không kết nối được MySQL thật.
        config(['database.connections.mysql' => ['driver' => 'sqlite', 'database' => ':memory:']]);
    }

    public function test_it_reports_ok_when_both_databases_are_reachable(): void
    {
        $this->useWorkingMysql();

        $this->artisan('app:check-connections')
            ->expectsOutput('MySQL: OK')
            ->expectsOutput('MongoDB: OK')
            ->assertSuccessful();
    }

    public function test_it_still_checks_mongodb_when_mysql_fails(): void
    {
        config([
            'database.connections.mysql.host' => '127.0.0.1',
            'database.connections.mysql.port' => 1,
        ]);

        $this->artisan('app:check-connections')
            ->expectsOutputToContain('MySQL: LỖI')
            ->expectsOutput('MongoDB: OK')
            ->assertFailed();
    }

    public function test_it_reports_mongodb_failure(): void
    {
        $this->useWorkingMysql();

        config(['database.connections.mongodb.dsn' => 'mongodb://127.0.0.1:1']);
        DB::purge('mongodb');

        $this->artisan('app:check-connections')
            ->expectsOutput('MySQL: OK')
            ->expectsOutputToContain('MongoDB: LỖI')
            ->assertFailed();
    }
}
