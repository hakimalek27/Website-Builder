import { test, expect } from '@playwright/test';
import { readFileSync } from 'node:fs';
import { fileURLToPath } from 'node:url';
import { dirname, join } from 'node:path';

const __dirname = dirname(fileURLToPath(import.meta.url));
const { token, generation } = JSON.parse(
    readFileSync(join(__dirname, '.smoke-target.json'), 'utf-8'),
);

// Semua halaman custom REKA (awam + PIC + wizard + admin). Draf/tweak/lulus perlu sesi demo.
const PAGES = [
    // Awam
    { name: 'awam-landing', path: '/' },
    { name: 'awam-minat', path: '/minat' },
    { name: 'awam-terima-kasih', path: '/minat/terima-kasih' },
    { name: 'awam-privasi', path: '/privasi' },
    { name: 'awam-terma', path: '/terma' },
    { name: 'admin-login', path: '/admin/login' },
    // PIC
    { name: 'pic-home', path: `/b/${token}` },
    { name: 'pic-semak', path: `/b/${token}/semak` },
    { name: 'pic-jana', path: `/b/${token}/jana` },
    { name: 'pic-status', path: `/b/${token}/status` },
    { name: 'pic-draf', path: `/b/${token}/draf/${generation}` },
    { name: 'pic-tweak-reka', path: `/b/${token}/tweak/reka` },
    { name: 'pic-tweak-kandungan', path: `/b/${token}/tweak/kandungan` },
    { name: 'pic-lulus', path: `/b/${token}/lulus` },
    // Wizard 0–9
    ...Array.from({ length: 10 }, (_, i) => ({ name: `wizard-langkah-${i}`, path: `/b/${token}/langkah/${i}` })),
];

// Bunyi console yang boleh diabaikan (bukan pepijat aplikasi):
// - rangkaian/font luar; ResizeObserver; sandbox iframe draf (§5.2 P6 — sekat skrip draf AI, memang betul).
const BENIGN = /favicon|fonts\.g(static|oogleapis)|net::ERR|Failed to load resource|ResizeObserver|sandbox/i;

for (const p of PAGES) {
    test(`smoke: ${p.name}`, async ({ page }, testInfo) => {
        const errors = [];
        page.on('console', (msg) => {
            if (msg.type() === 'error' && !BENIGN.test(msg.text())) {
                errors.push(`console » ${msg.text()}`);
            }
        });
        page.on('pageerror', (err) => errors.push(`pageerror » ${err.message}`));

        const resp = await page.goto(p.path, { waitUntil: 'load' });

        // 1. Bukan ralat pelayan (4xx/5xx).
        expect(resp, `${p.name}: tiada respons`).toBeTruthy();
        expect(resp.status(), `${p.name}: status HTTP ${resp?.status()}`).toBeLessThan(400);

        // 2. Halaman ada kandungan.
        await expect(page.locator('body')).not.toBeEmpty();

        // 3. Paksa semua elemen reveal jadi kelihatan supaya screenshot penuh bersih.
        await page.evaluate(() =>
            document.querySelectorAll('.reveal').forEach((el) => el.classList.add('is-revealed')),
        );
        await page.waitForTimeout(250);

        // 4. Screenshot penuh untuk semakan visual manual.
        await page.screenshot({
            path: join(__dirname, 'screenshots', `${p.name}__${testInfo.project.name}.png`),
            fullPage: true,
        });

        // 5. Tiada ralat console/JS sebenar (termasuk pelanggaran CSP).
        expect(errors, `${p.name} ada ralat:\n${errors.join('\n')}`).toEqual([]);
    });
}
