<x-app-layout>
    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            {{-- Banner chào, chỉ có lời chào và tên --}}
            <div class="rounded-2xl bg-gradient-to-br from-sky-700 to-indigo-600 text-white p-8">
                <h1 class="text-2xl font-extrabold">Chào {{ auth()->user()->name }} 👋</h1>
            </div>

            {{-- Lối tắt theo vai trò, giống menu trên cùng --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                @if (auth()->user()->isStudent())
                    <a href="{{ route('exams.index') }}" class="rounded-2xl bg-sky-100 p-6 hover:shadow-md transition">
                        <span class="text-lg font-extrabold text-sky-900">Làm bài</span>
                    </a>
                @endif
                @if (auth()->user()->isAdmin())
                    <a href="{{ route('admin.dashboard') }}" class="rounded-2xl bg-violet-100 p-6 hover:shadow-md transition">
                        <span class="text-lg font-extrabold text-violet-900">Quản trị</span>
                    </a>
                @endif
                <a href="{{ route('packages.index') }}" class="rounded-2xl bg-amber-100 p-6 hover:shadow-md transition">
                    <span class="text-lg font-extrabold text-amber-900">Gói IELTS</span>
                </a>
            </div>
        </div>
    </div>
</x-app-layout>
