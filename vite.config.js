import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import react from '@vitejs/plugin-react';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig(({ command }) => {
    if (command === 'build') {
        process.env.NODE_ENV = 'production';
    }

    return {
        plugins: [
            laravel({ input: ['resources/css/app.css', 'resources/js/app.jsx'], refresh: true }),
            react(),
            tailwindcss(),
        ],
        build: {
            chunkSizeWarningLimit: 1200,
        },
    };
});