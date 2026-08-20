import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        tailwindcss(),
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
    ],
    optimizeDeps: {
        // TipTap is loaded by dynamic import in resources/js/editor.js so it
        // stays out of the main bundle. That leaves it absent from the dep
        // cache until the first terms page is opened, and the on-demand
        // optimisation that follows is fragile: if the cache in
        // node_modules/.vite has been replaced underneath a running dev server
        // — which `npm run build` does, since dev and build share it — every
        // request for these modules 504s and the editor silently never mounts.
        // Naming them here pre-bundles them at server start instead.
        include: ['@tiptap/core', '@tiptap/starter-kit', '@tiptap/extension-link'],
    },
});
