<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Quản trị
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <p>Xin chào {{ auth()->user()->name }}. Chỉ tài khoản admin mới vào được trang này.</p>
                    <div class="mt-4 flex gap-6">
                        <a href="{{ route('admin.exams.index') }}" class="font-medium text-indigo-600 hover:text-indigo-800">
                            Quản lý đề thi
                        </a>
                        <a href="{{ route('admin.statistics') }}" class="font-medium text-indigo-600 hover:text-indigo-800">
                            Thống kê
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
