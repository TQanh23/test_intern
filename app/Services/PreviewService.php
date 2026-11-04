<?php

namespace App\Services;

use App\Events\DocumentPreviewed;
use App\Models\Document;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use setasign\Fpdi\Fpdi;
use setasign\Fpdi\PdfParser\StreamReader;

class PreviewService
{
    public const URL_MINUTES = 5;

    public const AUDIO_PREVIEW_SECONDS = 15;

    /**
     * Cấp link xem thử có hạn và ghi một lượt xem. Link chỉ trỏ tới bản cắt, không trỏ tới file gốc.
     *
     * @return array{url: string, expires_at: string}
     */
    public function issueUrl(Document $document, User $user, ?string $ip): array
    {
        $expiresAt = now()->addMinutes(self::URL_MINUTES);

        DocumentPreviewed::dispatch($document->load('unit.package'), $user, $ip);

        return [
            'url' => URL::temporarySignedRoute('documents.preview-file', $expiresAt, ['document' => $document->id]),
            'expires_at' => $expiresAt->toIso8601String(),
        ];
    }

    /**
     * Đường dẫn bản cắt trong storage private. Lần đầu mở thì cắt rồi lưu lại, các lần sau dùng luôn file đó.
     * Trả về file thật để response()->file() hỗ trợ request Range: thiếu Range thì trình duyệt không cho tua audio.
     * Tên file có updated_at nên sửa tài liệu (ví dụ đổi preview_pages) sẽ tạo bản cắt mới.
     * shortcut: bản cắt cũ không bị xoá khi tài liệu đổi; khi có upload thật thì xoá trong lúc cập nhật tài liệu.
     */
    public function previewPath(Document $document): string
    {
        $extension = $document->type === 'pdf' ? 'pdf' : 'wav';
        $path = "previews/{$document->id}-{$document->updated_at->timestamp}.{$extension}";
        $disk = Storage::disk('local');

        if (! $disk->exists($path)) {
            $disk->put($path, $this->render($document));
        }

        return $disk->path($path);
    }

    /**
     * Đọc file gốc trong storage private rồi trả về bản cắt.
     */
    private function render(Document $document): string
    {
        $original = Storage::disk('local')->get($document->file_path);

        abort_if($original === null, 404);

        return $document->type === 'pdf'
            ? $this->cutPdf($original, $document->preview_pages)
            : $this->cutWav($original);
    }

    /**
     * Chép $pages trang đầu sang một PDF mới. FPDI bản free không đọc được PDF nén cross-reference (PDF 1.5+),
     * file do FPDF tạo ra thì đọc được.
     */
    private function cutPdf(string $original, int $pages): string
    {
        $pdf = new Fpdi;
        $total = $pdf->setSourceFile(StreamReader::createByString($original));

        for ($page = 1; $page <= min($pages, $total); $page++) {
            $pdf->AddPage();
            $pdf->useTemplate($pdf->importPage($page), adjustPageSize: true);
        }

        return $pdf->Output('S');
    }

    /**
     * Giữ AUDIO_PREVIEW_SECONDS giây đầu của WAV PCM rồi sửa hai trường kích thước trong header.
     * Chỉ đúng với header chuẩn 44 byte như file seeder tạo ra.
     */
    private function cutWav(string $original): string
    {
        // Byte 28-31 là byte rate: số byte cho một giây âm thanh.
        $byteRate = unpack('V', substr($original, 28, 4))[1];
        $data = substr($original, 44, $byteRate * self::AUDIO_PREVIEW_SECONDS);

        return substr($original, 0, 4).pack('V', 36 + strlen($data))
            .substr($original, 8, 32).pack('V', strlen($data))
            .$data;
    }
}
