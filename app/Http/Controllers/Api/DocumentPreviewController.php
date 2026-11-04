<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Document;
use App\Services\PreviewService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class DocumentPreviewController extends Controller
{
    public function __construct(private PreviewService $previewService) {}

    public function store(Request $request, Document $document): JsonResource
    {
        return new JsonResource($this->previewService->issueUrl($document, $request->user(), $request->ip()));
    }

    /**
     * Middleware `signed` đã từ chối link sai chữ ký hoặc hết hạn bằng 403 trước khi vào đây.
     */
    public function file(Document $document): BinaryFileResponse
    {
        return response()->file($this->previewService->previewPath($document), [
            'Content-Type' => $document->type === 'pdf' ? 'application/pdf' : 'audio/wav',
            'Content-Disposition' => 'inline',
            'Cache-Control' => 'private, no-store',
        ]);
    }
}
