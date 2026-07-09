<?php

namespace App\Services;

use App\Exceptions\UploadException;
use App\Models\Asset;
use App\Models\Project;
use finfo;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

/**
 * Muat naik terpusat (§11.4). Semakan keselamatan:
 * - finfo MIME SEBENAR (bukan sambungan).
 * - Imej → re-encode Intervention Image (driver GD DIKUNCI — metadata terbuang
 *   semasa re-encode, termasuk EXIF GPS) + resize sisi panjang.
 * - PDF → sahkan magic bytes %PDF.
 * - SVG (logo) → sanitasi asas (buang <script>/handler).
 * - Nama fail = ULID; disimpan di luar webroot (disk local); dihidang route bertoken.
 */
class UploadService
{
    /** MIME dibenarkan per jenis fail. */
    private const IMAGE_MIMES = ['image/jpeg', 'image/png', 'image/webp'];

    private const EXT_MAP = [
        'image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp',
        'image/svg+xml' => 'svg', 'application/pdf' => 'pdf',
    ];

    /** Had saiz (bait) per kind. */
    private function maxSize(string $kind): int
    {
        return match ($kind) {
            'logo' => 4 * 1024 * 1024,
            'doc' => 10 * 1024 * 1024,
            default => 8 * 1024 * 1024,
        };
    }

    private function longSide(string $kind): int
    {
        return $kind === 'hero' ? 2400 : 1200;
    }

    public function store(UploadedFile $file, string $kind, Project $project): Asset
    {
        if (! $file->isValid()) {
            throw new UploadException('Fail tidak sah.');
        }

        if ($file->getSize() > $this->maxSize($kind)) {
            throw new UploadException('Saiz fail melebihi had dibenarkan.');
        }

        $mime = (new finfo(FILEINFO_MIME_TYPE))->file($file->getRealPath()) ?: 'application/octet-stream';

        $relative = "assets/{$project->id}/".(string) Str::ulid();
        $width = null;
        $height = null;

        if ($kind === 'doc') {
            $this->assertPdf($file, $mime);
            $relative .= '.pdf';
            Storage::disk('local')->putFileAs(dirname($relative), $file, basename($relative));
        } elseif ($mime === 'image/svg+xml' && $kind === 'logo') {
            $relative .= '.svg';
            Storage::disk('local')->put($relative, $this->sanitizeSvg(file_get_contents($file->getRealPath())));
        } elseif (in_array($mime, self::IMAGE_MIMES, true)) {
            $ext = self::EXT_MAP[$mime];
            $relative .= '.'.$ext;
            [$width, $height] = $this->reencodeImage($file, $kind, $relative);
        } else {
            throw new UploadException('Jenis fail tidak dibenarkan.');
        }

        return Asset::create([
            'project_id' => $project->id,
            'kind' => $kind,
            'path' => $relative,
            'original_name' => $file->getClientOriginalName(),
            'mime' => $mime,
            'size' => Storage::disk('local')->size($relative),
            'width' => $width,
            'height' => $height,
        ]);
    }

    /** Re-encode + resize + buang EXIF (GD). Pulangkan [width, height]. */
    private function reencodeImage(UploadedFile $file, string $kind, string $relative): array
    {
        $manager = new ImageManager(new Driver); // GD DIKUNCI (§11.4)
        $image = $manager->decodePath($file->getRealPath());

        $long = $this->longSide($kind);
        $image->scaleDown($long, $long);

        $fullPath = Storage::disk('local')->path($relative);
        @mkdir(dirname($fullPath), 0755, true);
        $image->save($fullPath); // re-encode ikut sambungan → metadata terbuang

        return [$image->width(), $image->height()];
    }

    private function assertPdf(UploadedFile $file, string $mime): void
    {
        $handle = fopen($file->getRealPath(), 'rb');
        $magic = fread($handle, 5);
        fclose($handle);

        if ($mime !== 'application/pdf' || $magic !== '%PDF-') {
            throw new UploadException('Fail PDF tidak sah.');
        }
    }

    /** Sanitasi SVG asas — buang skrip & handler acara. */
    private function sanitizeSvg(string $svg): string
    {
        $svg = preg_replace('/<script\b[^>]*>.*?<\/script>/is', '', $svg) ?? $svg;
        $svg = preg_replace('/\son\w+="[^"]*"/i', '', $svg) ?? $svg;
        $svg = preg_replace('/<foreignObject\b.*?<\/foreignObject>/is', '', $svg) ?? $svg;

        return $svg;
    }
}
