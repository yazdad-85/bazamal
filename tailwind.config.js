import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './vendor/livewire/livewire/src/Features/SupportPagination/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './app/Livewire/**/*.php',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Nunito', ...defaultTheme.fontFamily.sans],
                display: ['Nunito', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                brand: {
                    50: '#e8f5e9',
                    100: '#c8e6c9',
                    500: '#2e7d32',
                    600: '#1b5e20',
                    700: '#145214',
                    800: '#0d3d12',
                    900: '#06280a',
                },
                accent: {
                    400: '#ffb74d',
                    500: '#f57c00',
                    600: '#ef6c00',
                    700: '#e65100',
                },
            },
        },
    },

    plugins: [forms],
};
