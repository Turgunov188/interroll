import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/site.css', 'resources/js/site.js'],
            refresh: ['resources/views/**', 'lang/**'],
        }),
    ],
    build: {
        // model-viewer (three.js) is loaded on demand, only on products with a 3D file
        chunkSizeWarningLimit: 1200,
    },
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
