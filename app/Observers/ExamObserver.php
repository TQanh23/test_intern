<?php

namespace App\Observers;

use App\Models\ActivityLog;
use App\Models\Exam;
use Illuminate\Support\Arr;

/**
 * Admin controller gọi thẳng Exam::create/update/delete, nên ghi log qua model event của Exam.
 */
class ExamObserver
{
    public function created(Exam $exam): void
    {
        $this->log($exam, 'exam.created', ['name' => $exam->name]);
    }

    public function updated(Exam $exam): void
    {
        $this->log($exam, 'exam.updated', [
            'name' => $exam->name,
            'changes' => Arr::except($exam->getChanges(), 'updated_at'),
        ]);
    }

    public function deleted(Exam $exam): void
    {
        $this->log($exam, 'exam.deleted', ['name' => $exam->name]);
    }

    /**
     * @param  array<string, mixed>  $details
     */
    private function log(Exam $exam, string $action, array $details): void
    {
        // auth()->id() là null khi đề được tạo từ seeder.
        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => $action,
            'exam_id' => $exam->id,
            'details' => $details,
        ]);
    }
}
