<?php

namespace App\Http\Resources;

use App\Models\Attempt;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Attempt
 */
class AttemptResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'exam_id' => $this->exam_id,
            'exam_name' => $this->exam_name,
            'status' => $this->status,
            'started_at' => $this->started_at?->toIso8601String(),
            'deadline_at' => $this->deadline_at?->toIso8601String(),
            'submitted_at' => $this->submitted_at?->toIso8601String(),
            'score' => $this->score,
            'total' => $this->total,
            // Snapshot chứa is_correct của từng đáp án, chỉ trả khi đã nộp để không lộ đáp án.
            'questions' => $this->when($this->status === 'submitted', fn () => $this->questions),
        ];
    }
}
