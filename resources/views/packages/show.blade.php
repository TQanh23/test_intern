<x-app-layout>
    <x-slot name="header">
        <a href="{{ route('packages.index') }}" class="inline-flex items-center gap-1 text-sm font-medium text-slate-500 hover:text-slate-700">
            <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" /></svg>
            Gói tài liệu IELTS
        </a>
    </x-slot>

    <div class="py-6">
        {{-- @js mã hoá slug lấy từ URL, an toàn khi đặt trong thuộc tính HTML và code JS. --}}
        <div x-data="packageDetail(@js($slug))" class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            <x-async-state>
                <x-slot name="loading">
                    <div class="space-y-6">
                        <div class="flex flex-col gap-6 rounded-2xl bg-white p-6 shadow-sm md:flex-row">
                            <div class="aspect-[4/3] w-full animate-pulse rounded-xl bg-slate-200 md:w-60"></div>
                            <div class="flex-1 space-y-3">
                                <div class="h-4 w-16 animate-pulse rounded-full bg-slate-200"></div>
                                <div class="h-7 w-2/3 animate-pulse rounded bg-slate-200"></div>
                                <div class="h-4 w-full animate-pulse rounded bg-slate-200"></div>
                                <div class="h-4 w-5/6 animate-pulse rounded bg-slate-200"></div>
                            </div>
                        </div>
                        <div class="h-48 animate-pulse rounded-2xl bg-white shadow-sm"></div>
                    </div>
                    <span class="sr-only">Đang tải gói...</span>
                </x-slot>

                {{-- x-if: pkg là null cho đến khi tải xong, x-show sẽ báo lỗi khi đọc pkg.name. --}}
                <template x-if="pkg">
                    <div class="space-y-6">
                        <section class="flex flex-col gap-6 rounded-2xl bg-white p-6 shadow-sm md:flex-row">
                            <div class="aspect-[4/3] w-full shrink-0 overflow-hidden rounded-xl bg-gradient-to-br from-sky-100 to-indigo-100 md:w-60">
                                <img x-show="pkg.cover_url" :src="pkg.cover_url" :alt="'Ảnh bìa ' + pkg.name" class="h-full w-full object-cover">
                            </div>
                            <div class="flex flex-1 flex-col gap-3">
                                <span class="w-fit rounded-full px-2.5 py-0.5 text-xs font-semibold" :class="levelClass(pkg.level)" x-text="'Level ' + pkg.level"></span>
                                <h1 class="text-2xl font-bold text-slate-900" x-text="pkg.name"></h1>
                                <p class="text-slate-600" x-text="pkg.description"></p>
                                <p class="text-sm text-slate-500" x-text="pkg.units.length + ' unit · ' + documentsCount + ' tài liệu'"></p>
                                <div class="mt-auto flex flex-wrap items-center gap-x-4 gap-y-2 pt-2">
                                    <span class="text-2xl font-bold text-slate-900" x-text="formatPrice(pkg.price)"></span>
                                    <span class="inline-flex items-center gap-1.5 text-sm text-amber-700">
                                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><rect x="5" y="11" width="14" height="10" rx="2" /><path d="M8 11V7a4 4 0 118 0v4" /></svg>
                                        Tài liệu đang khoá, bấm Xem thử để đọc hoặc nghe phần đầu.
                                    </span>
                                </div>
                            </div>
                        </section>

                        <section class="overflow-hidden rounded-2xl bg-white shadow-sm">
                            <h2 class="border-b border-slate-100 px-4 py-4 text-lg font-semibold text-slate-900 sm:px-6">Mục lục</h2>

                            <p x-show="!pkg.units.length" class="px-6 py-10 text-center text-slate-500">Gói này chưa có unit nào.</p>

                            <template x-for="unit in pkg.units" :key="unit.id">
                                <div class="border-b border-slate-100 last:border-b-0">
                                    <h3>
                                        <button type="button" x-on:click="toggle(unit.id)" :aria-expanded="isOpen(unit.id)" :aria-controls="'unit-' + unit.id"
                                            class="flex w-full items-center gap-3 px-4 py-4 text-left hover:bg-slate-50 focus:outline-none focus-visible:bg-slate-50 sm:px-6">
                                            <span class="flex-1 font-medium text-slate-900" x-text="unit.title"></span>
                                            <span class="text-sm text-slate-500" x-text="unit.documents.length + ' tài liệu'"></span>
                                            <svg class="h-5 w-5 text-slate-400 transition-transform" :class="isOpen(unit.id) && 'rotate-180'" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                            </svg>
                                        </button>
                                    </h3>

                                    <ul :id="'unit-' + unit.id" x-show="isOpen(unit.id)" x-transition.opacity class="divide-y divide-slate-100 bg-slate-50/50">
                                        <template x-for="doc in unit.documents" :key="doc.id">
                                            <li class="flex items-center gap-3 px-4 py-3 sm:px-6">
                                                <x-ielts.file-icon />
                                                <div class="min-w-0 flex-1">
                                                    <p class="truncate font-medium text-slate-900" x-text="doc.title"></p>
                                                    <p class="truncate text-xs text-slate-500" x-text="describeDocument(doc)"></p>
                                                </div>
                                                {{-- Chưa có chức năng mua gói nên mọi tài liệu đều khoá. Khi có thì API trả thêm cờ đã mở khoá. --}}
                                                <svg class="h-5 w-5 shrink-0 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" role="img" aria-label="Chưa mở khoá">
                                                    <title>Chưa mở khoá</title>
                                                    <rect x="5" y="11" width="14" height="10" rx="2" /><path d="M8 11V7a4 4 0 118 0v4" />
                                                </svg>
                                                <button type="button" x-on:click="preview(doc)"
                                                    class="shrink-0 rounded-full bg-slate-900 px-4 py-1.5 text-sm font-semibold text-white shadow-sm hover:bg-slate-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-sky-500 focus-visible:ring-offset-2">
                                                    Xem thử
                                                </button>
                                            </li>
                                        </template>
                                        <li x-show="!unit.documents.length" class="px-6 py-4 text-sm text-slate-500">Unit này chưa có tài liệu.</li>
                                    </ul>
                                </div>
                            </template>
                        </section>
                    </div>
                </template>
            </x-async-state>

            <x-ielts.preview-modal />
        </div>
    </div>
</x-app-layout>
