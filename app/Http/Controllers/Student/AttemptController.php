<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Http\Requests\SaveAnswerRequest;
use App\Http\Requests\SubmitAttemptRequest;
use App\Models\Attempt;
use App\Models\Exam;
use App\Services\AttemptService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
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

    public function saveAnswer(SaveAnswerRequest $request, Attempt $attempt): Response
    {
        $this->attemptService->saveAnswer($attempt, $request->integer('question_id'), $request->validated('option_id'));

        return response()->noContent();
    }

    public function submit(SubmitAttemptRequest $request, Attempt $attempt): RedirectResponse
    {
        $this->attemptService->submit($attempt, $request->validated('answers', []));

        return redirect()->route('attempts.show', $attempt)->with('status', 'Đã nộp bài.');
    }
}
