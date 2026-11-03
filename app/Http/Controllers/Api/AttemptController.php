<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\SubmitAttemptRequest;
use App\Http\Resources\AttemptResource;
use App\Models\Attempt;
use App\Services\AttemptService;

class AttemptController extends Controller
{
    public function __construct(private AttemptService $attemptService) {}

    public function submit(SubmitAttemptRequest $request, Attempt $attempt): AttemptResource
    {
        return new AttemptResource($this->attemptService->submit($attempt, $request->validated('answers', [])));
    }
}
