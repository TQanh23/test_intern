@props(['active'])

@php
$classes = ($active ?? false)
            ? 'block w-full rounded-lg px-4 py-2 text-start text-base font-medium text-white bg-ink focus:outline-none transition duration-150 ease-in-out'
            : 'block w-full rounded-lg px-4 py-2 text-start text-base font-medium text-slate-600 hover:text-ink hover:bg-surface-low focus:outline-none focus:text-ink focus:bg-surface-low transition duration-150 ease-in-out';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
