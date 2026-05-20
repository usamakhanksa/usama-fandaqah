import { defineConfig } from 'vite';
import vue from '@vitejs/plugin-vue';
import laravel from 'laravel-vite-plugin';
import path from 'path';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/main.js'],
            refresh: true,
        }),
        vue(),
    ],
    resolve: {
        alias: [
            { find: '@inertiajs/vue3', replacement: path.resolve(__dirname, './resources/js/shims/inertia.js') },
            { find: 'lodash', replacement: path.resolve(__dirname, './resources/js/shims/lodash.js') },
            { find: 'vue3-draggable', replacement: path.resolve(__dirname, './resources/js/shims/draggable.js') },
            { find: '@mui/icons-material-runtime', replacement: path.resolve(__dirname, './resources/js/shims/mui-icons.js') },
            { find: /^@\/[Cc]omponents\/Pagination\.vue$/, replacement: path.resolve(__dirname, './resources/js/shims/Pagination.vue') },
            { find: /^@\/[Cc]omponents\/Modal\.vue$/, replacement: path.resolve(__dirname, './resources/js/shims/Modal.vue') },
            { find: /^@\/Layouts\/AuthenticatedLayout\.vue$/, replacement: path.resolve(__dirname, './resources/js/shims/AuthenticatedLayout.vue') },
            { find: '@', replacement: path.resolve(__dirname, './resources/js') },
        ]
    },
    build: {
        chunkSizeWarningLimit: 1600,
        sourcemap: false,
        rollupOptions: {
            output: {
                manualChunks(id) {
                    if (id.includes('node_modules')) {
                        return id.toString().split('node_modules/')[1].split('/')[0].toString();
                    }
                }
            }
        }
    },
    server: {
        host: '127.0.0.1',
        port: 5173,
        cors: true,
        hmr: {
            host: '127.0.0.1',
        },
        proxy: {
            '/api': {
                target: 'http://usama-fandaqah.test',
                changeOrigin: true,
                secure: false,
            },
            '/sanctum': {
                target: 'http://usama-fandaqah.test',
                changeOrigin: true,
                secure: false,
            },
        },
    },
});
