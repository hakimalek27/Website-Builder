import { execSync } from 'node:child_process';
import { writeFileSync } from 'node:fs';
import { fileURLToPath } from 'node:url';
import { dirname, join } from 'node:path';

const __dirname = dirname(fileURLToPath(import.meta.url));

/** Jalankan reka:demo-token (dengan/ tanpa --ngo) dan huraikan JSON {token, generation}. */
function runDemo(args = '') {
    const raw = execSync(`php artisan reka:demo-token ${args}`.trim(), {
        cwd: join(__dirname, '..'),
        encoding: 'utf-8',
    });
    const clean = raw.replace(/\x1b\[[0-9;]*m/g, '');
    const line = clean
        .split('\n')
        .map((s) => s.trim())
        .filter((l) => l.startsWith('{') && l.endsWith('}'))
        .pop();

    if (!line) {
        throw new Error(`reka:demo-token ${args} tidak mengembalikan JSON. Output:\n${clean}`);
    }

    return JSON.parse(line);
}

/**
 * Jana sesi PIC demo MASJID + NGO (projek + token + draf sebenar), tulis ke
 * .smoke-target.json untuk dibaca oleh smoke.spec.js.
 */
export default async function globalSetup() {
    const mosque = runDemo();
    const ngo = runDemo('--ngo');

    const target = {
        token: mosque.token,
        generation: mosque.generation,
        ngoToken: ngo.token,
        ngoGeneration: ngo.generation,
    };
    writeFileSync(join(__dirname, '.smoke-target.json'), JSON.stringify(target, null, 2));
    console.log(`[smoke] sesi demo sedia — masjid ${mosque.token.slice(0, 8)}… + NGO ${ngo.token.slice(0, 8)}…`);
}
