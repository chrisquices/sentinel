import { defineConfig } from 'vite';
import { svelte } from '@sveltejs/vite-plugin-svelte';

export default defineConfig({
    plugins: [svelte()],
    build: {
        outDir: 'public/vendor/vulcan-sentinel',
        emptyOutDir: true,
        rollupOptions: {
            input: {
                app: 'ui/src/main.js',
            },
        },
    },
});
