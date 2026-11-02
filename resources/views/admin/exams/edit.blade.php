<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Sửa đề thi</h2>
            <a href="{{ route('admin.exams.index') }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-800">
                Danh sách đề
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <x-auth-session-status class="px-4 sm:px-0" :status="session('status')" />

            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                @include('admin.exams._form', ['action' => route('admin.exams.update', $exam), 'method' => 'PUT'])
            </div>

            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <div class="flex items-center justify-between gap-4">
                    <h3 class="font-semibold text-gray-800">Câu hỏi ({{ $exam->questions->count() }})</h3>
                    <a href="{{ route('admin.exams.questions.create', $exam) }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-800">
                        Thêm câu hỏi
                    </a>
                </div>

                @if ($exam->questions->isEmpty())
                    <p class="mt-4 text-gray-600">Đề này chưa có câu hỏi nào.</p>
                @else
                    <ol class="mt-4 divide-y divide-gray-100">
                        @foreach ($exam->questions as $question)
                            <li class="py-4">
                                <p class="font-medium text-gray-900">{{ $loop->iteration }}. {{ $question->content }}</p>
                                <ul class="mt-2 space-y-1 text-sm text-gray-600">
                                    @foreach ($question->options as $option)
                                        <li @class(['font-semibold text-green-700' => $option->is_correct])>
                                            {{ $option->content }}
                                            @if ($option->is_correct)
                                                (đáp án đúng)
                                            @endif
                                        </li>
                                    @endforeach
                                </ul>
                                <div class="mt-3 flex items-center gap-4 text-sm">
                                    <a href="{{ route('admin.questions.edit', $question) }}" class="font-medium text-indigo-600 hover:text-indigo-800">Sửa</a>
                                    <form method="POST" action="{{ route('admin.questions.destroy', $question) }}" onsubmit="return confirm('Xóa câu hỏi này?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="font-medium text-red-600 hover:text-red-800">Xóa</button>
                                    </form>
                                </div>
                            </li>
                        @endforeach
                    </ol>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
