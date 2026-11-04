<?php

namespace Database\Seeders\Support;

use FPDF;

/**
 * Tự sinh file PDF và WAV mẫu cho seeder và test, không dùng bản scan sách thật.
 */
class SampleFiles
{
    public const WAV_SAMPLE_RATE = 8000;

    /**
     * PDF có $pages trang, mỗi trang ghi tiêu đề và số trang để test đếm được trang nào được giữ lại.
     */
    public static function pdf(string $title, int $pages): string
    {
        $pdf = new FPDF;
        $pdf->SetFont('Helvetica', '', 20);

        for ($page = 1; $page <= $pages; $page++) {
            $pdf->AddPage();
            $pdf->Cell(0, 10, $title, ln: 1);
            $pdf->Cell(0, 10, "Page {$page} / {$pages}");
        }

        return $pdf->Output('S');
    }

    /**
     * WAV PCM 8 kHz, mono, 8-bit, phát một tiếng bíp 400 Hz dài $seconds giây.
     * Header chuẩn 44 byte: PreviewService dựa vào điều này để cắt audio.
     */
    public static function wav(int $seconds): string
    {
        // 400 Hz chia hết 8000 nên một chu kỳ đúng 20 mẫu, chỉ cần tính 20 mẫu rồi lặp lại.
        $period = '';
        for ($i = 0; $i < 20; $i++) {
            $period .= chr((int) round(128 + 100 * sin(2 * M_PI * $i / 20)));
        }

        $data = str_repeat($period, 400 * $seconds);

        // fmt: kích thước chunk 16, PCM (1), 1 kênh, sample rate, byte rate, block align 1, 8 bit.
        return 'RIFF'.pack('V', 36 + strlen($data)).'WAVE'
            .'fmt '.pack('VvvVVvv', 16, 1, 1, self::WAV_SAMPLE_RATE, self::WAV_SAMPLE_RATE, 1, 8)
            .'data'.pack('V', strlen($data)).$data;
    }
}
