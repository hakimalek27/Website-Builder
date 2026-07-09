<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\HandoverExport;
use App\Models\Project;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Str;
use RuntimeException;
use ZipArchive;

/**
 * §14.1 — pakej serahan ZIP: spec.json, build-brief.md, content/sanity-seed.ndjson,
 * assets/, draft/approved-draft.html, README-HANDOVER.md.
 */
class HandoverExporter
{
    public function __construct(
        private SpecBuilder $specBuilder,
        private SanitySeedBuilder $sanitySeedBuilder,
    ) {}

    public function export(Project $project): HandoverExport
    {
        $approval = $project->approval;
        if ($approval === null) {
            throw new RuntimeException('Projek belum diluluskan.');
        }

        $spec = $approval->snapshot['spec'] ?? $this->specBuilder->build($project, $approval);
        $buildBrief = View::make('handover::build-brief', ['spec' => $spec])->render();
        $ndjson = $this->sanitySeedBuilder->build($spec);
        $readme = $this->readme($spec);

        $draftPath = $approval->snapshot['draft_path'] ?? null;
        $draftHtml = ($draftPath && Storage::disk('local')->exists($draftPath))
            ? Storage::disk('local')->get($draftPath)
            : '<!doctype html><title>Draf tidak tersedia</title>';

        $slug = Str::slug($spec['mosque']['short_name'] ?? $spec['mosque']['official_name'] ?? 'masjid') ?: 'masjid';
        $date = now()->format('Ymd');
        $zipName = "handover-{$slug}-{$date}.zip";
        $zipRelative = "handovers/{$project->id}/{$zipName}";
        $zipFull = Storage::disk('local')->path($zipRelative);
        @mkdir(dirname($zipFull), 0755, true);

        $zip = new ZipArchive;
        if ($zip->open($zipFull, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new RuntimeException('Gagal mencipta fail ZIP.');
        }

        $zip->addFromString('spec.json', json_encode($spec, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        $zip->addFromString('build-brief.md', $buildBrief);
        $zip->addFromString('content/sanity-seed.ndjson', $ndjson);
        $zip->addFromString('draft/approved-draft.html', $draftHtml);
        $zip->addFromString('README-HANDOVER.md', $readme);

        // assets/ — fail sebenar (dinamakan {kind}-{nn}-{slug}.{ext}) + nota.
        $zip->addFromString('assets/README.txt', "Aset laman. Ganti placeholder dengan fail sebenar.\n");
        foreach (($spec['assets'] ?? []) as $asset) {
            $src = $asset['source_path'] ?? null;
            if ($src && Storage::disk('local')->exists($src)) {
                $zip->addFromString($asset['file'], Storage::disk('local')->get($src));
            }
        }

        $manifest = [
            'files' => ['spec.json', 'build-brief.md', 'content/sanity-seed.ndjson', 'draft/approved-draft.html', 'README-HANDOVER.md', 'assets/'],
            'asset_count' => count($spec['assets'] ?? []),
            'spec_version' => $spec['reka_spec_version'],
        ];

        $zip->close();

        $export = $project->handoverExports()->create([
            'approval_id' => $approval->id,
            'zip_path' => $zipRelative,
            'manifest' => $manifest,
            'exported_at' => now(),
        ]);

        AuditLog::record('admin', null, 'handover.exported', $export);

        return $export;
    }

    private function readme(array $spec): string
    {
        $name = $spec['mosque']['official_name'] ?? 'Masjid';

        return <<<MD
        # README — Pakej Serahan: {$name}

        Pakej ini mengandungi segala yang diperlukan untuk membina laman web sebenar.

        ## Kandungan
        - `spec.json` — spesifikasi kanonik penuh (sumber kebenaran).
        - `build-brief.md` — arahan pembinaan (MOD A template / MOD B dari kosong).
        - `content/sanity-seed.ndjson` — seed CMS (import ke Sanity).
        - `assets/` — logo, hero, galeri (ganti placeholder).
        - `draft/approved-draft.html` — draf yang diluluskan PIC.

        ## Checklist Azan (SEBELUM live)
        1. Sediakan domain & hosting.
        2. Jalankan Claude Code dengan `build-brief.md`.
        3. `php artisan zones:verify` (di sistem REKA) — pastikan zon disahkan.
        4. Ganti semua aset placeholder dengan fail sebenar.
        5. Semak kandungan AI-flagged (lihat build-brief).
        6. Sahkan nombor akaun infaq betul.
        7. Deploy + HTTPS + HSTS.

        > Dijana oleh REKA · {$spec['generated_at']}
        MD;
    }
}
