{{--
    Card một gói. Dùng trong <template x-for="pkg in ..."> của component có `formatPrice` và `levelClass`.
    Chỉ có một phần tử gốc vì x-for yêu cầu vậy.
--}}
<a :href="'{{ url('/packages') }}/' + pkg.slug"
    class="group flex flex-col overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-200 transition hover:-translate-y-0.5 hover:shadow-md focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500">
    <div class="aspect-[4/3] bg-gradient-to-br from-gray-200 to-gray-300">
        <img x-show="pkg.cover_url" :src="pkg.cover_url" :alt="'Ảnh bìa ' + pkg.name" loading="lazy" class="h-full w-full object-cover">
    </div>

    <div class="flex flex-1 flex-col gap-2 p-5">
        <span class="w-fit rounded-full px-2.5 py-0.5 text-xs font-semibold" :class="levelClass(pkg.level)" x-text="'Level ' + pkg.level"></span>
        <h3 class="text-lg font-semibold text-gray-900 group-hover:text-indigo-700" x-text="pkg.name"></h3>
        <p class="flex items-center gap-1.5 text-sm text-gray-500">
            <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                <path d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V7a2 2 0 00-2-2h-5.586l-1.707-1.707A1 1 0 008 3H4z" />
            </svg>
            <span x-text="pkg.documents_count + ' tài liệu'"></span>
        </p>
        <p class="mt-auto pt-3 text-xl font-bold text-gray-900" x-text="formatPrice(pkg.price)"></p>
    </div>
</a>
