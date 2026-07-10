<?php

use App\Enums\Tier;
use App\Models\Project;
use App\Models\ProjectSection;
use App\Services\Ai\PromptBuilder;

// Fasa 12 W6 — data wizard penuh kini mengalir ke prompt AI.

function enrichedMosque(): Project
{
    [$project] = picSession(['tier' => Tier::MasjidKariah]);
    enablePages($project, ['utama', 'hubungi', 'visi_misi', 'perutusan', 'soalan_lazim', 'berita', 'kuliah_mingguan', 'nikah', 'info_pelawat']);
    ProjectSection::create(['project_id' => $project->id, 'section_key' => 'step_2', 'data' => ['mood' => 'tenang_khusyuk']]);
    ProjectSection::create(['project_id' => $project->id, 'section_key' => 'step_4', 'data' => ['panels' => [
        'sejarah' => ['mode' => 'tulis_penuh', 'full_text' => 'Masjid ini dibina pada tahun 1965 oleh penduduk kampung dengan usaha gotong-royong.'],
        'visi_misi' => ['visi' => 'Menjadi masjid contoh dalam memakmurkan syiar Islam.', 'misi' => 'Menyediakan program ibadah.', 'moto' => 'Makmur Bersama'],
        'perutusan' => ['role' => 'Nazir', 'name' => 'Ustaz Ahmad Farid', 'mode' => 'tulis_penuh', 'message' => 'Selamat datang ke laman rasmi kami. Marilah memakmurkan masjid.'],
        'soalan_lazim' => ['faqs' => [['q' => 'Bila waktu pejabat masjid?', 'a' => 'Isnin hingga Jumaat, 9 pagi hingga 5 petang.']]],
        'berita' => ['seed_items' => [['tajuk' => 'Majlis Khatam Al-Quran', 'tarikh' => '15 Ogos', 'ringkasan' => 'Sertai majlis khatam tahunan.']]],
        'kuliah_mingguan' => ['sessions' => [['day' => 'Isnin', 'time' => '8:30 malam', 'topic' => 'Tafsir', 'speaker' => 'Ustaz Kamal', 'kitab' => 'Tafsir Ibnu Kathir']]],
        'nikah' => ['short_desc' => 'Khidmat akad nikah', 'full_desc' => 'Khidmat akad nikah lengkap dengan jurunikah bertauliah.', 'apply_method' => 'Hubungi pejabat.', 'contact_person' => 'Ustaz Ali'],
        'info_pelawat' => ['dress_code' => 'Pakaian sopan menutup aurat.', 'getting_here' => 'Berhampiran stesen LRT.'],
    ]]]);

    return $project->fresh();
}

it('feeds rich wizard content into the AI prompt', function () {
    $prompt = app(PromptBuilder::class)->build(enrichedMosque());
    $u = $prompt['user'];

    expect($u)->toContain('1965')                    // sejarah penuh
        ->toContain('Menjadi masjid contoh')         // visi
        ->toContain('waktu pejabat masjid')          // FAQ soalan
        ->toContain('Majlis Khatam')                 // seed berita
        ->toContain('Tafsir Ibnu Kathir');           // kuliah kitab

    expect($prompt['requested_keys'])
        ->toContain('visi_misi', 'perutusan', 'faq', 'announcements', 'kuliah', 'services', 'visitor_info');
});

it('requests announcements ONLY when seed items exist', function () {
    $project = enrichedMosque();
    $s = $project->sections()->where('section_key', 'step_4')->first();
    $d = $s->data;
    unset($d['panels']['berita']);
    $s->update(['data' => $d]);

    $keys = app(PromptBuilder::class)->build($project->fresh())['requested_keys'];

    expect($keys)->not->toContain('announcements');
});

it('sends perutusan role + message but NOT the person name (nor service contact)', function () {
    $u = app(PromptBuilder::class)->build(enrichedMosque())['user'];

    expect($u)->toContain('Nazir')                   // jawatan dihantar
        ->toContain('memakmurkan masjid')            // teks perutusan dihantar
        ->not->toContain('Ustaz Ahmad Farid')        // nama perutusan TIDAK dihantar
        ->not->toContain('Ustaz Ali');               // contact_person khidmat TIDAK dihantar
});
