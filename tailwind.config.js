import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                'compuplay-blue': '#2454FF',
                'compuplay-green': '#5FAD56',
                'compuplay-orange': '#F26430',
                'compuplay-yellow': '#FFBA08',
                'compuplay-red': '#E92222',
                'compuplay-black': '#000000',
                'compuplay-dark-gray': '#3E3E3E',
                'compuplay-gray': '#969696',
                'compuplay-white': '#FFFFFF',
            },
        },
    },

    plugins: [forms],
};
