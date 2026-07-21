import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    build: {
        outDir: '../../public/build-smartbot',
        emptyOutDir: true,
        manifest: true,
    },
    plugins: [
        laravel({
            publicDirectory: '../../public',
            buildDirectory: 'build-smartbot',
            input: [
                __dirname + '/resources/assets/js/app.js'
            ],
            refresh: true,
        }),
    ],
});
