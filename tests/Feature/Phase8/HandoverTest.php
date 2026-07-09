<?php

use App\Enums\AiDriver;
use App\Enums\GenerationType;
use App\Enums\ProjectStatus;
use App\Enums\Tier;
use App\Exceptions\GateException;
use App\Livewire\Wizard\WizardStep;
use App\Models\AiProvider;
use App\Models\Generation;
use App\Models\ProjectSection;
use App\Services\ApprovalService;
use App\Services\DesignRerenderService;
use App\Services\DraftGenerationService;
use App\Services\DraftRenderer;
use App\Services\HandoverExporter;
use App\Services\SanitySeedBuilder;
use App\Services\SpecBuilder;
use Database\Seeders\DesignPackageSeeder;
use Database\Seeders\VerseLibrarySeeder;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

// Fasa 8 — pemapar, tweak, kelulusan, pakej serahan (§5.2 P5–P9, §14).

function readyWithDraft(ProjectStatus $status = ProjectStatus::DraftReady, int $quotaUsed = 1): array
{
    [$project, $token] = picSession(['status' => $status, 'tier' => Tier::SurauRingkas, 'quota_ai_used' => $quotaUsed, 'jakim_zone' => 'WLY01']);
    enablePages($project, ['utama', 'hubungi', 'infaq']);

    foreach ([
        'step_1' => ['official_name' => 'Masjid Ujian', 'city' => 'KL', 'state' => 'W.P. Kuala Lumpur', 'jakim_zone' => 'WLY01', 'logo_status' => 'teks_sahaja', 'gps' => '3.19, 101.73'],
        'step_2' => ['mood' => 'tenang_khusyuk', 'design_package' => 'warisan_hijau'],
        'step_4' => ['panels' => ['infaq' => ['bank_name' => 'Maybank', 'bank_account' => '1234567890', 'account_holder' => 'Masjid Ujian', 'categories' => [['title' => 'Infaq Am']]]]],
        'step_5' => ['payment_gateway' => 'manual_bank', 'cms_updater' => 'ajk_sendiri'],
        'step_6' => ['hero_mode' => 'stok_sementara'],
    ] as $key => $data) {
        ProjectSection::create(['project_id' => $project->id, 'section_key' => $key, 'data' => $data]);
    }

    AiProvider::factory()->default()->create(['driver' => AiDriver::Anthropic]);

    // Draf berjaya sedia (selesai 10 minit lalu — cooldown lepas).
    $content = validContent();
    $gen = Generation::factory()->for($project)->succeeded()->create([
        'type' => GenerationType::Initial, 'output_json' => $content, 'finished_at' => now()->subMinutes(10),
    ]);
    $path = app(DraftRenderer::class)->renderAndStore($project, $gen, $content, 1);
    $gen->update(['rendered_path' => $path]);

    return [$project, $token, $gen];
}

beforeEach(function () {
    $this->seed(DesignPackageSeeder::class);
    $this->seed(VerseLibrarySeeder::class);
});

it('design rerender uses no AI and no AI quota (§8.7)', function () {
    Http::fake();
    [$project] = readyWithDraft();

    app(DesignRerenderService::class)->rerender($project);

    Http::assertNothingSent();
    expect($project->fresh()->quota_ai_used)->toBe(1);         // AI tidak disentuh
    expect($project->fresh()->quota_design_used)->toBe(1);      // design bertambah
    expect($project->fresh()->generations()->where('type', GenerationType::DesignRender)->count())->toBe(1);
});

it('caps the design render quota at 5 (§8.7)', function () {
    [$project] = readyWithDraft();
    $project->update(['quota_design_used' => 5]);

    expect(fn () => app(DesignRerenderService::class)->rerender($project))->toThrow(GateException::class);
});

it('content tweak consumes AI quota (§8.7)', function () {
    // Projek ada infaq → skema minta kunci infaq juga.
    $content = validContent();
    $content['infaq'] = ['heading' => 'Infaq', 'paragraph' => 'Sumbangan anda membantu masjid.'];
    fakeAnthropic($content);
    [$project] = readyWithDraft(ProjectStatus::DraftReady, 1);

    app(DraftGenerationService::class)->request($project, GenerationType::ContentTweak, 'pic', [
        'categories' => ['nada'], 'message' => 'Buat lebih mesra', 'current_json' => validContent(),
    ]);

    expect($project->fresh()->quota_ai_used)->toBe(2);
});

it('approval freezes the snapshot and locks the wizard (§14.2/§4.2)', function () {
    [$project, $token, $gen] = readyWithDraft();
    ProjectSection::where('project_id', $project->id)->where('section_key', 'step_1')
        ->update(['data' => ['official_name' => 'Original', 'logo_status' => 'teks_sahaja']]);

    app(ApprovalService::class)->approve($project, $gen, [
        'pic_name' => 'Ali', 'pic_position' => 'Setiausaha', 'pic_phone' => '0123456789',
    ], '203.0.113.5', 'PHPUnit');

    expect($project->fresh()->status)->toBe(ProjectStatus::Approved);

    // Wizard kini baca-sahaja.
    $t = Livewire::test(WizardStep::class, ['token' => $token, 'step' => 1])
        ->set('data.official_name', 'Changed');
    $t->assertSet('readOnly', true);
    expect(ProjectSection::where('project_id', $project->id)->where('section_key', 'step_1')->value('data')['official_name'])->toBe('Original');
});

it('approval records identity, IP and a frozen spec snapshot (§11.1)', function () {
    [$project, $token, $gen] = readyWithDraft();

    $approval = app(ApprovalService::class)->approve($project, $gen, [
        'pic_name' => 'Siti', 'pic_position' => 'Bendahari', 'pic_phone' => '0198887777',
    ], '203.0.113.9', 'Mozilla-Test');

    expect($approval->ip)->toBe('203.0.113.9');
    expect($approval->pic_name)->toBe('Siti');
    expect($approval->snapshot['spec']['reka_spec_version'])->toBe('1.0');
    expect($approval->snapshot['draft_hash'])->not->toBeNull();
});

it('spec.json has the exact top-level schema keys (§14.2)', function () {
    [$project] = readyWithDraft();
    $spec = app(SpecBuilder::class)->build($project);

    expect(array_keys($spec))->toBe([
        'reka_spec_version', 'generated_at', 'approval', 'meta', 'mosque', 'design',
        'pages', 'content', 'features', 'assets', 'references', 'technical', 'notes', 'ai_flags',
    ]);
});

it('sanity-seed.ndjson is valid line-by-line with known types (§14.4)', function () {
    [$project] = readyWithDraft();
    $spec = app(SpecBuilder::class)->build($project);
    $ndjson = app(SanitySeedBuilder::class)->build($spec);

    $known = ['siteSettings', 'service', 'facility', 'committee', 'quranClass', 'weeklyKuliahSlot', 'faq', 'announcement', 'historyArticle'];
    $lines = array_filter(explode("\n", trim($ndjson)));
    expect($lines)->not->toBeEmpty();

    foreach ($lines as $line) {
        $doc = json_decode($line, true);
        expect(json_last_error())->toBe(JSON_ERROR_NONE);
        expect($doc['_type'])->toBeIn($known);
        expect($doc)->toHaveKey('_id');
    }
});

it('handover ZIP contains all six artifacts (§14.1)', function () {
    [$project, $token, $gen] = readyWithDraft();
    app(ApprovalService::class)->approve($project, $gen, [
        'pic_name' => 'Ali', 'pic_position' => 'SU', 'pic_phone' => '0123456789',
    ], '203.0.113.5', 'PHPUnit');

    $export = app(HandoverExporter::class)->export($project);

    $zip = new ZipArchive;
    $zip->open(Storage::disk('local')->path($export->zip_path));
    $names = [];
    for ($i = 0; $i < $zip->numFiles; $i++) {
        $names[] = $zip->getNameIndex($i);
    }
    $zip->close();

    expect($names)->toContain('spec.json', 'build-brief.md', 'content/sanity-seed.ndjson', 'draft/approved-draft.html', 'README-HANDOVER.md');
    expect(collect($names)->contains(fn ($n) => str_starts_with($n, 'assets/')))->toBeTrue();
});

it('build-brief contains real values with no leftover placeholders (§14.3)', function () {
    [$project] = readyWithDraft();
    $spec = app(SpecBuilder::class)->build($project);
    $brief = view('handover::build-brief', ['spec' => $spec])->render();

    expect($brief)->toContain('Masjid Ujian');
    expect($brief)->toContain('WLY01');
    expect($brief)->not->toContain('{{');
    expect($brief)->not->toContain('}}');
});
