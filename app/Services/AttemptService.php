<?php

namespace App\Services;

use App\Models\Attempt;
use App\Models\Exam;
use App\Models\Option;
use App\Models\Question;
use App\Models\User;

class AttemptService
{
    /**
     * Tạo lượt làm bài mới với câu hỏi đã trộn. Thời hạn do server tính.
     */
    public function start(User $user, Exam $exam): Attempt
    {
        $exam->load('questions.options');

        abort_if($exam->questions->isEmpty(), 422, 'Đề này chưa có câu hỏi.');

        $startedAt = now();

        return Attempt::create([
            'user_id' => $user->id,
            'exam_id' => $exam->id,
            'exam_name' => $exam->name,
            'duration_minutes' => $exam->duration_minutes,
            'status' => 'in_progress',
            'started_at' => $startedAt,
            'deadline_at' => $startedAt->copy()->addMinutes($exam->duration_minutes),
            'submitted_at' => null,
            'score' => null,
            'total' => null,
            'questions' => $exam->questions->shuffle()->map(fn (Question $question) => [
                'question_id' => $question->id,
                'content' => $question->content,
                'options' => $question->options->map(fn (Option $option) => [
                    'option_id' => $option->id,
                    'content' => $option->content,
                    'is_correct' => $option->is_correct,
                ])->all(),
                'selected_option_id' => null,
                'is_correct' => null,
            ])->all(),
        ]);
    }
}
