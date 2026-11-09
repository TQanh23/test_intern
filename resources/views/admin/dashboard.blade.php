<x-admin-layout>
    <x-slot name="header">
        <h2 class="font-extrabold text-2xl text-slate-900">
            Quản trị
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <div class="rounded-2xl bg-gradient-to-r from-sky-500 to-indigo-500 p-6 text-white shadow-sm">
                <p>Xin chào {{ auth()->user()->name }}. Chỉ tài khoản admin mới vào được trang này.</p>
            </div>

            <div class="grid gap-4 sm:grid-cols-3">
                <a href="{{ route('admin.exams.index') }}" class="rounded-2xl bg-sky-100 p-6 font-extrabold text-sky-800 shadow-sm transition hover:bg-sky-200">
                    Quản lý đề thi
                </a>
                <a href="{{ route('admin.statistics') }}" class="rounded-2xl bg-violet-100 p-6 font-extrabold text-violet-800 shadow-sm transition hover:bg-violet-200">
                    Thống kê
                </a>
                <a href="{{ route('admin.preview-statistics') }}" class="rounded-2xl bg-amber-100 p-6 font-extrabold text-amber-800 shadow-sm transition hover:bg-amber-200">
                    Lượt xem thử gói IELTS
                </a>
            </div>
        </div>
    </div>
</x-admin-layout>
