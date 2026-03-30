import {
    defineConfig
} from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from "@tailwindcss/vite";

export default defineConfig({
    plugins: [
        laravel({
            input: [
                // Base Tailwind + Flux (cargado en partials/head.blade.php — ambas áreas)
                'resources/css/app.css',

                // CSS específico de /app (cargado en layouts/app/sidebar.blade.php)
                'resources/css/starcho-app.css',

                // CSS específico de /admin (cargado en layouts/admin/sidebar.blade.php)
                'resources/css/starcho-admin.css',

                // JS de /app: starcho.js + PowerGrid
                'resources/js/app.js',

                // JS de /admin: starcho.js + PowerGrid + adminLayout()
                'resources/js/admin.js',
            ],
            refresh: true,
        }),
        tailwindcss(),
    ],
    server: {
        cors: true,
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
