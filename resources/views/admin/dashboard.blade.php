<x-admin-layout>
    <x-slot name="header">
        <p class="text-xs font-semibold uppercase tracking-wider text-brand">Trung tâm điều hành</p>
        <h2 class="font-display font-bold text-2xl tracking-tight text-ink sm:text-3xl">
            Quản trị
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <div class="rounded-xl bg-ink p-6 text-white shadow-sm">
                <p>Xin chào {{ auth()->user()->name }}. Chỉ tài khoản admin mới vào được trang này.</p>
            </div>

            {{-- Ba ô lối tắt kiểu thẻ số liệu trong Figma: nhãn chữ hoa nhỏ, tên lớn, mũi tên --}}
            <div class="grid gap-4 sm:grid-cols-3">
                @foreach ([
                    ['Đề thi', 'Quản lý đề thi', 'admin.exams.index'],
                    ['MongoDB', 'Thống kê', 'admin.statistics'],
                    ['Gói IELTS', 'Lượt xem thử gói IELTS', 'admin.preview-statistics'],
                ] as [$label, $title, $routeName])
                    <a href="{{ route($routeName) }}" class="group rounded-xl bg-white p-6 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md focus:outline-none focus-visible:ring-2 focus-visible:ring-brand">
                        <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">{{ $label }}</p>
                        <p class="mt-2 font-display text-xl font-bold text-ink group-hover:text-brand">{{ $title }} →</p>
                    </a>
                @endforeach
            </div>
        </div>
    </div>
</x-admin-layout>
