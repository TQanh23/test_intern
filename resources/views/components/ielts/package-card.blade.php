{{--
    Card một gói theo Figma: ảnh bìa có chip level đè lên, thân card có tên, mô tả, số tài liệu, giá và nút.
    Dùng trong <template x-for="pkg in ..."> của component có `formatPrice` và `levelClass`.
    Chỉ có một phần tử gốc vì x-for yêu cầu vậy.
--}}
<a :href="'{{ url('/packages') }}/' + pkg.slug"
    class="group flex flex-col overflow-hidden rounded-xl bg-white shadow-sm transition hover:-translate-y-0.5 hover:shadow-md focus:outline-none focus-visible:ring-2 focus-visible:ring-brand">
    <div class="relative aspect-[4/3] bg-surface-high">
        <img x-show="pkg.cover_url" :src="pkg.cover_url" :alt="'Ảnh bìa ' + pkg.name" loading="lazy" class="h-full w-full object-cover">
        {{-- Lớp tối dần xuống dưới để chữ trắng ở đáy ảnh đọc được --}}
        <div class="absolute inset-0 bg-gradient-to-t from-ink/80 via-ink/0 to-ink/0" aria-hidden="true"></div>
        <span class="absolute left-3 top-3 rounded-md px-2.5 py-1 text-xs font-bold tracking-wide shadow-sm" :class="levelClass(pkg.level)" x-text="'Level ' + pkg.level"></span>
        <span class="absolute bottom-3 left-3 flex items-center gap-1 text-xs font-medium tracking-wide text-white">
            <svg class="h-3 w-3" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                <path d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V7a2 2 0 00-2-2h-5.586l-1.707-1.707A1 1 0 008 3H4z" />
            </svg>
            <span x-text="pkg.documents_count + ' tài liệu'"></span>
        </span>
    </div>

    <div class="flex flex-1 flex-col gap-1 p-4">
        <h3 class="text-lg font-bold leading-6 text-ink group-hover:text-brand" x-text="pkg.name"></h3>
        <p x-show="pkg.description" class="line-clamp-2 text-sm text-slate-600" x-text="pkg.description"></p>
        <p class="flex items-center gap-1 pt-1 text-[11px] font-medium text-brand">
            <svg class="h-2.5 w-2.5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
            </svg>
            Có tài liệu xem thử miễn phí
        </p>
    </div>

    {{-- Chân card nền xanh nhạt: giá và nút như Figma --}}
    <div class="space-y-2 bg-surface-low/50 px-4 pb-4 pt-2">
        <p class="text-lg font-bold text-ink" x-text="formatPrice(pkg.price)"></p>
        <span class="flex w-full items-center justify-center gap-1 rounded-lg bg-ink px-4 py-2.5 text-sm font-semibold tracking-wide text-white shadow-sm group-hover:bg-brand">
            Xem chi tiết &amp; Học thử
            <svg class="h-3 w-3" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
            </svg>
        </span>
    </div>
</a>
