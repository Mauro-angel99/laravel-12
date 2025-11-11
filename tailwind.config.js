import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.vue',
        './resources/js/**/*.ts',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                'copam-blue': {
                    DEFAULT: 'rgba(4, 69, 133, 1)',
                    50: 'rgba(4, 69, 133, 0.1)',
                    100: 'rgba(4, 69, 133, 0.2)',
                    200: 'rgba(4, 69, 133, 0.3)',
                    300: 'rgba(4, 69, 133, 0.4)',
                    400: 'rgba(4, 69, 133, 0.5)',
                    500: 'rgba(4, 69, 133, 0.6)',
                    600: 'rgba(4, 69, 133, 0.7)',
                    700: 'rgba(4, 69, 133, 0.8)',
                    800: 'rgba(4, 69, 133, 0.9)',
                    900: 'rgba(4, 69, 133, 1)',
                    hover: 'rgba(3, 55, 106, 1)',
                    active: 'rgba(2, 41, 80, 1)',
                },
            },
        },
    },

    plugins: [forms],
};
