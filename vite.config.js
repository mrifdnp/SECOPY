import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
            ],
            refresh: true,
            buildDirectory: 'build-vite', // Laravel akan otomatis cari di public/build-vite/manifest.json
        }),
    ],
    build: {
        outDir: 'public/build-vite',
    },
});