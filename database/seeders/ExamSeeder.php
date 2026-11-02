<?php

namespace Database\Seeders;

use App\Models\Exam;
use Illuminate\Database\Seeder;

class ExamSeeder extends Seeder
{
    /**
     * `correct` là vị trí của đáp án đúng trong `options`, đánh số từ 0.
     */
    private const EXAMS = [
        [
            'name' => 'Toán cơ bản',
            'duration_minutes' => 15,
            'questions' => [
                ['content' => '12 × 8 = ?', 'options' => ['84', '96', '108', '116'], 'correct' => 1],
                ['content' => 'Căn bậc hai của 144 là bao nhiêu?', 'options' => ['12', '14', '16', '18'], 'correct' => 0],
                ['content' => 'Tổng ba góc trong của một tam giác bằng bao nhiêu độ?', 'options' => ['90', '180', '270', '360'], 'correct' => 1],
                ['content' => '15% của 200 là bao nhiêu?', 'options' => ['15', '20', '30', '45'], 'correct' => 2],
                ['content' => 'Tìm x biết 2x + 6 = 14.', 'options' => ['3', '8', '10', '4'], 'correct' => 3],
            ],
        ],
        [
            'name' => 'Tiếng Anh cơ bản',
            'duration_minutes' => 10,
            'questions' => [
                ['content' => 'She ___ to school every day.', 'options' => ['go', 'goes', 'going', 'gone'], 'correct' => 1],
                ['content' => 'Dạng quá khứ của "write" là gì?', 'options' => ['writed', 'wrote', 'written', 'writes'], 'correct' => 1],
                ['content' => 'Từ nào đồng nghĩa với "happy"?', 'options' => ['sad', 'angry', 'glad', 'tired'], 'correct' => 2],
                ['content' => 'They ___ football now.', 'options' => ['is playing', 'plays', 'played', 'are playing'], 'correct' => 3],
            ],
        ],
    ];

    /**
     * Tạo đề mẫu. Đề đã có thì bỏ qua để không ghi đè nội dung admin đã sửa.
     */
    public function run(): void
    {
        foreach (self::EXAMS as $data) {
            if (Exam::where('name', $data['name'])->exists()) {
                continue;
            }

            $exam = Exam::create([
                'name' => $data['name'],
                'duration_minutes' => $data['duration_minutes'],
            ]);

            foreach ($data['questions'] as $questionData) {
                $question = $exam->questions()->create(['content' => $questionData['content']]);

                foreach ($questionData['options'] as $index => $content) {
                    $question->options()->create([
                        'content' => $content,
                        'is_correct' => $index === $questionData['correct'],
                    ]);
                }
            }
        }
    }
}
