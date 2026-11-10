@props(['disabled' => false])

{{-- Ô nhập nền xanh nhạt, không viền, như ô tìm kiếm trong Figma. --}}
<input @disabled($disabled) {{ $attributes->merge(['class' => 'rounded-lg border-transparent bg-surface-low text-ink placeholder:text-slate-500 focus:border-brand focus:bg-white focus:ring-brand']) }}>
