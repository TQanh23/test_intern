<x-admin-layout>
    <x-slot name="header">
        <h2 class="font-extrabold text-2xl text-slate-900">Lượt xem thử gói IELTS</h2>
    </x-slot>

    <div class="py-6">
        <div x-data="previewStats" class="mx-auto max-w-7xl space-y-4 px-4 sm:px-6 lg:px-8">
            <div class="flex flex-wrap items-center justify-between gap-2">
                <p class="text-sm text-slate-600">Mỗi lần bấm Xem thử tính một lượt. Top 10 gói và top 10 tài liệu.</p>
                <button type="button" x-on:click="load()" :disabled="status === 'loading'"
                    class="rounded-full bg-white px-4 py-1.5 text-sm font-semibold text-slate-700 ring-1 ring-slate-200 hover:bg-slate-50 disabled:opacity-40">
                    Làm mới
                </button>
            </div>

            <x-async-state>
                <x-slot name="empty">Chưa có lượt xem thử nào.</x-slot>

                <template x-if="stats">
                    <div class="grid gap-6 lg:grid-cols-2">
                        <section class="overflow-hidden rounded-2xl bg-white shadow-sm">
                            <h3 class="border-b border-slate-100 px-6 py-4"><span class="rounded-full bg-sky-100 px-2.5 py-0.5 text-xs font-semibold text-sky-700">Gói được xem nhiều nhất</span></h3>
                            <ol class="divide-y divide-slate-100">
                                <template x-for="(row, index) in stats.packages" :key="row.package_id">
                                    <li class="px-6 py-3">
                                        <div class="flex items-baseline justify-between gap-3 text-sm">
                                            <span class="min-w-0 truncate font-medium text-slate-900" x-text="(index + 1) + '. ' + row.package_name"></span>
                                            <span class="shrink-0 tabular-nums text-slate-600" x-text="row.views + ' lượt'"></span>
                                        </div>
                                        <div class="mt-2 h-2 rounded-full bg-slate-100">
                                            <div class="h-2 rounded-full bg-gradient-to-r from-sky-700 to-indigo-600" :style="{ width: barWidth(stats.packages, row) }"></div>
                                        </div>
                                    </li>
                                </template>
                            </ol>
                        </section>

                        <section class="overflow-hidden rounded-2xl bg-white shadow-sm">
                            <h3 class="border-b border-slate-100 px-6 py-4"><span class="rounded-full bg-violet-100 px-2.5 py-0.5 text-xs font-semibold text-violet-700">Tài liệu được xem nhiều nhất</span></h3>
                            <ol class="divide-y divide-slate-100">
                                <template x-for="(row, index) in stats.documents" :key="row.document_id">
                                    <li class="px-6 py-3">
                                        <div class="flex items-baseline justify-between gap-3 text-sm">
                                            <span class="min-w-0 truncate">
                                                <span class="font-medium text-slate-900" x-text="(index + 1) + '. ' + row.document_title"></span>
                                                <span class="text-slate-500" x-text="'· ' + row.package_name"></span>
                                            </span>
                                            <span class="shrink-0 tabular-nums text-slate-600" x-text="row.views + ' lượt'"></span>
                                        </div>
                                        <div class="mt-2 h-2 rounded-full bg-slate-100">
                                            <div class="h-2 rounded-full bg-gradient-to-r from-sky-700 to-indigo-600" :style="{ width: barWidth(stats.documents, row) }"></div>
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
</x-admin-layout>
