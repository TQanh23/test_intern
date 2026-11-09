<x-app-layout>
    <x-slot name="header">
        <h2 class="font-extrabold text-2xl text-slate-900">Gói tài liệu IELTS</h2>
    </x-slot>

    <div class="py-6">
        <div x-data="packageList" class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div role="group" aria-label="Lọc theo level" class="flex flex-wrap gap-2">
                    <template x-for="option in levels" :key="option.value">
                        <button type="button" x-on:click="level = option.value" :aria-pressed="level === option.value"
                            :class="level === option.value ? 'bg-slate-900 text-white shadow-sm' : 'bg-white text-slate-700 ring-1 ring-slate-200 hover:bg-slate-50'"
                            class="rounded-full px-4 py-1.5 text-sm font-medium transition focus:outline-none focus-visible:ring-2 focus-visible:ring-sky-500"
                            x-text="option.label"></button>
                    </template>
                </div>

                <label class="relative block sm:w-72">
                    <span class="sr-only">Tìm gói theo tên</span>
                    <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd" />
                    </svg>
                    <input type="search" x-model.debounce.300ms="q" placeholder="Tìm theo tên gói..." maxlength="100"
                        class="w-full rounded-full border-slate-200 pl-9 text-sm shadow-sm focus:border-sky-500 focus:ring-sky-500">
                </label>
            </div>

            <x-async-state>
                <x-slot name="loading">
                    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
                        @for ($i = 0; $i < 3; $i++)
                            <div class="overflow-hidden rounded-2xl bg-white shadow-sm">
                                <div class="aspect-[4/3] animate-pulse bg-slate-200"></div>
                                <div class="space-y-3 p-5">
                                    <div class="h-4 w-16 animate-pulse rounded-full bg-slate-200"></div>
                                    <div class="h-5 w-3/4 animate-pulse rounded bg-slate-200"></div>
                                    <div class="h-4 w-1/3 animate-pulse rounded bg-slate-200"></div>
                                    <div class="h-6 w-1/2 animate-pulse rounded bg-slate-200"></div>
                                </div>
                            </div>
                        @endfor
                    </div>
                    <span class="sr-only">Đang tải danh sách gói...</span>
                </x-slot>

                <x-slot name="empty">
                    <p>Không tìm thấy gói nào phù hợp.</p>
                    <button type="button" x-on:click="clearFilters()" class="mt-3 text-sm font-medium text-sky-600 hover:text-sky-800">
                        Xoá bộ lọc
                    </button>
                </x-slot>

                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    <template x-for="pkg in packages" :key="pkg.id">
                        <x-ielts.package-card />
                    </template>
                </div>

                <nav x-show="meta && meta.last_page > 1" class="mt-8 flex items-center justify-center gap-3" aria-label="Phân trang">
                    <button type="button" x-on:click="goTo(page - 1)" :disabled="page <= 1"
                        class="rounded-full bg-white px-4 py-1.5 text-sm font-medium text-slate-700 ring-1 ring-slate-200 hover:bg-slate-50 disabled:opacity-40">
                        Trước
                    </button>
                    <span class="text-sm text-slate-600" x-text="'Trang ' + page + ' / ' + (meta?.last_page ?? 1)"></span>
                    <button type="button" x-on:click="goTo(page + 1)" :disabled="page >= (meta?.last_page ?? 1)"
                        class="rounded-full bg-white px-4 py-1.5 text-sm font-medium text-slate-700 ring-1 ring-slate-200 hover:bg-slate-50 disabled:opacity-40">
                        Sau
                    </button>
                </nav>
            </x-async-state>
        </div>
    </div>
</x-app-layout>
