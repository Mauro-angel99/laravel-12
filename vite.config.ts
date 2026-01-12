import { wayfinder } from '@laravel/vite-plugin-wayfinder';
import vue from '@vitejs/plugin-vue';
import laravel from 'laravel-vite-plugin';
import { defineConfig } from 'vite';

export default defineConfig({
    resolve: {
        alias: {
            vue: 'vue/dist/vue.esm-bundler.js',
        },
    },
    plugins: [
        laravel({
            input: ['resources/js/app.ts', 'resources/js/app.js'],
            ssr: 'resources/js/ssr.ts',
            refresh: true,
        }),
        wayfinder({
            formVariants: true,
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
    server: {
        host: 'myproject.local',
        port: 5173,
        strictPort: true,
        cors: true,
        origin: 'http://myproject.local:5173',
        hmr: {
            host: 'myproject.local',
            protocol: 'ws',
        },
    },
    /*server: {
        host: '0.0.0.0', // Espone Vite su tutte le interfacce
        port: 5173,
        strictPort: true,
        cors: true,
        hmr: {
            host: '10.39.218.41', // Il tuo IP locale
            protocol: 'ws',
        },
    },*/
    preview: {
        host: 'myproject.local',
        port: 4173,
        strictPort: true,
    },
});
