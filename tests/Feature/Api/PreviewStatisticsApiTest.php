<?php

namespace Tests\Feature\Api;

use App\Models\PreviewLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class PreviewStatisticsApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->assertSame('exam_testing', DB::connection('mongodb')->getDatabase()->getDatabaseName());
        PreviewLog::truncate();
    }

    private function log(int $packageId, string $packageName, int $documentId, string $documentTitle): void
    {
        PreviewLog::create([
            'user_id' => 1, 'ip' => '127.0.0.1',
            'package_id' => $packageId, 'package_name' => $packageName,
            'document_id' => $documentId, 'document_title' => $documentTitle,
        ]);
    }

    public function test_student_gets_403(): void
    {
        Sanctum::actingAs(User::factory()->create(['role' => 'student']));

        $this->getJson('/api/admin/preview-stats')->assertForbidden();
    }

    public function test_counts_match_logs(): void
    {
        // Gói 1: tài liệu 10 xem 2 lần, tài liệu 11 xem 1 lần. Gói 2: tài liệu 20 xem 1 lần.
        $this->log(1, 'Level 1', 10, 'Reading 1');
        $this->log(1, 'Level 1', 10, 'Reading 1');
        $this->log(1, 'Level 1', 11, 'Listening 1');
        $this->log(2, 'Level 2', 20, 'Reading 2');
        Sanctum::actingAs(User::factory()->create(['role' => 'admin']));

        $this->getJson('/api/admin/preview-stats')
            ->assertOk()
            ->assertExactJson(['data' => [
                'packages' => [
                    ['package_id' => 1, 'package_name' => 'Level 1', 'views' => 3],
                    ['package_id' => 2, 'package_name' => 'Level 2', 'views' => 1],
                ],
                'documents' => [
                    ['document_id' => 10, 'document_title' => 'Reading 1', 'package_name' => 'Level 1', 'views' => 2],
                    ['document_id' => 11, 'document_title' => 'Listening 1', 'package_name' => 'Level 1', 'views' => 1],
                    ['document_id' => 20, 'document_title' => 'Reading 2', 'package_name' => 'Level 2', 'views' => 1],
                ],
            ]]);
    }
}
