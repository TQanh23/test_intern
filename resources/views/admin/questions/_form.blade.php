@php
    $correctOption = old('correct_option', $question->options->search(fn ($option) => $option->is_correct));
@endphp

<form method="POST" action="{{ $action }}" class="space-y-6">
    @csrf
    @method($method)

    <div>
        <x-input-label for="content" value="Nội dung câu hỏi" />
        <textarea id="content" name="content" rows="3" required maxlength="5000" class="mt-1 block w-full border-slate-300 focus:border-brand focus:ring-brand rounded-xl shadow-sm">{{ old('content', $question->content) }}</textarea>
        <x-input-error class="mt-2" :messages="$errors->get('content')" />
    </div>

    <fieldset class="space-y-4">
        <legend class="font-medium text-sm text-slate-600">Đáp án (chọn đáp án đúng)</legend>

        @foreach (range(0, 3) as $index)
            <div>
                <div class="flex items-center gap-3">
                    <input type="radio" name="correct_option" value="{{ $index }}" required @checked((string) $correctOption === (string) $index) aria-label="Đáp án {{ $index + 1 }} là đáp án đúng" class="border-slate-300 text-brand focus:ring-brand">
                    <x-text-input name="options[]" type="text" class="block w-full" :value="old('options.'.$index, $question->options[$index]->content ?? '')" required maxlength="1000" aria-label="Đáp án {{ $index + 1 }}" />
                </div>
                <x-input-error class="mt-2" :messages="$errors->get('options.'.$index)" />
            </div>
        @endforeach

        <x-input-error :messages="$errors->get('options')" />
        <x-input-error :messages="$errors->get('correct_option')" />
    </fieldset>

    <x-primary-button>Lưu</x-primary-button>
</form>
