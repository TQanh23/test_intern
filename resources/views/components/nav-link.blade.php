@props(['active'])

@php
// Link đang chọn là "viên thuốc" nền navy chữ trắng, các link khác chữ xám (theo header Figma).
$classes = ($active ?? false)
            ? 'inline-flex items-center rounded-lg bg-ink px-4 py-2 text-sm font-medium tracking-wide text-white focus:outline-none focus-visible:ring-2 focus-visible:ring-brand transition duration-150 ease-in-out'
            : 'inline-flex items-center rounded-lg px-4 py-2 text-sm font-medium tracking-wide text-slate-600 hover:bg-surface-low hover:text-ink focus:outline-none focus-visible:ring-2 focus-visible:ring-brand transition duration-150 ease-in-out';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }} @if ($active ?? false) aria-current="page" @endif>
    {{ $slot }}
</a>
