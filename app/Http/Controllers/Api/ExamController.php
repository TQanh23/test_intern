<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ExamResource;
use App\Services\ExamService;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ExamController extends Controller
{
    public function __construct(private ExamService $examService) {}

    public function index(): AnonymousResourceCollection
    {
        return ExamResource::collection($this->examService->paginateAvailable());
    }
}
