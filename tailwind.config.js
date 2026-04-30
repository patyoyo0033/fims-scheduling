import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.vue',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Inter', 'Noto Sans Thai', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                'nursing': {
                    50:  '#F0F8FF',   // Alice Blue (Background Base)
                    100: '#DBEAFE',
                    200: '#BFDBFE',
                    300: '#89CFF0',   // Soft Healing Blue
                    400: '#60A5FA',
                    500: '#3B82F6',
                    600: '#0A66C2',   // Primary Blue
                    700: '#0854A0',
                    800: '#06417D',
                    900: '#1E293B',   // Text Dark (Slate)
                },
            },
            boxShadow: {
                'glass': '0 8px 32px rgba(10, 102, 194, 0.12)',
                'glass-hover': '0 16px 48px rgba(10, 102, 194, 0.18)',
                'card-float': '0 4px 20px rgba(10, 102, 194, 0.08)',
                'card-float-hover': '0 12px 36px rgba(10, 102, 194, 0.15)',
            },
            animation: {
                'float-slow': 'float 20s ease-in-out infinite',
                'float-medium': 'float 15s ease-in-out infinite reverse',
                'float-fast': 'float 10s ease-in-out infinite',
                'pulse-soft': 'pulse-soft 3s ease-in-out infinite',
            },
            keyframes: {
                'float': {
                    '0%, 100%': { transform: 'translate(0, 0) scale(1)' },
                    '25%': { transform: 'translate(30px, -20px) scale(1.05)' },
                    '50%': { transform: 'translate(-20px, 20px) scale(0.95)' },
                    '75%': { transform: 'translate(15px, 10px) scale(1.02)' },
                },
                'pulse-soft': {
                    '0%, 100%': { opacity: '0.6' },
                    '50%': { opacity: '0.9' },
                },
            },
        },
    },

    plugins: [forms],
};
