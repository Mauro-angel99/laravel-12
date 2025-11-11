<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center px-4 py-2 bg-copam-blue border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-copam-blue-hover focus:bg-copam-blue-hover active:bg-copam-blue-active focus:outline-none focus:ring-2 focus:ring-copam-blue focus:ring-offset-2 transition ease-in-out duration-150']) }}>
    {{ $slot }}
</button>
