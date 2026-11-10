{{--
    Bốn trạng thái của một khối dữ liệu tải bằng API. Đặt bên trong component Alpine có:
    `status` ('loading' | 'error' | 'empty' | 'ready'), `error` (câu báo lỗi) và hàm thử lại (mặc định `load()`).
    Slot tùy chọn: `loading` (khung chờ), `empty` (khi không có dữ liệu). Slot chính hiện khi `ready`.
--}}
@props(['retry' => 'load()'])

<div {{ $attributes }}>
    <div x-show="status === 'loading'" role="status" aria-live="polite">
        @isset($loading)
            {{ $loading }}
        @else
            <div class="flex items-center justify-center gap-3 py-12 text-slate-500">
                <svg class="h-5 w-5 animate-spin" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                    <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" class="opacity-25" />
                    <path d="M4 12a8 8 0 018-8" stroke="currentColor" stroke-width="4" stroke-linecap="round" />
                </svg>
                <span>Đang tải...</span>
            </div>
        @endisset
    </div>

    <div x-show="status === 'error'" x-cloak role="alert" class="rounded-xl border border-red-200 bg-red-50 px-6 py-8 text-center">
        <p class="font-medium text-red-800" x-text="error"></p>
        <button type="button" x-on:click="{{ $retry }}"
            class="mt-4 inline-flex items-center rounded-lg bg-white px-4 py-2 text-sm font-semibold text-red-700 shadow-sm ring-1 ring-red-200 hover:bg-red-100 focus:outline-none focus-visible:ring-2 focus-visible:ring-red-500">
            Thử lại
        </button>
    </div>

    <div x-show="status === 'empty'" x-cloak class="rounded-xl border border-dashed border-slate-300 bg-white px-6 py-12 text-center text-slate-600">
        @isset($empty)
            {{ $empty }}
        @else
            Không có dữ liệu.
        @endisset
    </div>

    <div x-show="status === 'ready'" x-cloak>
        {{ $slot }}
    </div>
</div>
