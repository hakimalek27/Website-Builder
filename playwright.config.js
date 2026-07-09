import { defineConfig, devices } from '@playwright/test';

/**
 * Ujian smoke REKA — semak semua halaman render (awam + PIC + wizard + admin)
 * merentas 3 saiz skrin, tangkap screenshot, dan gagal jika ada ralat console/CSP.
 *
 * Prasyarat: DB dev telah di-seed (`php artisan migrate:fresh --seed`).
 * Jalankan: `npm run test:e2e` (auto `vite build` + guna/mulakan pelayan :8000).
 */
export default defineConfig({
    testDir: './tests-e2e',
    globalSetup: './tests-e2e/global-setup.js',
    fullyParallel: true,
    forbidOnly: !!process.env.CI,
    retries: 0,
    workers: process.env.CI ? 2 : undefined,
    reporter: [['list'], ['html', { open: 'never' }]],
    outputDir: './test-results',

    use: {
        baseURL: 'http://127.0.0.1:8000',
        screenshot: 'only-on-failure',
        trace: 'retain-on-failure',
    },

    projects: [
        { name: 'mobile', use: { ...devices['Pixel 5'] } },
        { name: 'tablet', use: { ...devices['Desktop Chrome'], viewport: { width: 768, height: 1024 } } },
        { name: 'desktop', use: { ...devices['Desktop Chrome'], viewport: { width: 1440, height: 900 } } },
    ],

    // Guna semula pelayan dev sedia ada di :8000; jika tiada, mulakan satu.
    webServer: {
        command: 'php artisan serve --host=127.0.0.1 --port=8000',
        url: 'http://127.0.0.1:8000/up',
        reuseExistingServer: true,
        timeout: 60_000,
    },
});
