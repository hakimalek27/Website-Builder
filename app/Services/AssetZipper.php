<?php

namespace App\Services;

use App\Models\Project;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use ZipArchive;

/**
 * §Fasa 16 — pakej ZIP semua aset PIC (logo/hero/gambar AJK/QR/PDF) untuk admin.
 * Tersedia submitted+ (TIDAK memerlukan approval — beza HandoverExporter). Nama entri disanitasi
 * (original_name = input pengguna → slug; ext diambil dari path DB).
 */
class AssetZipper
{
    /** @return array{path: string, name: string} */
    public function zipFor(Project $project): array
    {
        $assets = $project->assets()->orderBy('kind')->orderBy('sort')->get();
        if ($assets->isEmpty()) {
            throw new RuntimeException('Projek ini tiada aset dimuat naik.');
        }

        $slug = Str::slug($project->short_name ?: $project->mosque_name) ?: 'projek';
        $stamp = now()->format('Ymd-His');
        $zipRelative = "tmp/aset-{$slug}-{$stamp}.zip";
        $zipFull = Storage::disk('local')->path($zipRelative);
        @mkdir(dirname($zipFull), 0755, true);

        $zip = new ZipArchive;
        if ($zip->open($zipFull, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new RuntimeException('Gagal mencipta fail ZIP.');
        }

        $seq = [];
        $added = 0;
        foreach ($assets as $asset) {
            if (! Storage::disk('local')->exists($asset->path)) {
                continue;
            }
            $kind = $asset->kind ?: 'lain';
            $n = $seq[$kind] = ($seq[$kind] ?? 0) + 1;
            $ext = pathinfo($asset->path, PATHINFO_EXTENSION) ?: 'bin';
            $base = Str::slug(pathinfo($asset->original_name ?? 'aset', PATHINFO_FILENAME)) ?: 'aset';
            $zip->addFromString(sprintf('%s/%02d-%s.%s', $kind, $n, $base, $ext), Storage::disk('local')->get($asset->path));
            $added++;
        }

        $zip->close();

        if ($added === 0) {
            Storage::disk('local')->delete($zipRelative);
            throw new RuntimeException('Fail aset tidak dijumpai pada storan.');
        }

        return ['path' => $zipRelative, 'name' => "aset-{$slug}-{$stamp}.zip"];
    }
}
