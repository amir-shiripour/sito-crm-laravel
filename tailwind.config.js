import defaultTheme from 'tailwindcss/defaultTheme'
import forms from '@tailwindcss/forms'
import typography from '@tailwindcss/typography'
import tailwindcssRtl from 'tailwindcss-rtl'

/** @type {import('tailwindcss').Config} */
export default {
    darkMode: 'class',
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './vendor/laravel/jetstream/**/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.js',

        // ⬇️ اضافه کن (ساختار nwidart modules)
        './Modules/**/resources/views/**/*.blade.php',
        './Modules/**/resources/js/**/*.js',
        './Modules/**/resources/**/*.vue', // اگر Vue داری
        './Modules/**/resources/**/*.tsx', // اگر React/TSX داری
    ],
    theme: {
        extend: {
            fontFamily: { sans: ['IRANYekanX', ...defaultTheme.fontFamily.sans] },
        },
    },
    plugins: [forms, typography, tailwindcssRtl],
}
