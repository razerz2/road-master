@props(['active' => false])

@php
$triggerClasses = ($active ?? false)
    ? 'inline-flex items-center px-3 py-2 rounded-lg text-sm font-medium leading-5 text-white bg-gradient-to-r from-indigo-600 to-purple-600 shadow-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition duration-150 ease-in-out'
    : 'inline-flex items-center px-3 py-2 rounded-lg text-sm font-medium leading-5 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 hover:text-gray-900 dark:hover:text-white focus:outline-none focus:bg-gray-100 dark:focus:bg-gray-700 focus:text-gray-900 dark:focus:text-white transition duration-150 ease-in-out';
@endphp

<div class="relative" x-data="{ open: false }" @click.outside="open = false">
    <button @click="open = ! open" {{ $attributes->merge(['class' => $triggerClasses]) }}>
        {{ $trigger }}
        <svg class="ml-1.5 h-4 w-4 transition-transform duration-200" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
        </svg>
    </button>

    <div x-show="open"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-75"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95"
         class="absolute z-50 mt-2 w-56 rounded-md shadow-lg bg-white dark:bg-gray-800 ring-1 ring-black ring-opacity-5"
         style="display: none;">
        <div class="py-1">
            {{ $content }}
        </div>
    </div>
</div>

