<x-admin-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <h2 class="font-extrabold text-2xl text-slate-900">Sửa đề thi</h2>
            <a href="{{ route('admin.exams.index') }}" class="rounded-full bg-white px-4 py-2 text-sm font-semibold text-slate-700 ring-1 ring-slate-200 hover:bg-slate-50">
                Danh sách đề
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <x-auth-session-status :status="session('status')" />

            <div class="bg-white rounded-2xl shadow-sm p-6">
                @include('admin.exams._form', ['action' => route('admin.exams.update', $exam), 'method' => 'PUT'])
            </div>

            <div class="bg-white rounded-2xl shadow-sm p-6">
                <div class="flex items-center justify-between gap-4">
                    <h3 class="font-extrabold text-slate-900">Câu hỏi ({{ $exam->questions->count() }})</h3>
                    <a href="{{ route('admin.exams.questions.create', $exam) }}" class="rounded-full bg-white px-4 py-2 text-sm font-semibold text-slate-700 ring-1 ring-slate-200 hover:bg-slate-50">
                        Thêm câu hỏi
                    </a>
                </div>

                @if ($exam->questions->isEmpty())
                    <p class="mt-4 text-slate-600">Đề này chưa có câu hỏi nào.</p>
                @else
                    <ol class="mt-4 divide-y divide-slate-100">
                        @foreach ($exam->questions as $question)
                            <li class="py-4">
                                <p class="break-words font-medium text-slate-900">{{ $loop->iteration }}. {{ $question->content }}</p>
                                <ul class="mt-2 space-y-1 text-sm text-slate-600">
                                    @foreach ($question->options as $option)
                                        <li @class(['break-words font-semibold text-slate-900' => $option->is_correct])>
                                            {{ $option->content }}
                                            @if ($option->is_correct)
                                                <span class="ms-1 rounded-full bg-emerald-100 px-2.5 py-0.5 text-xs font-semibold text-emerald-700">(đáp án đúng)</span>
                                            @endif
                                        </li>
                                    @endforeach
                                </ul>
                                <div class="mt-3 flex items-center gap-2 text-sm">
                                    <a href="{{ route('admin.questions.edit', $question) }}" class="rounded-full bg-indigo-50 px-3 py-1 text-xs font-semibold text-indigo-700 hover:bg-indigo-100">Sửa</a>
                                    <form method="POST" action="{{ route('admin.questions.destroy', $question) }}" onsubmit="return confirm('Xóa câu hỏi này?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="rounded-full bg-red-600 px-3 py-1 text-xs font-semibold text-white hover:bg-red-500">Xóa</button>
                                    </form>
                                </div>
                            </li>
                        @endforeach
                    </ol>
                @endif
            </div>
        </div>
    </div>
</x-admin-layout>
