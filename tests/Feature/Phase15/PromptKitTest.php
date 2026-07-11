<?php

use App\Enums\Tier;
use App\Models\Asset;
use App\Models\Project;
use App\Models\ProjectSection;
use App\Services\Ai\HtmlPromptBuilder;
use Database\Seeders\DesignPackageSeeder;
use Illuminate\Support\Str;

// §Fasa 15 W4 — rombakan prompt: kit cheat-sheet, DNA pakej, keunikan, blueprint lampiran server.

beforeEach(fn () => $this->seed(DesignPackageSeeder::class));

function kitMosque(array $step6 = ['hero_mode' => 'stok_sementara'], bool $withLogo = false): Project
{
    [$project] = picSession(['tier' => Tier::MasjidKariah]);
    enablePages($project, ['utama', 'hubungi', 'infaq']);
    $project->design()->create(['package_key' => 'arang_moden', 'overrides' => ['style_seed' => 5]]);
    ProjectSection::create(['project_id' => $project->id, 'section_key' => 'step_1', 'data' => ['phone_primary' => '03-6201 2345', 'email' => 'surau@contoh.my']]);
    ProjectSection::create(['project_id' => $project->id, 'section_key' => 'step_2', 'data' => ['mood' => 'megah_berwibawa']]);
    ProjectSection::create(['project_id' => $project->id, 'section_key' => 'step_4', 'data' => ['panels' => [
        'perutusan' => ['role' => 'Nazir', 'name' => 'Ustaz Ahmad Farid'],
        'infaq' => ['bank_name' => 'Bank Islam', 'bank_account' => '1234567890', 'account_holder' => 'Masjid Ujian'],
    ]]]);
    ProjectSection::create(['project_id' => $project->id, 'section_key' => 'step_6', 'data' => $step6]);
    if ($withLogo) {
        Asset::create(['project_id' => $project->id, 'kind' => 'logo', 'path' => 'a/'.Str::ulid().'.png', 'original_name' => 'l.png', 'mime' => 'image/png', 'size' => 3000, 'sort' => 0]);
    }

    return $project->fresh();
}

it('appends the kit cheat-sheet, blueprints and current year to the stage-2 message (server-side)', function () {
    $req = app(HtmlPromptBuilder::class)->stage2Request(kitMosque(), 'PROMPT JURUTERA: bina laman.');
    $u = $req['user'];

    expect($u)
        ->toContain('PROMPT JURUTERA: bina laman.')       // prompt P1 kekal
        ->toContain('KELAS KIT REKA')                     // cheat-sheet
        ->toContain('rk-hero')                            // blueprint
        ->toContain('rk-footer')
        ->toContain('TAHUN SEMASA: '.now()->year);
});

it('carries package DNA, uniqueness directive and kit guidance in the engineer request', function () {
    $u = app(HtmlPromptBuilder::class)->engineerRequest(kitMosque())['user'];

    expect($u)
        ->toContain('DNA REKA BENTUK PAKEJ')
        ->toContain('arang')                              // watak DNA arang_moden
        ->toContain('KEUNIKAN')                           // arahan anti-pendua
        ->toContain('KIT REKA')
        ->toContain('rk-*')
        ->toContain('TAHUN SEMASA: '.now()->year);
});

it('drops vestigial radius/headerStyle from the colour spec', function () {
    $u = app(HtmlPromptBuilder::class)->engineerRequest(kitMosque())['user'];
    // headerStyle 'transparent-to-solid' + radius adalah token vestigial yang mengelirukan model.
    expect($u)->not->toContain('transparent-to-solid')->not->toContain('headerStyle');
});

it('describes each design variant with its kit class (not a bare keyword)', function () {
    $u = app(HtmlPromptBuilder::class)->engineerRequest(kitMosque())['user'];
    expect($u)->toContain('kelas_kit')->toContain('arahan_seni')->toContain('rk-section--dark');
});

it('adds the LOGO placeholder only when a logo asset exists', function () {
    $withLogo = app(HtmlPromptBuilder::class)->engineerRequest(kitMosque(withLogo: true))['user'];
    $without = app(HtmlPromptBuilder::class)->engineerRequest(kitMosque())['user'];

    expect($withLogo)->toContain('[[LOGO]]');
    expect($without)->not->toContain('[[LOGO]]');
});

it('adds HERO_IMAGE for stok_sementara and VIDEO_LINK when a video url is set', function () {
    $u = app(HtmlPromptBuilder::class)->engineerRequest(kitMosque(['hero_mode' => 'stok_sementara', 'video_url' => 'https://youtu.be/x']))['user'];
    expect($u)->toContain('[[HERO_IMAGE]]')->toContain('[[VIDEO_LINK]]');
});

it('instructs the server-only token contract and no-role-writing for perutusan', function () {
    $u = app(HtmlPromptBuilder::class)->engineerRequest(kitMosque())['user'];
    expect($u)->toContain('HANYA token')->toContain('JANGAN tulis nama ATAU jawatan');
});

it('keeps the stage-2 append free of PII', function () {
    $u = app(HtmlPromptBuilder::class)->stage2Request(kitMosque(), 'PROMPT')['user'];
    expect($u)->not->toContain('1234567890')->not->toContain('03-6201 2345')->not->toContain('Ustaz Ahmad Farid');
});

it('builds a polish request over raw tokened html preserving tokens', function () {
    $issues = [['type' => 'logo_missing', 'mesej' => 'Logo tidak dipaparkan.']];
    $suggestions = [['type' => 'low_depth', 'mesej' => 'Tambah kedalaman bayang.']];
    $req = app(HtmlPromptBuilder::class)->stage2PolishRequest(kitMosque(), '<body>[[CONTACT_STRIP]] draf</body>', $issues, $suggestions);

    expect($req['user'])
        ->toContain('[[CONTACT_STRIP]]')                  // token dikekalkan
        ->toContain('Logo tidak dipaparkan.')
        ->toContain('Tambah kedalaman bayang.')
        ->toContain('KELAS KIT REKA');
});
