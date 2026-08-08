import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import path from 'node:path';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/js/app.js',
            ],
            refresh: true,
        }),
    ],
    resolve: {
        alias: {
            '@': path.resolve(__dirname, 'resources/js'),
        },
    },
    build: {
        chunkSizeWarningLimit: 2000,
        rollupOptions: {
            output: {
                manualChunks: {
                    'vendor-bootstrap': ['bootstrap'],
                    'vendor-datatable': [
                        'datatables.net-bs5',
                        'datatables.net-buttons-bs5',
                        'datatables.net-buttons',
                        'datatables.net-responsive-bs5',
                        'jszip',
                    ],
                    'vendor-pdf': ['pdfmake'],
                    'vendor-charts': ['chart.js'],
                    'vendor-ui': ['sweetalert2', 'toastr', 'select2', 'flatpickr'],
                },
            },
        },
    },
});
