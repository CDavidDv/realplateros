import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';

export default defineConfig({
    server: {
        host: '0.0.0.0', // Escucha en todas las interfaces
        port: 5173, // Puerto que quieres usar, 5173 es el predeterminado
        hmr: {
          host: '192.168.1.73', // Reemplaza con la IP local de tu laptop
        },
      },
    plugins: [
        laravel({
            input: 'resources/js/app.js',
            refresh: true,
        }),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
    ],
});
