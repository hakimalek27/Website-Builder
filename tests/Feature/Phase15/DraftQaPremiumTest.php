<?php

use App\Enums\Tier;
use App\Models\Asset;
use App\Models\Project;
use App\Models\ProjectSection;
use App\Services\DraftQaService;
use Database\Seeders\DesignPackageSeeder;
use Illuminate\Support\Str;

// §Fasa 15 W5 — QA premium: issues struktural baharu + suggestions estetik.

beforeEach(fn () => $this->seed(DesignPackageSeeder::class));

function qaProject(array $step2 = [], array $step6 = ['hero_mode' => 'teks_sahaja']): Project
{
    [$project] = picSession(['tier' => Tier::MasjidKariah]);
    enablePages($project, ['utama', 'hubungi']);
    ProjectSection::create(['project_id' => $project->id, 'section_key' => 'step_2', 'data' => array_merge(['mood' => 'tenang_khusyuk'], $step2)]);
    ProjectSection::create(['project_id' => $project->id, 'section_key' => 'step_6', 'data' => $step6]);

    return $project->fresh();
}

$clean = fn (string $body, string $raw = '') => [
    'final' => '<!DOCTYPE html><html><head><style id="reka-kit"></style><title>X</title></head><body>'.$body.'</body></html>',
    'raw' => $raw,
];

it('flags a missing logo when a logo asset exists but the token was omitted', function () use ($clean) {
    $p = qaProject();
    Asset::create(['project_id' => $p->id, 'kind' => 'logo', 'path' => 'a/'.Str::ulid().'.png', 'original_name' => 'l.png', 'mime' => 'image/png', 'size' => 3000, 'sort' => 0]);
    $c = $clean('<section id="utama">Hi</section><section id="hubungi">x</section>', 'raw draf tanpa token logo');

    $qa = app(DraftQaService::class)->analyse($p->fresh(), $c['final'], $c['raw']);
    expect(collect($qa['issues'])->pluck('type'))->toContain('logo_missing');
    expect(collect($qa['issues'])->firstWhere('type', 'logo_missing')['polishable'])->toBeTrue();
});

it('flags a missing hero image when a hero is expected', function () use ($clean) {
    $p = qaProject(step6: ['hero_mode' => 'stok_sementara']);
    $c = $clean('<section id="utama">Hi</section><section id="hubungi">x</section>', 'raw tanpa hero token');

    $qa = app(DraftQaService::class)->analyse($p, $c['final'], $c['raw']);
    expect(collect($qa['issues'])->pluck('type'))->toContain('hero_image_missing');
});

it('flags missing islamic elements when chosen but not rendered', function () use ($clean) {
    $p = qaProject(step2: ['islamic_elements' => ['corak_geometri' => true]]);
    $c = $clean('<section id="utama">Hi</section><section id="hubungi">x</section>');

    $qa = app(DraftQaService::class)->analyse($p, $c['final'], null);
    expect(collect($qa['issues'])->pluck('type'))->toContain('islamic_missing');

    // Dengan corak → tiada isu.
    $ok = $clean('<section id="utama" class="rk-pattern rk-pattern--bintang">Hi</section><section id="hubungi">x</section>');
    $qa2 = app(DraftQaService::class)->analyse($p, $ok['final'], null);
    expect(collect($qa2['issues'])->pluck('type'))->not->toContain('islamic_missing');
});

it('flags a wrong copyright year as a regression guard', function () use ($clean) {
    $p = qaProject();
    $c = $clean('<section id="utama">Hi</section><section id="hubungi">x</section><footer>&copy; 2019 X</footer>');
    $qa = app(DraftQaService::class)->analyse($p, $c['final'], null);
    expect(collect($qa['issues'])->pluck('type'))->toContain('wrong_year');
});

it('flags an empty tbody scaffold', function () use ($clean) {
    $p = qaProject();
    $c = $clean('<section id="utama">Hi</section><section id="hubungi">x</section><table><tbody></tbody></table>');
    $qa = app(DraftQaService::class)->analyse($p, $c['final'], null);
    expect(collect($qa['issues'])->pluck('type'))->toContain('empty_tbody');
});

it('only emits aesthetic suggestions for large raw drafts', function () {
    $p = qaProject();
    $final = '<!DOCTYPE html><html><head><style id="reka-kit"></style></head><body><section id="utama">Hi</section><section id="hubungi">x</section></body></html>';

    // Raw kecil → tiada suggestions.
    $small = app(DraftQaService::class)->analyse($p, $final, '<body>kecil</body>');
    expect($small['suggestions'])->toBeEmpty();

    // Raw besar & flat (tiada rk-, tiada bayang) → suggestions estetik.
    $bigFlat = '<body>'.str_repeat('<div>teks biasa tanpa gaya premium. ', 900).'</body>';
    $big = app(DraftQaService::class)->analyse($p, $final, $bigFlat);
    expect(collect($big['suggestions'])->pluck('type'))->toContain('low_kit_usage');
});

it('does not suggest depth fixes for a kit-heavy draft (no false flag)', function () {
    $p = qaProject();
    $final = '<!DOCTYPE html><html><head><style id="reka-kit"></style></head><body><section id="utama">Hi</section><section id="hubungi">x</section></body></html>';
    // Raw besar TAPI guna kit banyak → tiada suggestion low_depth.
    $bigKit = '<body>'.str_repeat('<div class="rk-card rk-card--terapung rk-card-hover rk-shadow-elev">isi</div> ', 400).'</body>';
    $qa = app(DraftQaService::class)->analyse($p, $final, $bigKit);
    expect(collect($qa['suggestions'])->pluck('type'))->not->toContain('low_kit_usage');
});

it('keeps passed independent of suggestions', function () {
    $p = qaProject();
    $final = '<!DOCTYPE html><html><head><style id="reka-kit"></style></head><body><section id="utama">Hi</section><section id="hubungi">x</section></body></html>';
    $bigFlat = '<body>'.str_repeat('<div>teks biasa. ', 900).'</body>';
    $qa = app(DraftQaService::class)->analyse($p, $final, $bigFlat);

    expect($qa['passed'])->toBeTrue();               // tiada issues struktural
    expect($qa['suggestions'])->not->toBeEmpty();     // tetapi ada suggestions estetik
});
