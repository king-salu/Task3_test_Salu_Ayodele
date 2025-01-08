import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import react from '@vitejs/plugin-react';
import { resolve } from 'path';

export default defineConfig({
    plugins: [laravel(['resources/js/app.jsx']), react()],
    server: {
        host: 'localhost',
        port: 3000,
    },
    build: {
        outDir: 'public/build',
        manifest: true,
        rollupOptions: {
            input: resolve(__dirname, 'resources/js/app.jsx'),
        },
    },
    resolve: {
        alias: {
            '@': resolve(__dirname, 'resources/js'),
        },
    },
    publicDir: 'public/build', // Ensure publicDir is set to the correct directory
});
