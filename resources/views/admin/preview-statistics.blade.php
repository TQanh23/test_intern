<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Lượt xem thử gói IELTS</h2>
    </x-slot>

    <div class="py-8 sm:py-12">
        <div x-data="previewStats" class="mx-auto max-w-7xl space-y-4 px-4 sm:px-6 lg:px-8">
            <div class="flex flex-wrap items-center justify-between gap-2">
                <p class="text-sm text-gray-600">Mỗi lần bấm Xem thử tính một lượt. Top 10 gói và top 10 tài liệu.</p>
                <button type="button" x-on:click="load()" :disabled="status === 'loading'"
                    class="rounded-md bg-white px-3 py-1.5 text-sm font-medium text-gray-700 ring-1 ring-gray-300 hover:bg-gray-50 disabled:opacity-40">
                    Làm mới
                </button>
            </div>

            <x-async-state>
                <x-slot name="empty">Chưa có lượt xem thử nào.</x-slot>

                <template x-if="stats">
                    <div class="grid gap-6 lg:grid-cols-2">
                        <section class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-200">
                            <h3 class="border-b border-gray-200 px-6 py-4 font-semibold text-gray-900">Gói được xem nhiều nhất</h3>
                            <ol class="divide-y divide-gray-100">
                                <template x-for="(row, index) in stats.packages" :key="row.package_id">
                                    <li class="px-6 py-3">
                                        <div class="flex items-baseline justify-between gap-3 text-sm">
                                            <span class="min-w-0 truncate font-medium text-gray-900" x-text="(index + 1) + '. ' + row.package_name"></span>
                                            <span class="shrink-0 tabular-nums text-gray-600" x-text="row.views + ' lượt'"></span>
                                        </div>
                                        <div class="mt-2 h-2 rounded-full bg-gray-100">
                                            <div class="h-2 rounded-full bg-indigo-500" :style="{ width: barWidth(stats.packages, row) }"></div>
                                        </div>
                                    </li>
                                </template>
                            </ol>
                        </section>

                        <section class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-200">
                            <h3 class="border-b border-gray-200 px-6 py-4 font-semibold text-gray-900">Tài liệu được xem nhiều nhất</h3>
                            <ol class="divide-y divide-gray-100">
                                <template x-for="(row, index) in stats.documents" :key="row.document_id">
                                    <li class="px-6 py-3">
                                        <div class="flex items-baseline justify-between gap-3 text-sm">
                                            <span class="min-w-0 truncate">
                                                <span class="font-medium text-gray-900" x-text="(index + 1) + '. ' + row.document_title"></span>
                                                <span class="text-gray-500" x-text="'· ' + row.package_name"></span>
                                            </span>
                                            <span class="shrink-0 tabular-nums text-gray-600" x-text="row.views + ' lượt'"></span>
                                        </div>
                                        <div class="mt-2 h-2 rounded-full bg-gray-100">
                                            <div class="h-2 rounded-full bg-teal-500" :style="{ width: barWidth(stats.documents, row) }"></div>
                                        </div>
                                    </li>
                                </template>
                            </ol>
                        </section>
                    </div>
                </template>
            </x-async-state>
        </div>
    </div>
</x-app-layout>
