<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center justify-center gap-1 rounded-lg border border-transparent bg-ink px-4 py-2.5 text-sm font-semibold tracking-wide text-white shadow-sm hover:bg-ink/90 active:bg-ink focus:outline-none focus:ring-2 focus:ring-brand focus:ring-offset-2 disabled:opacity-40 transition ease-in-out duration-150']) }}>
    {{ $slot }}
</button>
