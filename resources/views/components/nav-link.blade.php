@props(['active'])

@php
$classes = ($active ?? false)
            ? 'inline-flex items-center px-1 pt-1 border-b-2 border-compuplay-blue text-sm font-medium leading-5 text-compuplay-dark-gray focus:outline-none focus:border-compuplay-blue transition duration-150 ease-in-out'
            : 'inline-flex items-center px-1 pt-1 border-b-2 border-transparent text-sm font-medium leading-5 text-compuplay-gray hover:text-compuplay-dark-gray hover:border-compuplay-blue/30 focus:outline-none focus:text-compuplay-dark-gray focus:border-compuplay-blue/30 transition duration-150 ease-in-out';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
