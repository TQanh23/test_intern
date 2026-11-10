<x-app-layout>
    {{-- Băng tiêu đề nền xanh nhạt với hai đốm màu mờ, theo màn "Danh sách gói tài liệu" trong Figma --}}
    <section class="relative overflow-hidden bg-surface-low">
        <div class="pointer-events-none absolute -right-24 -top-24 h-96 w-96 rounded-full bg-brand-light/30 blur-3xl" aria-hidden="true"></div>
        <div class="relative mx-auto flex max-w-7xl flex-col gap-6 px-4 py-10 sm:px-6 lg:flex-row lg:items-end lg:justify-between lg:px-8">
            <div class="max-w-3xl space-y-2">
                <p class="text-sm font-semibold uppercase tracking-wider text-brand">Tài liệu luyện thi IELTS</p>
                <h1 class="font-display text-3xl font-bold tracking-tight text-ink sm:text-4xl">Gói tài liệu IELTS</h1>
                <p class="text-lg leading-relaxed text-slate-600">
                    Tài liệu PDF và audio theo từng level. Mỗi tài liệu cho xem thử miễn phí một số trang đầu hoặc nghe thử vài giây đầu.
                </p>
            </div>
        </div>
    </section>

    <div class="py-6">
        <div x-data="packageList" class="mx-auto max-w-7xl space-y-8 px-4 sm:px-6 lg:px-8">
            {{-- Khung lọc: ô tìm kiếm ở trên, các nút level ở dưới --}}
            <div class="space-y-4 rounded-xl bg-white p-4 shadow-sm">
                <label class="relative block">
                    <span class="sr-only">Tìm gói theo tên</span>
                    <svg class="pointer-events-none absolute left-4 top-1/2 h-[18px] w-[18px] -translate-y-1/2 text-slate-500" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd" />
                    </svg>
                    <input type="search" x-model.debounce.300ms="q" placeholder="Tìm kiếm gói tài liệu theo tên..." maxlength="100"
                        class="w-full rounded-lg border-transparent bg-surface-low py-3 pl-12 text-sm text-ink placeholder:text-slate-500 focus:border-brand focus:bg-white focus:ring-brand">
                </label>

                <div role="group" aria-label="Lọc theo level" class="flex gap-1 overflow-x-auto pb-1">
                    <template x-for="option in levels" :key="option.value">
                        <button type="button" x-on:click="level = option.value" :aria-pressed="level === option.value"
                            :class="level === option.value ? 'bg-ink text-white shadow-sm' : 'bg-surface-low text-slate-600 hover:bg-surface-high'"
                            class="shrink-0 rounded-lg px-4 py-2 text-sm font-medium tracking-wide transition focus:outline-none focus-visible:ring-2 focus-visible:ring-brand"
                            x-text="option.label"></button>
                    </template>
                </div>
            </div>

            {{-- Băng chính sách xem thử --}}
            <div class="flex items-center gap-4 rounded-xl bg-brand-pale p-4 shadow-sm">
                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-brand text-white">
                    <svg class="h-6 w-6" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path d="M10 12a2 2 0 100-4 2 2 0 000 4z" />
                        <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div>
                    <p class="flex flex-wrap items-center gap-2 text-lg font-bold text-ink">
                        Chính sách học thử
                        <span class="rounded-full bg-brand-light px-2 py-0.5 text-[11px] font-bold uppercase text-ink">Miễn phí</span>
                    </p>
                    <p class="text-sm text-slate-600">Mở bất kỳ gói nào để xem thử các trang đầu của tài liệu PDF và nghe thử đoạn đầu của audio, không cần mua.</p>
                </div>
            </div>

            <x-async-state>
                <x-slot name="loading">
                    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                        @for ($i = 0; $i < 3; $i++)
                            <div class="overflow-hidden rounded-xl bg-white shadow-sm">
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
                    <button type="button" x-on:click="clearFilters()" class="mt-3 text-sm font-medium text-brand hover:text-brand-dark">
                        Xoá bộ lọc
                    </button>
                </x-slot>

                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                    <template x-for="pkg in packages" :key="pkg.id">
                        <x-ielts.package-card />
                    </template>
                </div>

                {{-- Phân trang theo Figma: dòng "Hiển thị x - y trong tổng số z" bên trái, nút trang bên phải --}}
                <div x-show="meta" class="mt-8 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <p class="text-sm text-slate-600">
                        Hiển thị <span class="font-semibold text-ink" x-text="(meta?.from ?? 0) + ' - ' + (meta?.to ?? 0)"></span>
                        trong tổng số <span class="font-semibold text-ink" x-text="meta?.total ?? 0"></span> gói tài liệu
                    </p>

                    <nav x-show="meta && meta.last_page > 1" class="flex items-center gap-1 rounded-xl bg-white p-1 shadow-sm" aria-label="Phân trang">
                        <button type="button" x-on:click="goTo(page - 1)" :disabled="page <= 1"
                            class="h-9 rounded-lg px-3 text-sm font-medium text-ink hover:bg-surface-low disabled:opacity-40">
                            Trang trước
                        </button>
                        <span class="flex h-9 min-w-[2.25rem] items-center justify-center rounded-lg bg-ink px-2 text-sm font-semibold text-white" x-text="page + ' / ' + (meta?.last_page ?? 1)"></span>
                        <button type="button" x-on:click="goTo(page + 1)" :disabled="page >= (meta?.last_page ?? 1)"
                            class="h-9 rounded-lg px-3 text-sm font-medium text-ink hover:bg-surface-low disabled:opacity-40">
                            Trang tiếp
                        </button>
                    </nav>
                </div>
            </x-async-state>
        </div>
    </div>
</x-app-layout>
