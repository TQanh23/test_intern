<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-2">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ $attempt->exam_name }}</h2>
            <p class="text-sm text-gray-600">
                Hạn nộp: {{ $attempt->deadline_at->timezone('Asia/Ho_Chi_Minh')->format('H:i d/m/Y') }}
            </p>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <x-auth-session-status class="px-4 sm:px-0" :status="session('status')" />
            <x-input-error class="px-4 sm:px-0" :messages="$errors->get('attempt')" />

            @if ($attempt->status === 'submitted')
                <div class="bg-white shadow-sm sm:rounded-lg p-6">
                    <p class="text-gray-900">Kết quả: <span class="font-semibold">{{ $attempt->score }}/{{ $attempt->total }}</span> câu đúng.</p>
                </div>
            @elseif ($attempt->isExpired())
                <div class="bg-white shadow-sm sm:rounded-lg p-6">
                    <p class="text-gray-900">Đã hết giờ làm bài. Bài này không được chấm điểm.</p>
                </div>
            @else
                <form method="POST" action="{{ route('attempts.submit', $attempt) }}" class="space-y-6">
                    @csrf

                    @foreach ($attempt->questions as $question)
                        <fieldset class="bg-white shadow-sm sm:rounded-lg p-6">
                            <legend class="sr-only">Câu {{ $loop->iteration }}</legend>
                            <p class="font-medium text-gray-900">{{ $loop->iteration }}. {{ $question['content'] }}</p>

                            <div class="mt-4 space-y-2">
                                @foreach ($question['options'] as $option)
                                    <label class="flex items-center gap-3 text-gray-700">
                                        <input type="radio" name="answers[{{ $question['question_id'] }}]" value="{{ $option['option_id'] }}" class="border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                        <span>{{ $option['content'] }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </fieldset>
                    @endforeach

                    <div class="px-4 sm:px-0">
                        <x-primary-button>Nộp bài</x-primary-button>
                    </div>
                </form>
            @endif
        </div>
    </div>
</x-app-layout>
