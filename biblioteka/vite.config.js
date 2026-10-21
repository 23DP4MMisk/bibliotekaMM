import { defineConfig } from 'vite';
import vue from '@vitejs/plugin-vue';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/js/app.js'],
            refresh: true,
        }),
        vue(),
    ],
     assetsInclude: ['**/*.gif'], 
    server: {
        fs: {
            
            allow: ['..', '../public']
        }
    },
    proxy: {
            '/api': {
                target: 'http://127.0.0.1:8000', 
                changeOrigin: true,
                secure: false,
            }
        }
    
});