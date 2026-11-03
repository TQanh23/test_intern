<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\StatisticsService;
use Illuminate\View\View;

class StatisticsController extends Controller
{
    public function __construct(private StatisticsService $statisticsService) {}

    public function index(): View
    {
        return view('admin.statistics', [
            'exams' => $this->statisticsService->averageByExam(),
            'students' => $this->statisticsService->topStudents(),
            'questions' => $this->statisticsService->mostWrongQuestions(),
        ]);
    }
}
