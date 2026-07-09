<?php

namespace App\Console\Commands;

use App\Models\JakimZone;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Throwable;

/**
 * zones:verify — sahkan setiap kod zon dengan e-Solat JAKIM (§9.3/§16.A).
 * status "OK!" = sah → set verified_at. Exit non-zero jika ada kegagalan.
 * JANGAN dijalankan dalam suite ujian (R12) — ujian guna Http::fake().
 */
class ZonesVerifyCommand extends Command
{
    protected $signature = 'zones:verify';

    protected $description = 'Sahkan 59 kod zon JAKIM dengan e-Solat (§16.A)';

    private const ENDPOINT = 'https://www.e-solat.gov.my/index.php';

    public function handle(): int
    {
        $zones = JakimZone::query()->orderBy('code')->get();
        $rows = [];
        $failures = 0;

        foreach ($zones as $zone) {
            $ok = false;
            $note = '';

            try {
                $response = Http::timeout(20)->get(self::ENDPOINT, [
                    'r' => 'esolatApi/takwimsolat',
                    'period' => 'today',
                    'zone' => $zone->code,
                ]);

                $ok = $response->successful() && $response->json('status') === 'OK!';
                $note = $ok ? 'OK!' : 'status='.($response->json('status') ?? 'HTTP '.$response->status());
            } catch (Throwable $e) {
                $note = 'ralat: '.$e->getMessage();
            }

            if ($ok) {
                $zone->update(['verified_at' => now()]);
            } else {
                $failures++;
            }

            $rows[] = [$zone->code, $zone->state, $ok ? '✓' : '✗', $note];
        }

        $this->table(['Kod', 'Negeri', 'Sah', 'Nota'], $rows);
        $this->line(sprintf('Jumlah: %d zon · %d gagal.', $zones->count(), $failures));

        if ($failures > 0) {
            $this->error('Ada kod gagal — HENTIKAN & semak sebelum prospek pertama (§16.A).');

            return self::FAILURE;
        }

        $this->info('Semua zon disahkan.');

        return self::SUCCESS;
    }
}
