import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
        tailwindcss(),
    ],
    server: {
        // Bind IPv4 eksplisit: tanpa ini Vite bind IPv6 loopback dan menulis
        // `http://[::1]:5173` ke public/hot. `[::1]` BUKAN host-source CSP yang sah
        // (browser tolak) → aset dev disekat & CSS/JS pecah. `127.0.0.1` dibenarkan
        // oleh gate CSP withViteDevHosts (localhost:* / 127.0.0.1:*).
        host: '127.0.0.1',
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
