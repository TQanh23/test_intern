<?php

namespace Tests\Feature;

use App\Models\Document;
use App\Models\Exam;
use App\Models\Option;
use App\Models\Package;
use App\Models\Question;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DatabaseSeederTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // PackageSeeder ghi file PDF và WAV, giả lập disk để test không ghi vào storage thật.
        Storage::fake('local');
    }

    public function test_seeder_creates_admin_and_student_accounts(): void
    {
        $this->seed();

        $admin = User::where('email', 'admin@example.com')->firstOrFail();
        $student = User::where('email', 'student@example.com')->firstOrFail();

        $this->assertSame('admin', $admin->role);
        $this->assertSame('student', $student->role);
        $this->assertTrue(Hash::check('password', $admin->password));
        $this->assertTrue(Hash::check('password', $student->password));
    }

    public function test_seeder_creates_sample_exams(): void
    {
        $this->seed();

        $this->assertSame(2, Exam::count());
        $this->assertSame(9, Question::count());
        $this->assertSame(36, Option::count());
    }

    public function test_every_question_has_exactly_one_correct_option(): void
    {
        $this->seed();

        $questions = Question::withCount(['options as correct_count' => fn ($query) => $query->where('is_correct', true)])->get();

        $this->assertCount(9, $questions);

        foreach ($questions as $question) {
            $this->assertSame(1, $question->correct_count, $question->content);
        }
    }

    public function test_seeder_can_run_twice_without_duplicating_data(): void
    {
        $this->seed();
        $this->seed();

        $this->assertSame(2, User::count());
        $this->assertSame(2, Exam::count());
        $this->assertSame(9, Question::count());
        $this->assertSame(36, Option::count());
        $this->assertSame(3, Package::count());
        $this->assertSame(9, Unit::count());
        $this->assertSame(18, Document::count());
    }

    public function test_seeder_creates_ielts_packages_with_private_files(): void
    {
        $this->seed();

        $this->assertSame([1, 2, 3], Package::orderBy('level')->pluck('level')->all());
        $this->assertSame(9, Unit::count());
        $this->assertSame(9, Document::where('type', 'pdf')->where('page_count', 10)->where('preview_pages', 3)->count());
        $this->assertSame(9, Document::where('type', 'audio')->count());

        foreach (Document::pluck('file_path') as $path) {
            Storage::disk('local')->assertExists($path);
        }

        // Ảnh bìa nằm trong public/ và được commit, không do seeder sinh ra.
        foreach (Package::all() as $package) {
            $this->assertGreaterThan(0, $package->price);
            $this->assertFileExists(public_path($package->cover_image));
        }
    }
}
