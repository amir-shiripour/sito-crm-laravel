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
        './Modules/**/Resources/views/**/*.blade.php',
        './Modules/**/Resources/js/**/*.js',
        './Modules/**/Resources/**/*.vue', // اگر Vue داری
        './Modules/**/Resources/**/*.tsx', // اگر React/TSX داری
    ],
    theme: {
        extend: {
            fontFamily: { sans: ['Vazirmatn', ...defaultTheme.fontFamily.sans] },
        },
    },
    plugins: [forms, typography, tailwindcssRtl],
}
