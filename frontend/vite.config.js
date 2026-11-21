import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'src/css/app.css',
                'src/js/app.js',
            ],
            publicDirectory: '../backend/public',
            buildDirectory: 'build',
            hotFile: '../backend/public/hot',
            refresh: [
                '../backend/resources/views/**',
                '../backend/routes/**',
                'src/**',
            ],
        }),
        tailwindcss(),
    ],
    server: {
        host: 'localhost',
        port: 5173,
        strictPort: true,
        cors: true,
        proxy: {
            // Ketika membuka http://localhost:5173, otomatis teruskan request ke Laravel backend (port 8000)
            '^/(?!@|src|node_modules|@vite|@fs|resources|assets).*': {
                target: 'http://127.0.0.1:8000',
                changeOrigin: true,
                secure: false,
            },
        },
    },
});
