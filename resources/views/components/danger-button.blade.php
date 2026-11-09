<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center rounded-full border border-transparent bg-red-600 px-5 py-2 text-sm font-semibold text-white hover:bg-red-500 active:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150']) }}>
    {{ $slot }}
</button>
