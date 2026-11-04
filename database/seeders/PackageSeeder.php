<?php

namespace Database\Seeders;

use App\Models\Package;
use Database\Seeders\Support\SampleFiles;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

/**
 * 3 gói Mindset for IELTS, mỗi gói 3 unit, mỗi unit 1 PDF và 1 audio. File do SampleFiles tự sinh, không phải sách thật.
 */
class PackageSeeder extends Seeder
{
    private const UNITS = ['Daily life', 'Education', 'Environment'];

    public function run(): void
    {
        $wav = SampleFiles::wav(60);

        foreach ([1, 2, 3] as $level) {
            // updateOrCreate theo slug và title để chạy seed nhiều lần không nhân đôi dữ liệu.
            $package = Package::updateOrCreate(['slug' => "mindset-for-ielts-level-{$level}"], [
                'name' => "Mindset for IELTS Level {$level}",
                'level' => $level,
                'description' => "Tài liệu mẫu tự tạo cho level {$level}: mỗi unit có một bài đọc PDF và một bài nghe.",
            ]);

            foreach (self::UNITS as $index => $topic) {
                $position = $index + 1;
                $unit = $package->units()->updateOrCreate(['position' => $position], ['title' => "Unit {$position}: {$topic}"]);

                $pdfPath = "documents/level-{$level}/unit-{$position}.pdf";
                $audioPath = "documents/level-{$level}/unit-{$position}.wav";
                Storage::disk('local')->put($pdfPath, SampleFiles::pdf("Mindset for IELTS Level {$level} - Unit {$position}: {$topic}", 10));
                Storage::disk('local')->put($audioPath, $wav);

                $unit->documents()->updateOrCreate(['type' => 'pdf'], [
                    'title' => "Unit {$position} - Reading",
                    'file_path' => $pdfPath,
                    'page_count' => 10,
                    'preview_pages' => 3,
                ]);
                $unit->documents()->updateOrCreate(['type' => 'audio'], [
                    'title' => "Unit {$position} - Listening",
                    'file_path' => $audioPath,
                ]);
            }
        }
    }
}
