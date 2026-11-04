<?php

namespace Tests\Feature\Api;

use App\Models\Document;
use App\Models\Package;
use App\Models\PreviewLog;
use App\Models\User;
use Database\Seeders\Support\SampleFiles;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Testing\TestResponse;
use Laravel\Sanctum\Sanctum;
use setasign\Fpdi\Fpdi;
use setasign\Fpdi\PdfParser\StreamReader;
use Tests\TestCase;

class DocumentPreviewApiTest extends TestCase
{
    use RefreshDatabase;

    private Package $package;

    private Document $pdf;

    private Document $audio;

    protected function setUp(): void
    {
        parent::setUp();

        $this->assertSame('exam_testing', DB::connection('mongodb')->getDatabase()->getDatabaseName());
        PreviewLog::truncate();

        Storage::fake('local');
        Storage::disk('local')->put('documents/sample.pdf', SampleFiles::pdf('Sample', 10));
        Storage::disk('local')->put('documents/sample.wav', SampleFiles::wav(60));

        $this->package = Package::create(['name' => 'Mindset 1', 'slug' => 'mindset-1', 'level' => 1]);
        $unit = $this->package->units()->create(['title' => 'Unit 1', 'position' => 1]);
        $this->pdf = $unit->documents()->create([
            'title' => 'Reading', 'type' => 'pdf', 'file_path' => 'documents/sample.pdf',
            'page_count' => 10, 'preview_pages' => 3,
        ]);
        $this->audio = $unit->documents()->create([
            'title' => 'Listening', 'type' => 'audio', 'file_path' => 'documents/sample.wav',
        ]);
    }

    private function previewUrl(Document $document): string
    {
        Sanctum::actingAs(User::factory()->create());

        return $this->postJson("/api/documents/{$document->id}/preview")->assertOk()->json('data.url');
    }

    public function test_guest_gets_401(): void
    {
        $this->postJson("/api/documents/{$this->pdf->id}/preview")->assertUnauthorized();
    }

    public function test_unknown_document_gets_404(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $this->postJson('/api/documents/999/preview')->assertNotFound();
    }

    public function test_preview_returns_signed_url_with_expiry(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $this->postJson("/api/documents/{$this->pdf->id}/preview")
            ->assertOk()
            ->assertJsonStructure(['data' => ['url', 'expires_at']])
            ->assertJsonPath('data.url', fn (string $url) => str_contains($url, 'signature='));
    }

    public function test_pdf_preview_contains_only_preview_pages(): void
    {
        $response = $this->get($this->previewUrl($this->pdf))
            ->assertOk()
            ->assertHeader('Content-Type', 'application/pdf');

        $pages = (new Fpdi)->setSourceFile(StreamReader::createByString($this->body($response)));

        $this->assertSame(3, $pages);
    }

    /**
     * Response là file (BinaryFileResponse) nên getContent() trả false, phải đọc file mà nó trỏ tới.
     */
    private function body(TestResponse $response): string
    {
        return file_get_contents($response->baseResponse->getFile()->getPathname());
    }

    public function test_audio_preview_is_cut_to_15_seconds(): void
    {
        $body = $this->body($this->get($this->previewUrl($this->audio))
            ->assertOk()
            ->assertHeader('Content-Type', 'audio/wav'));

        $dataBytes = SampleFiles::WAV_SAMPLE_RATE * 15;

        $this->assertSame(44 + $dataBytes, strlen($body));
        // Hai trường kích thước trong header phải khớp phần dữ liệu đã cắt, nếu không trình phát đọc sai độ dài.
        $this->assertSame(36 + $dataBytes, unpack('V', substr($body, 4, 4))[1]);
        $this->assertSame($dataBytes, unpack('V', substr($body, 40, 4))[1]);
    }

    public function test_preview_supports_range_requests_so_audio_can_seek(): void
    {
        // Không hỗ trợ Range thì trình duyệt không cho tua thanh audio.
        $this->get($this->previewUrl($this->audio), ['Range' => 'bytes=0-99'])
            ->assertStatus(206)
            ->assertHeader('Accept-Ranges', 'bytes')
            ->assertHeader('Content-Length', '100');
    }

    public function test_preview_is_cut_once_and_reused(): void
    {
        $url = $this->previewUrl($this->pdf);

        $this->get($url)->assertOk();
        $this->get($url)->assertOk();

        $this->assertCount(1, Storage::disk('local')->files('previews'));
    }

    public function test_expired_url_gets_403(): void
    {
        $url = $this->previewUrl($this->pdf);

        $this->travel(6)->minutes();

        $this->get($url)->assertForbidden();
    }

    public function test_tampered_url_gets_403(): void
    {
        $url = $this->previewUrl($this->pdf);

        $this->get(str_replace("/documents/{$this->pdf->id}/", "/documents/{$this->audio->id}/", $url))->assertForbidden();
        $this->get(strtok($url, '?'))->assertForbidden();
    }

    public function test_original_file_has_no_public_url(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $this->get('/storage/documents/sample.pdf')->assertNotFound();
        $this->getJson('/api/packages/mindset-1')
            ->assertOk()
            ->assertJsonMissingPath('data.units.0.documents.0.file_path');
    }

    public function test_preview_writes_one_log(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $this->postJson("/api/documents/{$this->pdf->id}/preview")->assertOk();

        $this->assertSame(1, PreviewLog::count());
        $log = PreviewLog::first();
        $this->assertSame($user->id, $log->user_id);
        $this->assertSame('127.0.0.1', $log->ip);
        $this->assertSame($this->pdf->id, $log->document_id);
        $this->assertSame('Reading', $log->document_title);
        $this->assertSame($this->package->id, $log->package_id);
        $this->assertSame('Mindset 1', $log->package_name);
    }

    public function test_preview_is_rate_limited_per_user(): void
    {
        Sanctum::actingAs(User::factory()->create());

        for ($i = 0; $i < 10; $i++) {
            $this->postJson("/api/documents/{$this->pdf->id}/preview")->assertOk();
        }

        $this->postJson("/api/documents/{$this->pdf->id}/preview")->assertTooManyRequests();
    }
}
