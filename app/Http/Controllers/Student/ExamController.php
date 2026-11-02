<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Services\ExamService;
use Illuminate\View\View;

class ExamController extends Controller
{
    public function __construct(private ExamService $examService) {}

    public function index(): View
    {
        return view('student.exams.index', ['exams' => $this->examService->paginateAvailable()]);
    }
}
