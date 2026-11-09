{{-- Icon theo loại tài liệu. Đọc biến `doc` (có `type`) của scope Alpine bao quanh. --}}
<span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl"
    :class="doc?.type === 'pdf' ? 'bg-rose-100 text-rose-600' : 'bg-sky-100 text-sky-600'">
    <svg x-show="doc?.type === 'pdf'" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round" d="M14 3H7a2 2 0 00-2 2v14a2 2 0 002 2h10a2 2 0 002-2V8l-5-5z" />
        <path stroke-linecap="round" stroke-linejoin="round" d="M14 3v5h5M9 13h6M9 17h4" />
    </svg>
    <svg x-show="doc?.type === 'audio'" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round" d="M9 18V5l12-2v13" />
        <circle cx="6" cy="18" r="3" />
        <circle cx="18" cy="16" r="3" />
    </svg>
    <span class="sr-only" x-text="doc?.type === 'pdf' ? 'PDF' : 'Audio'"></span>
</span>
