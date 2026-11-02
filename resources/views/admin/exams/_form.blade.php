<form method="POST" action="{{ $action }}" class="space-y-6">
    @csrf
    @method($method)

    <div>
        <x-input-label for="name" value="Tên đề" />
        <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $exam->name)" required maxlength="255" />
        <x-input-error class="mt-2" :messages="$errors->get('name')" />
    </div>

    <div>
        <x-input-label for="duration_minutes" value="Thời gian làm bài (phút)" />
        <x-text-input id="duration_minutes" name="duration_minutes" type="number" class="mt-1 block w-40" :value="old('duration_minutes', $exam->duration_minutes)" required min="1" max="600" />
        <x-input-error class="mt-2" :messages="$errors->get('duration_minutes')" />
    </div>

    <x-primary-button>Lưu</x-primary-button>
</form>
