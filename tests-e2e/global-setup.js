import { execSync } from 'node:child_process';
import { writeFileSync } from 'node:fs';
import { fileURLToPath } from 'node:url';
import { dirname, join } from 'node:path';

const __dirname = dirname(fileURLToPath(import.meta.url));

/**
 * Jana sesi PIC demo (projek + token + draf sebenar) melalui `php artisan reka:demo-token`,
 * kemudian tulis {token, generation} ke .smoke-target.json untuk dibaca oleh smoke.spec.js.
 */
export default async function globalSetup() {
    const raw = execSync('php artisan reka:demo-token', {
        cwd: join(__dirname, '..'),
        encoding: 'utf-8',
    });

    // Buang kod ANSI, ambil baris JSON terakhir.
    const clean = raw.replace(/\x1b\[[0-9;]*m/g, '');
    const line = clean
        .split('\n')
        .map((s) => s.trim())
        .filter((l) => l.startsWith('{') && l.endsWith('}'))
        .pop();

    if (!line) {
        throw new Error(`reka:demo-token tidak mengembalikan JSON. Output:\n${clean}`);
    }

    const target = JSON.parse(line);
    writeFileSync(join(__dirname, '.smoke-target.json'), JSON.stringify(target, null, 2));
    console.log(`[smoke] sesi demo sedia — token ${target.token.slice(0, 8)}…`);
}
