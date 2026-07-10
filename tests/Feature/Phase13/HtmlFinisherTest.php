<?php

use App\Enums\Tier;
use App\Models\Project;
use App\Models\ProjectSection;
use App\Models\Verse;
use App\Services\HtmlDraftFinisher;

// §Fasa 13 W3 — pasca-proses HTML draf (placeholder verbatim + chrome DRAF).

function finisherMosque(): Project
{
    [$project] = picSession(['tier' => Tier::MasjidKariah, 'jakim_zone' => 'WLY01']);
    ProjectSection::create(['project_id' => $project->id, 'section_key' => 'step_1', 'data' => [
        'phone_primary' => '03-6201 2345', 'email' => 'surau@contoh.my',
    ]]);
    ProjectSection::create(['project_id' => $project->id, 'section_key' => 'step_4', 'data' => ['panels' => [
        'perutusan' => ['role' => 'Nazir', 'name' => 'Ustaz Ahmad Farid', 'message' => 'Salam.'],
        'ajk' => ['members' => [['name' => 'Encik Zaid', 'position' => 'Pengerusi'], ['name' => 'Puan Siti', 'position' => 'Setiausaha']]],
        'infaq' => ['bank_name' => 'Bank Islam', 'bank_account' => '1234567890', 'account_holder' => 'Masjid Ujian'],
    ]]]);

    return $project->fresh();
}

function runFinisher(Project $project, string $html): string
{
    return app(HtmlDraftFinisher::class)->finish($project, $html, 1);
}

function tokenHtml(): string
{
    return '<!DOCTYPE html><html lang="ms"><head><title>Masjid Ujian</title></head><body>'
        .'<section id="perutusan"><p>"Kata alu-aluan"</p>[[PERUTUSAN_NAMA]]</section>'
        .'<section id="ajk">[[AJK_GRID]]</section>'
        .'<section id="solat">[[WAKTU_SOLAT]]</section>'
        .'<section id="infaq">[[BANK_BLOCK]]</section>'
        .'[[CONTACT_STRIP]]'
        .'<img src="[[HERO_IMAGE]]" alt="hero">'
        .'</body></html>';
}

it('replaces all placeholders with verbatim local content', function () {
    $out = runFinisher(finisherMosque(), tokenHtml());

    expect($out)
        ->toContain('Ustaz Ahmad Farid')       // perutusan nama (render LOKAL)
        ->toContain('Nazir')                    // jawatan
        ->toContain('Encik Zaid')               // AJK
        ->toContain('1234567890')               // bank
        ->toContain('03-6201 2345')             // hubungi
        ->toContain('JAKIM e-Solat')            // waktu solat statik
        ->not->toContain('[[');                 // tiada token tinggal
});

it('removes the hero img tag when no upload image exists', function () {
    $out = runFinisher(finisherMosque(), tokenHtml());
    expect($out)->not->toContain('[[HERO_IMAGE]]')->not->toContain('alt="hero"');
});

it('always injects noindex, draft banner, watermark and the — DRAF title', function () {
    $out = runFinisher(finisherMosque(), tokenHtml());
    expect($out)
        ->toContain('name="robots"')
        ->toContain('reka-draft-banner')
        ->toContain('reka-watermark')
        ->toContain('— DRAF')
        ->toContain('DRAF SAMPEL');
});

it('injects mandatory contact and bank blocks when the AI omits the tokens', function () {
    $bare = '<!DOCTYPE html><html><head><title>X</title></head><body><h1>Tiada token</h1></body></html>';
    $out = runFinisher(finisherMosque(), $bare);

    expect($out)
        ->toContain('data-reka="contact"')
        ->toContain('1234567890')               // bank disuntik sebelum </body>
        ->toContain('03-6201 2345');            // hubungi disuntik
});

it('injects the official verse for the AYAT_ARAB token (mosque)', function () {
    Verse::create([
        'arabic_text' => 'إِنَّمَا يَعْمُرُ', 'translation_bm' => 'Terjemahan.',
        'source_label' => 'At-Taubah:18', 'verified_by' => 'Ujian', 'is_active' => true,
    ]);
    $out = runFinisher(finisherMosque(), '<!DOCTYPE html><html><head><title>X</title></head><body>[[AYAT_ARAB]]</body></html>');

    expect($out)->toContain('إِنَّمَا يَعْمُرُ')->toContain('data-reka="verse"');
});
