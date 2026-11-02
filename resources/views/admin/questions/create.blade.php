<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Thêm câu hỏi: {{ $exam->name }}</h2>
            <a href="{{ route('admin.exams.edit', $exam) }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-800">
                Quay lại đề
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                @include('admin.questions._form', ['action' => route('admin.exams.questions.store', $exam), 'method' => 'POST'])
            </div>
        </div>
    </div>
</x-app-layout>
