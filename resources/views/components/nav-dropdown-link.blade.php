@props(['active' => false])

@php
$classes = ($active ?? false)
    ? 'block px-4 py-2.5 text-sm leading-5 text-white bg-indigo-600 dark:bg-indigo-500 focus:outline-none focus:bg-indigo-700 dark:focus:bg-indigo-600 transition duration-150 ease-in-out'
    : 'block px-4 py-2.5 text-sm leading-5 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 focus:outline-none focus:bg-gray-100 dark:focus:bg-gray-700 transition duration-150 ease-in-out';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>

