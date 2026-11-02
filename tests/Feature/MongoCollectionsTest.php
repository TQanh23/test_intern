<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\Attempt;
use Carbon\CarbonInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class MongoCollectionsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Chặn việc xóa nhầm dữ liệu dev nếu phpunit.xml thiếu MONGODB_DATABASE.
        $this->assertSame('exam_testing', DB::connection('mongodb')->getDatabase()->getDatabaseName());

        // Dùng truncate vì drop() xóa cả index, mà RefreshDatabase chỉ migrate một lần.
        Attempt::truncate();
        ActivityLog::truncate();
    }

    private function createAttempt(): Attempt
    {
        return Attempt::create([
            'user_id' => 5,
            'exam_id' => 2,
            'exam_name' => 'Đề 1',
            'duration_minutes' => 30,
            'status' => 'submitted',
            'started_at' => now()->subMinutes(10),
            'deadline_at' => now()->addMinutes(20),
            'submitted_at' => now(),
            'score' => 1,
            'total' => 1,
            'questions' => [
                [
                    'question_id' => 10,
                    'content' => '1 + 1 = ?',
                    'options' => [
                        ['option_id' => 100, 'content' => '2', 'is_correct' => true],
                        ['option_id' => 101, 'content' => '3', 'is_correct' => false],
                    ],
                    'selected_option_id' => 100,
                    'is_correct' => true,
                ],
            ],
        ]);
    }

    public function test_attempt_keeps_its_snapshot_after_reading_back(): void
    {
        $attempt = Attempt::find($this->createAttempt()->id);

        $this->assertSame(5, $attempt->user_id);
        $this->assertSame('Đề 1', $attempt->exam_name);
        $this->assertInstanceOf(CarbonInterface::class, $attempt->started_at);
        $this->assertInstanceOf(CarbonInterface::class, $attempt->deadline_at);
        $this->assertInstanceOf(CarbonInterface::class, $attempt->submitted_at);
        $this->assertTrue($attempt->questions[0]['options'][0]['is_correct']);
        $this->assertSame(100, $attempt->questions[0]['selected_option_id']);
    }

    public function test_questions_are_stored_as_a_real_array(): void
    {
        $this->createAttempt();

        // Chuỗi JSON sẽ không khớp truy vấn này, và $unwind ở task 14 cũng sẽ hỏng.
        $this->assertSame(1, Attempt::where('questions.question_id', 10)->count());
    }

    public function test_activity_log_has_created_at_but_no_updated_at(): void
    {
        $log = ActivityLog::create([
            'user_id' => 5,
            'action' => 'attempt.submitted',
            'exam_id' => 2,
            'details' => ['score' => 1],
        ]);

        $stored = ActivityLog::find($log->id);

        $this->assertInstanceOf(CarbonInterface::class, $stored->created_at);
        $this->assertArrayNotHasKey('updated_at', $stored->getAttributes());
    }

    public function test_both_collections_have_the_required_indexes(): void
    {
        foreach (['attempts', 'activity_logs'] as $collection) {
            $indexes = [];

            foreach (DB::connection('mongodb')->getCollection($collection)->listIndexes() as $index) {
                $indexes[] = $index->getName();
            }

            foreach (['user_id_1', 'exam_id_1', 'created_at_1'] as $expected) {
                $this->assertContains($expected, $indexes, $collection);
            }
        }
    }
}
