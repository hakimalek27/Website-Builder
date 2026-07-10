<?php

use App\Enums\Tier;
use App\Models\Project;
use App\Models\ProjectSection;
use App\Services\Ai\HtmlPromptBuilder;
use Database\Seeders\DesignPackageSeeder;

// §Fasa 13 W2 — prompt jurutera (Peringkat 1) + tweak (Peringkat 2).

beforeEach(fn () => $this->seed(DesignPackageSeeder::class));

function htmlMosque(): Project
{
    [$project] = picSession(['tier' => Tier::MasjidKariah]);
    enablePages($project, ['utama', 'hubungi', 'visi_misi', 'perutusan', 'ajk', 'infaq']);
    $project->design()->create([
        'package_key' => 'warisan_hijau',
        'overrides' => ['header_style' => 'gradien', 'divider' => 'garis-emas', 'layout' => 'hero-belah'],
    ]);
    ProjectSection::create(['project_id' => $project->id, 'section_key' => 'step_1', 'data' => [
        'phone_primary' => '03-6201 2345', 'email' => 'surau@contoh.my',
    ]]);
    ProjectSection::create(['project_id' => $project->id, 'section_key' => 'step_2', 'data' => [
        'mood' => 'megah_berwibawa',
        'islamic_elements' => ['corak_geometri' => true],
    ]]);
    ProjectSection::create(['project_id' => $project->id, 'section_key' => 'step_4', 'data' => ['panels' => [
        'visi_misi' => ['visi' => 'Menjadi masjid contoh.', 'misi' => 'Program ibadah.'],
        'perutusan' => ['role' => 'Nazir', 'name' => 'Ustaz Ahmad Farid', 'message' => 'Selamat datang ke laman kami.'],
        'ajk' => ['members' => [['name' => 'Encik Zaid', 'position' => 'Pengerusi']]],
        'infaq' => ['bank_name' => 'Bank Islam', 'bank_account' => '1234567890', 'account_holder' => 'Masjid Ujian', 'categories' => [['title' => 'Tabung Am', 'desc' => 'Untuk operasi.']]],
    ]]]);

    return $project->fresh();
}

it('builds an engineer request carrying design spec, page labels and placeholders', function () {
    $req = app(HtmlPromptBuilder::class)->engineerRequest(htmlMosque());
    $u = $req['user'];

    // Reka bentuk terbawa TEPAT.
    expect($u)->toContain('#1B5E3F')          // warna primary warisan_hijau
        ->toContain('hero-belah')             // layout override
        ->toContain('gradien')                // header override
        ->toContain('garis-emas');            // divider override

    // Halaman dipilih (label BM).
    expect($u)->toContain('Visi & Misi')->toContain('Infaq / Derma');

    // §Fasa 14 — arahan id seksyen deterministik (page_key).
    expect($u)->toContain('<section id="utama">')->toContain('<section id="infaq">');

    // Placeholder wajib + bersyarat.
    expect($u)->toContain('[[CONTACT_STRIP]]')
        ->toContain('[[BANK_BLOCK]]')
        ->toContain('[[AJK_GRID]]')
        ->toContain('[[WAKTU_SOLAT]]')        // masjid
        ->toContain('[[PERUTUSAN_NAMA]]');

    // System = jurutera prompt.
    expect($req['system'])->toContain('jurutera prompt');
});

it('never leaks PII into the engineer request', function () {
    $u = app(HtmlPromptBuilder::class)->engineerRequest(htmlMosque())['user'];

    expect($u)->not->toContain('1234567890')       // no akaun bank
        ->not->toContain('03-6201 2345')           // telefon
        ->not->toContain('surau@contoh.my')        // emel
        ->not->toContain('Ustaz Ahmad Farid');     // nama perutusan
});

it('omits mosque-only placeholders for an NGO project without bank', function () {
    [$project] = picSession(['tier' => Tier::NgoKomuniti]);
    enablePages($project, ['utama', 'hubungi', 'program_utama']);
    ProjectSection::create(['project_id' => $project->id, 'section_key' => 'step_2', 'data' => ['mood' => 'profesional_ringkas']]);
    ProjectSection::create(['project_id' => $project->id, 'section_key' => 'step_4', 'data' => ['panels' => [
        'program_utama' => ['programs' => [['name' => 'Bantuan Ramadan', 'desc' => 'Agihan bantuan.']]],
    ]]]);

    $u = app(HtmlPromptBuilder::class)->engineerRequest($project->fresh())['user'];

    expect($u)->toContain('[[CONTACT_STRIP]]')
        ->not->toContain('[[WAKTU_SOLAT]]')
        ->not->toContain('[[AYAT_ARAB]]')
        ->not->toContain('[[BANK_BLOCK]]');       // tiada bank
});

it('builds a tweak request carrying current HTML and scrubbed instructions', function () {
    $req = app(HtmlPromptBuilder::class)->stage2TweakRequest(
        htmlMosque(),
        '<!DOCTYPE html><html><body>Draf lama</body></html>',
        ['categories' => ['warna'], 'message' => 'Tukar hijau ke biru. Hubungi saya 0123456789.'],
    );

    expect($req['user'])
        ->toContain('Draf lama')                 // HTML semasa
        ->toContain('Tukar hijau ke biru')       // arahan
        ->toContain('[nombor dibuang]')          // telefon di-scrub
        ->not->toContain('0123456789');
});
