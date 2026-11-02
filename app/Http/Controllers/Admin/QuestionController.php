<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\QuestionRequest;
use App\Models\Exam;
use App\Models\Question;
use App\Services\ExamService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class QuestionController extends Controller
{
    public function __construct(private ExamService $examService) {}

    public function create(Exam $exam): View
    {
        return view('admin.questions.create', ['exam' => $exam, 'question' => new Question]);
    }

    public function store(QuestionRequest $request, Exam $exam): RedirectResponse
    {
        $this->examService->createQuestion($exam, $request->validated());

        return redirect()->route('admin.exams.edit', $exam)->with('status', 'Đã thêm câu hỏi.');
    }

    public function edit(Question $question): View
    {
        return view('admin.questions.edit', ['question' => $question->load('options')]);
    }

    public function update(QuestionRequest $request, Question $question): RedirectResponse
    {
        $this->examService->updateQuestion($question, $request->validated());

        return redirect()->route('admin.exams.edit', $question->exam_id)->with('status', 'Đã cập nhật câu hỏi.');
    }

    public function destroy(Question $question): RedirectResponse
    {
        $question->delete();

        return redirect()->route('admin.exams.edit', $question->exam_id)->with('status', 'Đã xóa câu hỏi.');
    }
}
