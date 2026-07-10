<?php

use App\Enums\GenerationStatus;
use App\Enums\GenerationType;
use App\Enums\Tier;
use App\Models\NotificationLog;
use App\Models\ProjectDesign;
use App\Models\Setting;
use App\Services\DraftGenerationService;
use App\Services\DraftQaService;
use Database\Seeders\DesignPackageSeeder;
use Database\Seeders\VerseLibrarySeeder;
use Illuminate\Support\Facades\Mail;

// §Fasa 14 W3 — QA auto pasca-jana (seksyen + kontras). Helper dalam tests/Pest.php.

beforeEach(fn () => $this->seed(DesignPackageSeeder::class));

function qaHtml(string $body): string
{
    return '<!DOCTYPE html><html lang="ms"><head><title>Masjid Ujian</title></head><body>'.$body.'</body></html>';
}

it('passes when every enabled page has a section and tokens are readable', function () {
    [$project] = picSession(['tier' => Tier::SurauRingkas]);
    enablePages($project, ['utama', 'hubungi']);

    $qa = app(DraftQaService::class)->analyse($project, qaHtml(
        '<section id="utama">Utama</section><section id="hubungi">Hubungi</section>'
    ));

    expect($qa['passed'])->toBeTrue();
    expect($qa['issues'])->toBe([]);
    expect($qa['checked_at'])->not->toBeEmpty();
});

it('flags a missing section when neither id nor label appears', function () {
    [$project] = picSession(['tier' => Tier::SurauRingkas]);
    enablePages($project, ['utama', 'hubungi']);

    $qa = app(DraftQaService::class)->analyse($project, qaHtml('<section id="utama">Utama</section>'));

    expect($qa['passed'])->toBeFalse();
    $missing = collect($qa['issues'])->firstWhere('type', 'missing_section');
    expect($missing['page_key'])->toBe('hubungi');
    expect($missing['mesej'])->toContain('tidak ditemui');
});

it('rescues a legacy draft via the page label fallback', function () {
    [$project] = picSession(['tier' => Tier::SurauRingkas]);
    enablePages($project, ['utama', 'hubungi']);

    // Tiada id="hubungi" tetapi label "Hubungi" muncul sebagai teks.
    $qa = app(DraftQaService::class)->analyse($project, qaHtml(
        '<section id="utama">Utama</section><div><h2>Hubungi Kami</h2></div>'
    ));

    expect($qa['passed'])->toBeTrue();
});

it('accepts the home page under id hero', function () {
    [$project] = picSession(['tier' => Tier::SurauRingkas]);
    enablePages($project, ['utama']);

    $qa = app(DraftQaService::class)->analyse($project, qaHtml('<section id="hero">Selamat Datang</section>'));

    expect($qa['passed'])->toBeTrue();
});

it('flags low token contrast from a bad palette override', function () {
    [$project] = picSession(['tier' => Tier::SurauRingkas]);
    enablePages($project, ['utama']);
    // Paksa ink hampir sama dengan bg → kontras teks badan rendah.
    ProjectDesign::create([
        'project_id' => $project->id,
        'package_key' => 'warisan_hijau',
        'overrides' => ['palette' => ['ink' => '#EEEEEE', 'bg' => '#FFFFFF']],
    ]);

    $qa = app(DraftQaService::class)->analyse($project, qaHtml('<section id="utama">Utama</section>'));

    expect($qa['passed'])->toBeFalse();
    $low = collect($qa['issues'])->firstWhere('pair', 'ink/bg');
    expect($low['type'])->toBe('low_contrast');
    expect($low['ratio'])->toBeLessThan(4.5);
});

it('flags low inline contrast but skips css variables', function () {
    [$project] = picSession(['tier' => Tier::SurauRingkas]);
    enablePages($project, ['utama']);

    $qa = app(DraftQaService::class)->analyse($project, qaHtml(
        '<section id="utama">'
        .'<p style="color:#777;background:#888">rendah</p>'
        .'<p style="color:var(--x);background:#000">abai</p>'
        .'</section>'
    ));

    $inline = collect($qa['issues'])->where('type', 'low_contrast_inline');
    expect($inline)->toHaveCount(1);
});

it('runs QA in the pipeline without blocking the draft and notifies admin on issues', function () {
    Mail::fake();
    $this->seed(VerseLibrarySeeder::class);
    Setting::put('draft_pipeline', 'html');
    Setting::put('whatsapp_gateway_url', 'https://gw.test');
    Setting::put('admin_notify_phone', '60189030363');
    htmlProviders();
    // utama (id=hero) + hubungi hadir; tetapi halaman tambahan (nama unik) TIADA → QA flag.
    fakeTwoStage(qaHtml('<section id="hero">Hi</section><section id="hubungi">[[CONTACT_STRIP]]</section>'));
    [$project] = htmlReadyProject();
    $project->pages()->updateOrCreate(['page_key' => 'galeri'], ['enabled' => true, 'sort' => 5, 'custom_name' => 'Galeri Foto Khas Unik']);

    app(DraftGenerationService::class)->request($project, GenerationType::Initial);

    $gen = $project->fresh()->generations()->first();
    expect($gen->status)->toBe(GenerationStatus::Succeeded);       // QA tidak menghalang
    expect($gen->input_snapshot['qa']['passed'])->toBeFalse();
    expect(collect($gen->input_snapshot['qa']['issues'])->pluck('page_key'))->toContain('galeri');
    expect(NotificationLog::where('event', 'qa.flagged')->count())->toBeGreaterThan(0);
});

it('does not notify admin when QA passes', function () {
    Mail::fake();
    $this->seed(VerseLibrarySeeder::class);
    Setting::put('draft_pipeline', 'html');
    htmlProviders();
    // Kedua-dua seksyen utama+hubungi hadir.
    fakeTwoStage(qaHtml('<section id="utama">Hi</section><section id="hubungi">[[CONTACT_STRIP]]</section>'));
    [$project] = htmlReadyProject();

    app(DraftGenerationService::class)->request($project, GenerationType::Initial);

    $gen = $project->fresh()->generations()->first();
    expect($gen->status)->toBe(GenerationStatus::Succeeded);
    expect($gen->input_snapshot['qa']['passed'])->toBeTrue();
    expect(NotificationLog::where('event', 'qa.flagged')->count())->toBe(0);
});
