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
        </div>
    </div>
</x-app-layout>
