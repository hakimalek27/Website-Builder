<?php

use App\Enums\Tier;
use App\Models\Asset;
use App\Models\Project;
use App\Models\ProjectSection;
use App\Services\HtmlDraftFinisher;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

// §Fasa 15 W3 — finisher premium: kit, imej (logo/stok/hero), fix kemasan.

beforeEach(fn () => Storage::fake('local'));

/** Projek masjid dgn step_1/step_4/step_6 + hero_mode diberi. */
function premiumProject(string $heroMode = 'stok_sementara', array $extraStep6 = []): Project
{
    [$project] = picSession(['tier' => Tier::MasjidKariah, 'jakim_zone' => 'WLY01']);
    ProjectSection::create(['project_id' => $project->id, 'section_key' => 'step_1', 'data' => [
        'phone_primary' => '03-6201 2345', 'email' => 'surau@contoh.my',
    ]]);
    ProjectSection::create(['project_id' => $project->id, 'section_key' => 'step_4', 'data' => ['panels' => [
        'perutusan' => ['role' => 'Presiden', 'name' => 'Ustaz Ahmad Farid'],
        'infaq' => ['bank_name' => 'Bank Islam', 'bank_account' => '1234567890', 'account_holder' => 'Masjid Ujian'],
    ]]]);
    ProjectSection::create(['project_id' => $project->id, 'section_key' => 'step_6', 'data' => array_merge(['hero_mode' => $heroMode], $extraStep6)]);

    return $project->fresh();
}

/** Simpan aset imej sebenar (GD) ke disk fake + rekod Asset. */
function storeImageAsset(Project $p, string $kind, int $size, string $mime = 'image/png', ?string $svg = null, int $sort = 0): void
{
    $ext = $svg !== null ? 'svg' : 'png';
    $rel = "assets/{$p->id}/".Str::ulid().'.'.$ext;
    if ($svg !== null) {
        $bytes = $svg;
    } else {
        $im = imagecreatetruecolor(200, 120);
        ob_start();
        imagepng($im);
        $bytes = (string) ob_get_clean();
        imagedestroy($im);
    }
    Storage::disk('local')->put($rel, $bytes);
    Asset::create(['project_id' => $p->id, 'kind' => $kind, 'path' => $rel, 'original_name' => $kind.'.'.$ext, 'mime' => $mime, 'size' => $size, 'sort' => $sort]);
}

function fin(Project $p, string $html): string
{
    return app(HtmlDraftFinisher::class)->finish($p, $html, 1);
}

it('injects the premium kit style block', function () {
    $out = fin(premiumProject(), '<!DOCTYPE html><html><head><title>X</title></head><body><h1>Hi</h1></body></html>');
    expect($out)->toContain('id="reka-kit"')->toContain('--rk-primary')->toContain('.rk-card--lembut');
});

it('fills a themed stock scene for stok_sementara hero', function () {
    $html = '<!DOCTYPE html><html><head><title>X</title></head><body><section id="utama"><img src="[[HERO_IMAGE]]" alt=""></section></body></html>';
    $out = fin(premiumProject('stok_sementara'), $html);
    expect($out)->toContain('src="data:image/svg+xml,')->not->toContain('[[HERO_IMAGE]]');
});

it('embeds an uploaded logo and strips the tag when absent', function () {
    $p = premiumProject();
    storeImageAsset($p, 'logo', 40_000, 'image/png');
    $out = fin($p->fresh(), '<!DOCTYPE html><html><head><title>X</title></head><body><img src="[[LOGO]]" alt="logo"></body></html>');
    expect($out)->toContain('src="data:image/png;base64,')->not->toContain('[[LOGO]]');

    // Tanpa logo → tag <img> dibuang.
    $out2 = fin(premiumProject(), '<!DOCTYPE html><html><head><title>X</title></head><body><img src="[[LOGO]]" alt="logo"></body></html>');
    expect($out2)->not->toContain('[[LOGO]]')->not->toContain('alt="logo"');
});

it('embeds an svg logo as svg+xml data-URI', function () {
    $p = premiumProject();
    storeImageAsset($p, 'logo', 3000, 'image/svg+xml', svg: '<svg xmlns="http://www.w3.org/2000/svg"><rect width="10" height="10"/></svg>');
    $out = fin($p->fresh(), '<!DOCTYPE html><html><head><title>X</title></head><body><img src="[[LOGO]]"></body></html>');
    expect($out)->toContain('src="data:image/svg+xml;base64,');
});

it('re-encodes an oversized hero upload instead of dropping it', function () {
    $p = premiumProject('upload');
    storeImageAsset($p, 'hero', 2_000_000, 'image/jpeg');   // >1.5MB — dulu senyap jadi gradien
    $out = fin($p->fresh(), '<!DOCTYPE html><html><head><title>X</title></head><body><section id="utama"><img src="[[HERO_IMAGE]]"></section></body></html>');
    expect($out)->toContain('src="data:image/jpeg;base64,')->not->toContain('[[HERO_IMAGE]]');
});

it('uses the second hero upload for a section image', function () {
    $p = premiumProject('upload');
    storeImageAsset($p, 'hero', 40_000, 'image/png', sort: 0);
    storeImageAsset($p, 'hero', 40_000, 'image/png', sort: 1);
    $out = fin($p->fresh(), '<!DOCTYPE html><html><head><title>X</title></head><body><img src="[[HERO_IMAGE]]"><img src="[[IMG_SECTION_1]]"></body></html>');
    expect($out)->not->toContain('[[IMG_SECTION_1]]');
    expect(substr_count($out, 'data:image/png;base64,'))->toBeGreaterThanOrEqual(2);
});

it('renders a video button from step_6 video_url', function () {
    $p = premiumProject('stok_sementara', ['video_url' => 'https://youtu.be/abc123']);
    $out = fin($p->fresh(), '<!DOCTYPE html><html><head><title>X</title></head><body>[[VIDEO_LINK]]</body></html>');
    expect($out)->toContain('rk-btn--kaca')->toContain('https://youtu.be/abc123')->not->toContain('[[VIDEO_LINK]]');
});

it('corrects a hallucinated copyright year to the current year', function () {
    $out = fin(premiumProject(), '<!DOCTYPE html><html><head><title>X</title></head><body><footer>&copy; 2024 Masjid Ujian</footer></body></html>');
    expect($out)->toContain('&copy; '.now()->year)->not->toContain('&copy; 2024');
});

it('removes a duplicated position injected by the AI next to perutusan', function () {
    $html = '<!DOCTYPE html><html><head><title>X</title></head><body><section id="perutusan"><p>"Salam"</p>[[PERUTUSAN_NAMA]]<span>Presiden</span></section></body></html>';
    $out = fin(premiumProject(), $html);
    expect($out)->toContain('Ustaz Ahmad Farid');
    expect(substr_count($out, 'Presiden'))->toBe(1);   // hanya blok pelayan, duplikasi AI dibuang
});

it('removes an empty waiting-for-data table', function () {
    $html = '<!DOCTYPE html><html><head><title>X</title></head><body><table><thead><tr><th>Nama</th></tr></thead><tbody><!-- Data akan diisi oleh pelayan --></tbody></table></body></html>';
    $out = fin(premiumProject(), $html);
    expect($out)->not->toContain('<table')->not->toContain('akan diisi');
});
