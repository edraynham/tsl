import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/js/app.js',
                'resources/js/owner-competition-squads-entry.js',
                'resources/js/owner-competition-form-entry.js',
            ],
            refresh: true,
        }),
        tailwindcss(),
    ],
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
        // Allow the Laravel app on a custom host (e.g. https://tsl.local) to load the dev server
        // from [::1]:5173 without CORS blocking @vite/client.
        cors: true,
    },
});
