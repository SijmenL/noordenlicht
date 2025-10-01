import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                // your styles
                'resources/sass/app.scss',        // compiles to app.css
                'resources/css/app.css',          // only if this file actually exists
                'resources/css/texteditor.css',

                // your scripts
                'resources/js/app.js',
                'resources/js/bootstrap.js',
                'resources/js/texteditor.js',
                'resources/js/search-user.js',
                'resources/js/calendar.js',
                'resources/js/user-export.js',
            ],
            refresh: true,
        }),
    ],
});
