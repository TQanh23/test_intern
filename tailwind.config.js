import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        // Class màu theo level nằm trong resources/js/ielts/format.js.
        './resources/js/**/*.js',
    ],

    theme: {
        extend: {
            // Font và màu lấy từ thiết kế Figma (Inter cho chữ thường, Plus Jakarta Sans cho tiêu đề lớn).
            fontFamily: {
                sans: ['Inter', ...defaultTheme.fontFamily.sans],
                display: ['"Plus Jakarta Sans"', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                // Xanh navy: nút chính, link đang chọn, tiêu đề.
                ink: '#091426',
                // Xanh ngọc: màu nhấn (link, badge, nút phụ trội).
                brand: {
                    DEFAULT: '#006a63',
                    dark: '#00504a',
                    light: '#9cf2e8',
                    pale: '#d6f9f5',
                },
                // Ba tông nền xanh nhạt: nền trang, nền ô nhập/nút phụ, nền chip.
                surface: {
                    DEFAULT: '#f8f9ff',
                    low: '#eff4ff',
                    high: '#e5eeff',
                },
            },
        },
    },

    plugins: [forms],
};
