<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\StatisticsService;
use Illuminate\Http\Resources\Json\JsonResource;

class PreviewStatisticsController extends Controller
{
    public function __invoke(StatisticsService $statistics): JsonResource
    {
        return new JsonResource([
            'packages' => $statistics->topPreviewedPackages(),
            'documents' => $statistics->topPreviewedDocuments(),
        ]);
    }
}
