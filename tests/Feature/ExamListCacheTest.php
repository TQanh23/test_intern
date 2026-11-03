<?php

namespace Tests\Feature;

use App\Models\Exam;
use App\Models\User;
use App\Services\ExamService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ExamListCacheTest extends TestCase
{
    use RefreshDatabase;

    private ExamService $exams;

    protected function setUp(): void
    {
        parent::setUp();

        $this->exams = app(ExamService::class);
    }

    private function createExam(string $name = 'Toán'): Exam
    {
        $exam = Exam::create(['name' => $name, 'duration_minutes' => 30]);
        $exam->questions()->create(['content' => 'Câu 1']);

        return $exam;
    }

    /**
     * @return list<string>
     */
    private function availableNames(): array
    {
        return $this->exams->paginateAvailable()->pluck('name')->all();
    }

    private function admin(): User
    {
        return User::factory()->create(['role' => 'admin']);
    }

    public function test_second_request_reads_from_cache(): void
    {
        $this->createExam();
        $this->availableNames();

        $queries = 0;
        DB::listen(function () use (&$queries) {
            $queries++;
        });

        $this->assertSame(['Toán'], $this->availableNames());
        $this->assertSame(0, $queries);
    }

    public function test_creating_an_exam_with_a_question_refreshes_the_list(): void
    {
        $this->assertSame([], $this->availableNames());

        $this->createExam();

        $this->assertSame(['Toán'], $this->availableNames());
    }

    public function test_renaming_an_exam_refreshes_the_list(): void
    {
        $exam = $this->createExam();
        $this->availableNames();

        $this->actingAs($this->admin())->put("/admin/exams/{$exam->id}", ['name' => 'Toán nâng cao', 'duration_minutes' => 30]);

        $this->assertSame(['Toán nâng cao'], $this->availableNames());
    }

    public function test_deleting_the_last_question_removes_the_exam_from_the_list(): void
    {
        $exam = $this->createExam();
        $this->availableNames();

        $this->actingAs($this->admin())->delete('/admin/questions/'.$exam->questions()->value('id'));

        $this->assertSame([], $this->availableNames());
    }

    public function test_deleting_an_exam_refreshes_the_list(): void
    {
        $exam = $this->createExam();
        $this->createExam('Anh');
        $this->availableNames();

        $this->actingAs($this->admin())->delete("/admin/exams/{$exam->id}");

        $this->assertSame(['Anh'], $this->availableNames());
    }

    public function test_api_links_use_the_api_path_after_the_web_page_filled_the_cache(): void
    {
        $this->createExam();
        $student = User::factory()->create(['role' => 'student']);

        $this->actingAs($student)->get('/exams')->assertOk();

        Sanctum::actingAs($student);

        $this->getJson('/api/exams')
            ->assertOk()
            ->assertJsonPath('links.first', url('/api/exams').'?page=1');
    }
}
