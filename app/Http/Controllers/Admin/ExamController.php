<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\ExamRequest;
use App\Models\Exam;
use App\Services\ExamService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ExamController extends Controller
{
    public function __construct(private ExamService $examService) {}

    public function index(Request $request): View
    {
        $search = $request->string('search')->trim()->toString();

        return view('admin.exams.index', [
            'exams' => $this->examService->paginate($search),
            'search' => $search,
        ]);
    }

    public function create(): View
    {
        return view('admin.exams.create', ['exam' => new Exam]);
    }

    public function store(ExamRequest $request): RedirectResponse
    {
        $exam = Exam::create($request->validated());

        return redirect()->route('admin.exams.edit', $exam)->with('status', 'Đã tạo đề thi.');
    }

    public function edit(Exam $exam): View
    {
        return view('admin.exams.edit', ['exam' => $exam->load('questions.options')]);
    }

    public function update(ExamRequest $request, Exam $exam): RedirectResponse
    {
        $exam->update($request->validated());

        return redirect()->route('admin.exams.edit', $exam)->with('status', 'Đã cập nhật đề thi.');
    }

    public function destroy(Exam $exam): RedirectResponse
    {
        $exam->delete();

        return redirect()->route('admin.exams.index')->with('status', 'Đã xóa đề thi.');
    }
}
