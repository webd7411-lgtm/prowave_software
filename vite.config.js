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
                manualChunks(id) {
                    if (id.includes('node_modules/jquery')) return 'vendor-ui';
                    if (id.includes('node_modules/toastr')) return 'vendor-ui';
                    if (id.includes('node_modules/sweetalert2')) return 'vendor-ui';
                    if (id.includes('node_modules/select2')) return 'vendor-ui';
                    if (id.includes('node_modules/flatpickr')) return 'vendor-ui';
                    if (id.includes('node_modules/bootstrap')) return 'vendor-bootstrap';
                    if (
                        id.includes('node_modules/datatables.net') ||
                        id.includes('node_modules/jszip')
                    ) return 'vendor-datatable';
                    if (id.includes('node_modules/pdfmake')) return 'vendor-pdf';
                    if (id.includes('node_modules/chart.js')) return 'vendor-charts';
                },
            },
        },
    },
});
