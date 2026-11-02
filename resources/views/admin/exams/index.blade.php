<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Đề thi</h2>
            <a href="{{ route('admin.exams.create') }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-800">
                Tạo đề thi
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <x-auth-session-status class="px-4 sm:px-0" :status="session('status')" />

            <form method="GET" action="{{ route('admin.exams.index') }}" class="flex gap-3 px-4 sm:px-0">
                <x-text-input name="search" type="search" class="w-full sm:w-80" :value="$search" placeholder="Tìm theo tên đề" aria-label="Tìm theo tên đề" />
                <x-primary-button>Tìm</x-primary-button>
            </form>

            <div class="bg-white shadow-sm sm:rounded-lg overflow-x-auto">
                @if ($exams->isEmpty())
                    <p class="p-6 text-gray-600">Không có đề thi nào.</p>
                @else
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="text-left text-gray-500">
                            <tr>
                                <th scope="col" class="px-6 py-3 font-medium">Tên đề</th>
                                <th scope="col" class="px-6 py-3 font-medium">Thời gian</th>
                                <th scope="col" class="px-6 py-3 font-medium">Số câu</th>
                                <th scope="col" class="px-6 py-3 font-medium"><span class="sr-only">Thao tác</span></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach ($exams as $exam)
                                <tr>
                                    <td class="px-6 py-4 font-medium text-gray-900">{{ $exam->name }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-gray-600">{{ $exam->duration_minutes }} phút</td>
                                    <td class="px-6 py-4 text-gray-600">{{ $exam->questions_count }}</td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center justify-end gap-4">
                                            <a href="{{ route('admin.exams.edit', $exam) }}" class="font-medium text-indigo-600 hover:text-indigo-800">Sửa</a>
                                            <form method="POST" action="{{ route('admin.exams.destroy', $exam) }}" onsubmit="return confirm('Xóa đề này và toàn bộ câu hỏi của đề?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="font-medium text-red-600 hover:text-red-800">Xóa</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>

            <div class="px-4 sm:px-0">
                {{ $exams->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
