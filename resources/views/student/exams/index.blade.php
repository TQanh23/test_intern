<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Làm bài</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white shadow-sm sm:rounded-lg">
                @if ($exams->isEmpty())
                    <p class="p-6 text-gray-600">Hiện chưa có đề thi nào.</p>
                @else
                    <ul class="divide-y divide-gray-100">
                        @foreach ($exams as $exam)
                            <li class="p-6 flex flex-wrap items-center justify-between gap-4">
                                <div>
                                    <p class="font-medium text-gray-900">{{ $exam->name }}</p>
                                    <p class="mt-1 text-sm text-gray-600">{{ $exam->questions_count }} câu, {{ $exam->duration_minutes }} phút</p>
                                </div>
                                <form method="POST" action="{{ route('attempts.store', $exam) }}">
                                    @csrf
                                    <x-primary-button>Bắt đầu</x-primary-button>
                                </form>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>

            <div class="px-4 sm:px-0">
                {{ $exams->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
