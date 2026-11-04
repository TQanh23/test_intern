<?php

namespace App\Http\Resources;

use App\Models\Document;
use App\Services\PreviewService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Document
 */
class DocumentResource extends JsonResource
{
    /**
     * Không trả `file_path`: client chỉ lấy được bản xem thử qua link có chữ ký.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'type' => $this->type,
            'page_count' => $this->page_count,
            'preview_pages' => $this->preview_pages,
            // Lấy từ PreviewService để giao diện không phải tự ghi lại con số 15.
            'preview_seconds' => $this->type === 'audio' ? PreviewService::AUDIO_PREVIEW_SECONDS : null,
        ];
    }
}
