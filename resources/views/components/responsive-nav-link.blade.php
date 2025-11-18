@props(['active'])

@php
$classes = ($active ?? false)
            ? 'block w-full ps-3 pe-4 py-2 border-l-4 border-compuplay-blue text-start text-base font-medium text-compuplay-blue bg-compuplay-blue/10 focus:outline-none focus:text-compuplay-blue focus:bg-compuplay-blue/20 focus:border-compuplay-blue transition duration-150 ease-in-out'
            : 'block w-full ps-3 pe-4 py-2 border-l-4 border-transparent text-start text-base font-medium text-compuplay-gray hover:text-compuplay-dark-gray hover:bg-gray-50 hover:border-compuplay-blue/30 focus:outline-none focus:text-compuplay-dark-gray focus:bg-gray-50 focus:border-compuplay-blue/30 transition duration-150 ease-in-out';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
