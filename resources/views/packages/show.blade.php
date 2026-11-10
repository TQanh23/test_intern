<x-app-layout>
    <x-slot name="header">
        {{-- Đường dẫn kiểu breadcrumb như màn "Chi tiết gói" trong Figma --}}
        <a href="{{ route('packages.index') }}" class="inline-flex items-center gap-1 rounded-lg bg-surface-low px-3 py-1 text-sm font-medium text-slate-600 hover:text-ink">
            <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" /></svg>
            Gói tài liệu IELTS
        </a>
    </x-slot>

    <div class="py-6">
        {{-- @js mã hoá slug lấy từ URL, an toàn khi đặt trong thuộc tính HTML và code JS. --}}
        <div x-data="packageDetail(@js($slug))" class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <x-async-state>
                <x-slot name="loading">
                    <div class="space-y-6">
                        <div class="flex flex-col gap-6 rounded-xl bg-white p-6 shadow-sm md:flex-row">
                            <div class="aspect-[4/3] w-full animate-pulse rounded-xl bg-slate-200 md:w-60"></div>
                            <div class="flex-1 space-y-3">
                                <div class="h-4 w-16 animate-pulse rounded-full bg-slate-200"></div>
                                <div class="h-7 w-2/3 animate-pulse rounded bg-slate-200"></div>
                                <div class="h-4 w-full animate-pulse rounded bg-slate-200"></div>
                                <div class="h-4 w-5/6 animate-pulse rounded bg-slate-200"></div>
                            </div>
                        </div>
                        <div class="h-48 animate-pulse rounded-xl bg-white shadow-sm"></div>
                    </div>
                    <span class="sr-only">Đang tải gói...</span>
                </x-slot>

                {{-- x-if: pkg là null cho đến khi tải xong, x-show sẽ báo lỗi khi đọc pkg.name. --}}
                <template x-if="pkg">
                    <div class="space-y-6">
                        {{-- Khối đầu trang: ảnh bìa có chip level bên trái, thông tin + giá + nút bên phải --}}
                        <section class="flex flex-col gap-8 rounded-xl bg-white p-6 shadow-sm md:flex-row">
                            <div class="relative aspect-[3/4] w-full shrink-0 overflow-hidden rounded-xl bg-surface-high shadow-md md:w-64">
                                <img x-show="pkg.cover_url" :src="pkg.cover_url" :alt="'Ảnh bìa ' + pkg.name" class="h-full w-full object-cover">
                                <span class="absolute left-3 top-3 rounded-md px-2.5 py-1 text-xs font-bold tracking-wide shadow-sm" :class="levelClass(pkg.level)" x-text="'Level ' + pkg.level"></span>
                            </div>
                            <div class="flex flex-1 flex-col gap-4">
                                <div class="flex flex-wrap gap-2 text-xs font-semibold">
                                    <span class="rounded-md bg-brand-pale px-2.5 py-1 text-brand">Có tài liệu xem thử miễn phí</span>
                                    <span class="rounded-md bg-surface-low px-2.5 py-1 text-slate-600" x-text="pkg.units.length + ' unit · ' + documentsCount + ' tài liệu'"></span>
                                </div>
                                <h1 class="font-display text-3xl font-bold leading-tight tracking-tight text-ink" x-text="pkg.name"></h1>
                                <p class="leading-relaxed text-slate-600" x-text="pkg.description"></p>

                                <div class="mt-auto space-y-4 rounded-xl bg-surface-low p-4">
                                    <p class="font-display text-3xl font-bold text-ink" x-text="formatPrice(pkg.price)"></p>
                                    <div class="flex flex-wrap gap-2">
                                        <a href="#muc-luc" class="inline-flex items-center gap-1 rounded-lg bg-brand px-4 py-2.5 text-sm font-semibold tracking-wide text-white shadow-sm hover:bg-brand-dark focus:outline-none focus-visible:ring-2 focus-visible:ring-brand focus-visible:ring-offset-2">
                                            Học thử miễn phí
                                        </a>
                                    </div>
                                    <p class="inline-flex items-center gap-1.5 text-sm text-slate-600">
                                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><rect x="5" y="11" width="14" height="10" rx="2" /><path d="M8 11V7a4 4 0 118 0v4" /></svg>
                                        Tài liệu đang khoá, bấm Xem thử để đọc hoặc nghe phần đầu.
                                    </p>
                                </div>
                            </div>
                        </section>

                        <section id="muc-luc" class="scroll-mt-24 space-y-3">
                            <div>
                                <h2 class="font-display text-xl font-bold text-ink">Mục lục tài liệu</h2>
                                <p class="text-sm text-slate-600">Mỗi tài liệu cho xem thử vài trang đầu (PDF) hoặc nghe thử vài giây đầu (audio).</p>
                            </div>

                            <p x-show="!pkg.units.length" class="rounded-xl bg-white px-6 py-10 text-center text-slate-500 shadow-sm">Gói này chưa có unit nào.</p>

                            <template x-for="(unit, index) in pkg.units" :key="unit.id">
                                {{-- Mỗi unit là một card riêng, đầu card có số thứ tự như Figma --}}
                                <div class="overflow-hidden rounded-xl bg-white shadow-sm">
                                    <h3>
                                        <button type="button" x-on:click="toggle(unit.id)" :aria-expanded="isOpen(unit.id)" :aria-controls="'unit-' + unit.id"
                                            class="flex w-full items-center gap-3 px-4 py-4 text-left hover:bg-surface focus:outline-none focus-visible:bg-surface sm:px-6">
                                            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-surface-low text-sm font-bold text-ink" x-text="String(index + 1).padStart(2, '0')"></span>
                                            <span class="flex-1 font-semibold text-ink" x-text="unit.title"></span>
                                            <span class="text-sm text-slate-500" x-text="unit.documents.length + ' tài liệu'"></span>
                                            <svg class="h-5 w-5 text-slate-400 transition-transform" :class="isOpen(unit.id) && 'rotate-180'" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                            </svg>
                                        </button>
                                    </h3>

                                    <ul :id="'unit-' + unit.id" x-show="isOpen(unit.id)" x-transition.opacity class="space-y-2 border-t border-surface-high bg-surface/50 p-3 sm:p-4">
                                        <template x-for="doc in unit.documents" :key="doc.id">
                                            <li class="flex items-center gap-3 rounded-lg bg-white px-4 py-3 ring-1 ring-surface-high">
                                                <x-ielts.file-icon />
                                                <div class="min-w-0 flex-1">
                                                    <p class="truncate font-medium text-ink" x-text="doc.title"></p>
                                                    <p class="truncate text-xs text-slate-500" x-text="describeDocument(doc)"></p>
                                                </div>
                                                {{-- Chưa có chức năng mua gói nên mọi tài liệu đều khoá. Khi có thì API trả thêm cờ đã mở khoá. --}}
                                                <svg class="h-5 w-5 shrink-0 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" role="img" aria-label="Chưa mở khoá">
                                                    <title>Chưa mở khoá</title>
                                                    <rect x="5" y="11" width="14" height="10" rx="2" /><path d="M8 11V7a4 4 0 118 0v4" />
                                                </svg>
                                                {{-- PDF: nút navy "Xem thử", audio: nút xanh ngọc "Nghe thử" --}}
                                                <button type="button" x-on:click="preview(doc)"
                                                    :class="doc.type === 'pdf' ? 'bg-ink hover:bg-ink/90' : 'bg-brand hover:bg-brand-dark'"
                                                    class="shrink-0 rounded-lg px-4 py-1.5 text-sm font-semibold text-white shadow-sm focus:outline-none focus-visible:ring-2 focus-visible:ring-brand focus-visible:ring-offset-2"
                                                    x-text="doc.type === 'pdf' ? 'Xem thử' : 'Nghe thử'">
                                                    Xem thử
                                                </button>
                                            </li>
                                        </template>
                                        <li x-show="!unit.documents.length" class="px-2 py-2 text-sm text-slate-500">Unit này chưa có tài liệu.</li>
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
