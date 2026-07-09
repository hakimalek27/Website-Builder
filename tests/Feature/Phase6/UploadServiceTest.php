<?php

use App\Exceptions\UploadException;
use App\Models\Project;
use App\Services\UploadService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

// Fasa 6 — UploadService (§11.4): MIME sebenar, re-encode, buang EXIF GPS.

/**
 * Bina JPEG kecil dengan EXIF GPS (dibuat manual) untuk menguji penanggalan.
 */
function makeJpegWithGps(): string
{
    // TIFF little-endian: IFD0 di offset 8 → GPSInfo → GPS IFD → rationals.
    $tiff = 'II'.pack('v', 42).pack('V', 8);
    // IFD0: 1 entri (GPSInfo tag 0x8825 → offset 26).
    $tiff .= pack('v', 1).pack('v', 0x8825).pack('v', 4).pack('V', 1).pack('V', 26).pack('V', 0);
    // GPS IFD di offset 26: 2 entri.
    $tiff .= pack('v', 2);
    $tiff .= pack('v', 1).pack('v', 2).pack('V', 2)."N\0\0\0";            // GPSLatitudeRef = "N"
    $tiff .= pack('v', 2).pack('v', 5).pack('V', 3).pack('V', 56);         // GPSLatitude → rationals di 56
    $tiff .= pack('V', 0);                                                  // next IFD = 0
    // Rationals di offset 56: 3/1, 0/1, 0/1.
    $tiff .= pack('V', 3).pack('V', 1).pack('V', 0).pack('V', 1).pack('V', 0).pack('V', 1);

    $exif = "Exif\0\0".$tiff;
    $app1 = "\xFF\xE1".pack('n', strlen($exif) + 2).$exif;

    // Imej GD sebenar.
    $img = imagecreatetruecolor(120, 90);
    imagefill($img, 0, 0, imagecolorallocate($img, 40, 120, 80));
    ob_start();
    imagejpeg($img, null, 90);
    $gd = ob_get_clean();
    imagedestroy($img);

    // FFD8 (SOI) + APP1(EXIF GPS) + badan JPEG GD (tanpa SOI-nya).
    return "\xFF\xD8".$app1.substr($gd, 2);
}

function fakeUpload(string $bytes, string $name, string $mime): UploadedFile
{
    $tmp = tempnam(sys_get_temp_dir(), 'upl');
    file_put_contents($tmp, $bytes);

    return new UploadedFile($tmp, $name, $mime, null, true);
}

beforeEach(function () {
    Storage::fake('local');
});

it('rejects a PHP payload disguised as .jpg (real MIME check) (§11.4)', function () {
    $project = Project::factory()->create();
    $file = fakeUpload('<?php echo "evil"; ?>', 'photo.jpg', 'image/jpeg');

    expect(fn () => app(UploadService::class)->store($file, 'gallery', $project))
        ->toThrow(UploadException::class);
});

it('rejects oversized files (§11.4)', function () {
    $project = Project::factory()->create();
    // 9 MB > had 8 MB untuk imej.
    $file = UploadedFile::fake()->create('big.jpg', 9000, 'image/jpeg');

    expect(fn () => app(UploadService::class)->store($file, 'gallery', $project))
        ->toThrow(UploadException::class);
});

it('strips EXIF GPS from uploaded images (§11.4)', function () {
    $project = Project::factory()->create();
    $bytes = makeJpegWithGps();
    $file = fakeUpload($bytes, 'geo.jpg', 'image/jpeg');

    // Sanity: fixture MEMANG ada GPS.
    $srcTmp = tempnam(sys_get_temp_dir(), 'src');
    file_put_contents($srcTmp, $bytes);
    $srcExif = @exif_read_data($srcTmp);
    expect($srcExif !== false && (isset($srcExif['GPSLatitude']) || isset($srcExif['GPSLatitudeRef'])))->toBeTrue();

    $asset = app(UploadService::class)->store($file, 'gallery', $project);

    // Output: TIADA GPS.
    $outPath = Storage::disk('local')->path($asset->path);
    $outExif = @exif_read_data($outPath);
    $hasGps = $outExif !== false && (isset($outExif['GPSLatitude']) || isset($outExif['GPSLatitudeRef']));
    expect($hasGps)->toBeFalse();
});

it('re-encodes and resizes images, storing a valid asset (§11.4)', function () {
    $project = Project::factory()->create();
    $file = UploadedFile::fake()->image('hero.jpg', 4000, 3000); // 4000px lebar

    $asset = app(UploadService::class)->store($file, 'hero', $project);

    expect($asset->kind)->toBe('hero');
    expect($asset->width)->toBeLessThanOrEqual(2400); // sisi panjang hero ≤ 2400
    expect(Storage::disk('local')->exists($asset->path))->toBeTrue();
    // Fail output ialah imej sah.
    expect(getimagesizefromstring(Storage::disk('local')->get($asset->path)))->not->toBeFalse();
});

it('validates PDF magic bytes (§11.4)', function () {
    $project = Project::factory()->create();
    $notPdf = fakeUpload('NOTPDFCONTENT', 'doc.pdf', 'application/pdf');

    expect(fn () => app(UploadService::class)->store($notPdf, 'doc', $project))
        ->toThrow(UploadException::class);

    $realPdf = fakeUpload('%PDF-1.4 fake pdf body', 'ok.pdf', 'application/pdf');
    $asset = app(UploadService::class)->store($realPdf, 'doc', $project);
    expect($asset->kind)->toBe('doc');
});
