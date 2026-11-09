<x-app-layout>
    <x-slot name="header">
        <h2 class="font-extrabold text-2xl text-slate-900 leading-tight">Làm bài</h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            @if ($exams->isEmpty())
                <p class="bg-white rounded-2xl shadow-sm p-6 text-slate-600">Hiện chưa có đề thi nào.</p>
            @else
                <div class="grid gap-4 sm:grid-cols-2">
                    @foreach ($exams as $exam)
                        {{-- Nền thẻ xoay vòng 4 màu pastel; viết đủ tên class để Tailwind quét được. --}}
                        <div class="{{ ['bg-sky-50', 'bg-violet-50', 'bg-amber-50', 'bg-emerald-50'][$loop->index % 4] }} rounded-2xl shadow-sm p-6 flex flex-col gap-4">
                            <div>
                                <span class="rounded-full bg-white px-2.5 py-0.5 text-xs font-semibold text-slate-700">{{ $exam->duration_minutes }} phút</span>
                                <p class="mt-3 text-lg font-extrabold text-slate-900">{{ $exam->name }}</p>
                                <p class="mt-1 text-sm text-slate-600">{{ $exam->questions_count }} câu</p>
                            </div>
                            <form method="POST" action="{{ route('attempts.store', $exam) }}">
                                @csrf
                                <x-primary-button>Bắt đầu →</x-primary-button>
                            </form>
                        </div>
                    @endforeach
                </div>
            @endif

            <div>
                {{ $exams->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
