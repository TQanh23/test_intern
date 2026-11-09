<x-admin-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <h2 class="font-extrabold text-2xl text-slate-900">Sửa câu hỏi</h2>
            <a href="{{ route('admin.exams.edit', $question->exam_id) }}" class="rounded-full bg-white px-4 py-2 text-sm font-semibold text-slate-700 ring-1 ring-slate-200 hover:bg-slate-50">
                Quay lại đề
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white rounded-2xl shadow-sm p-6">
                @include('admin.questions._form', ['action' => route('admin.questions.update', $question), 'method' => 'PUT'])
            </div>
        </div>
    </div>
</x-admin-layout>
