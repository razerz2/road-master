<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center justify-center px-5 py-2.5 bg-gradient-to-r from-red-600 to-red-700 border border-transparent rounded-lg font-semibold text-sm text-white uppercase tracking-wide shadow-md hover:shadow-lg hover:from-red-700 hover:to-red-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 active:from-red-800 active:to-red-900 transform hover:scale-105 transition-all duration-200 ease-in-out']) }}>
    {{ $slot }}
</button>
