<button {{ $attributes->merge(['type' => 'button', 'class' => 'inline-flex items-center justify-center gap-1 rounded-lg bg-surface-low px-4 py-2.5 text-sm font-medium tracking-wide text-ink hover:bg-surface-high focus:outline-none focus:ring-2 focus:ring-brand focus:ring-offset-2 disabled:opacity-40 transition ease-in-out duration-150']) }}>
    {{ $slot }}
</button>
