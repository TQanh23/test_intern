<x-admin-layout>
    <x-slot name="header">
        <h2 class="font-extrabold text-2xl text-slate-900">Tạo đề thi</h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white rounded-2xl shadow-sm p-6">
                @include('admin.exams._form', ['action' => route('admin.exams.store'), 'method' => 'POST'])
            </div>
        </div>
    </div>
</x-admin-layout>
