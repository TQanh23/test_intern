<x-admin-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <h2 class="font-display font-bold text-2xl tracking-tight text-ink sm:text-3xl">Đề thi</h2>
            <a href="{{ route('admin.exams.create') }}" class="inline-flex items-center rounded-lg bg-brand px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-brand-dark focus:outline-none focus:ring-2 focus:ring-brand focus:ring-offset-2">
                Tạo đề thi
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <x-auth-session-status :status="session('status')" />

            <form method="GET" action="{{ route('admin.exams.index') }}" class="flex gap-3">
                <x-text-input name="search" type="search" class="w-full rounded-lg sm:w-80" :value="$search" placeholder="Tìm theo tên đề" aria-label="Tìm theo tên đề" />
                <x-primary-button>Tìm</x-primary-button>
            </form>

            <div class="bg-white rounded-xl shadow-sm overflow-x-auto">
                @if ($exams->isEmpty())
                    <p class="p-6 text-slate-600">Không có đề thi nào.</p>
                @else
                    <table class="min-w-full divide-y divide-surface-high text-sm">
                        <thead class="bg-surface-low text-left text-xs uppercase tracking-wider text-slate-500">
                            <tr>
                                <th scope="col" class="px-6 py-3 font-semibold">Tên đề</th>
                                <th scope="col" class="px-6 py-3 font-semibold">Thời gian</th>
                                <th scope="col" class="px-6 py-3 font-semibold">Số câu</th>
                                <th scope="col" class="px-6 py-3 font-semibold"><span class="sr-only">Thao tác</span></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-surface-high">
                            @foreach ($exams as $exam)
                                <tr class="hover:bg-surface">
                                    <td class="px-6 py-4 font-medium text-ink">{{ $exam->name }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-slate-600">{{ $exam->duration_minutes }} phút</td>
                                    <td class="px-6 py-4"><span class="rounded-md bg-surface-high px-2 py-0.5 text-xs font-semibold text-ink">{{ $exam->questions_count }} câu</span></td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center justify-end gap-2">
                                            <a href="{{ route('admin.exams.edit', $exam) }}" class="rounded-lg bg-surface-high px-3 py-1 text-xs font-semibold text-brand hover:bg-surface-high">Sửa</a>
                                            <form method="POST" action="{{ route('admin.exams.destroy', $exam) }}" onsubmit="return confirm('Xóa đề này và toàn bộ câu hỏi của đề?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="rounded-lg bg-red-600 px-3 py-1 text-xs font-semibold text-white hover:bg-red-500">Xóa</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>

            <div>
                {{ $exams->links() }}
            </div>
        </div>
    </div>
</x-admin-layout>
