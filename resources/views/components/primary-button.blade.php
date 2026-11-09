<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center rounded-full border border-transparent bg-slate-900 px-5 py-2 text-sm font-semibold text-white hover:bg-slate-700 focus:bg-slate-700 active:bg-slate-950 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150']) }}>
    {{ $slot }}
</button>
