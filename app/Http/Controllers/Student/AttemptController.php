<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Attempt;
use App\Models\Exam;
use App\Services\AttemptService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AttemptController extends Controller
{
    public function __construct(private AttemptService $attemptService) {}

    public function store(Request $request, Exam $exam): RedirectResponse
    {
        $attempt = $this->attemptService->start($request->user(), $exam);

        return redirect()->route('attempts.show', $attempt);
    }

    public function show(Attempt $attempt): View
    {
        return view('student.attempts.show', ['attempt' => $attempt]);
    }
}
