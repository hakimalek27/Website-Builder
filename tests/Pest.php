<?php

use App\Enums\AiDriver;
use App\Enums\ProjectStatus;
use App\Enums\Tier;
use App\Models\AiProvider;
use App\Models\Invitation;
use App\Models\Project;
use App\Models\ProjectSection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "pest()" function to bind different classes or traits.
|
*/

pest()->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    ->in('Feature');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/

/**
 * Cipta sesi PIC ujian: Project + Invitation + token plaintext.
 *
 * @return array{0: Project, 1: string}
 */
function picSession(array $projectAttrs = []): array
{
    $token = Invitation::generateToken();
    $project = Project::factory()->create($projectAttrs);
    Invitation::factory()->for($project)->withToken($token)->create();

    return [$project, $token];
}

/** Hidupkan senarai page_key untuk projek (project_pages). */
function enablePages(Project $project, array $pageKeys): void
{
    $sort = 0;
    foreach ($pageKeys as $key) {
        $project->pages()->updateOrCreate(['page_key' => $key], ['enabled' => true, 'sort' => $sort++]);
    }
}

/** Kandungan draf sah (4 kunci teras) untuk fixture AI. */
function validContent(): array
{
    return [
        'meta' => ['title' => 'Masjid Ujian', 'description' => 'Laman rasmi Masjid Ujian.'],
        'hero' => ['eyebrow' => 'Selamat Datang', 'headline' => 'Masjid Ujian', 'subheadline' => 'Memakmurkan masjid bersama komuniti.', 'cta_primary_label' => 'Infaq', 'cta_secondary_label' => 'Hubungi'],
        'about' => ['heading' => 'Tentang Kami', 'paragraphs' => ['Masjid ini berkhidmat untuk komuniti setempat.'], 'stats' => [['label' => 'Ditubuhkan', 'value' => '1987']]],
        'footer_description' => 'Masjid Ujian — memakmurkan syiar Islam.',
    ];
}

/** Http::fake respons Anthropic dengan kandungan diberi. */
function fakeAnthropic(array $content, int $status = 200): void
{
    Http::fake([
        '*api.anthropic.com*' => Http::response([
            'content' => [['type' => 'text', 'text' => json_encode($content, JSON_UNESCAPED_UNICODE)]],
            'usage' => ['input_tokens' => 1200, 'output_tokens' => 800],
        ], $status),
    ]);
}

// --- Saluran HTML dua-peringkat (§Fasa 13) ---

/** Dua penyedia berbeza base_url: jurutera prompt (engineer.test) + default GLM (glm.test). */
function htmlProviders(): void
{
    AiProvider::factory()->create([
        'name' => 'GPT Jurutera', 'driver' => AiDriver::OpenAiCompatible, 'base_url' => 'https://engineer.test/v1',
        'model' => 'gpt-5.5', 'is_prompt_engineer' => true,
        'meta' => ['rate_in_per_mtok' => 5.0, 'rate_out_per_mtok' => 30.0, 'currency' => 'USD'],
    ]);
    AiProvider::factory()->create([
        'name' => 'GLM Draf', 'driver' => AiDriver::OpenAiCompatible, 'base_url' => 'https://glm.test/v1',
        'model' => 'glm-5.2', 'is_default' => true,
        'meta' => ['rate_in_per_mtok' => 1.40, 'rate_out_per_mtok' => 4.40, 'currency' => 'USD'],
    ]);
}

/** Projek surau siap-hantar (canGenerate) + PIC phone/email untuk saluran HTML. @return array{0: Project, 1: string} */
function htmlReadyProject(): array
{
    [$project, $token] = picSession(['status' => ProjectStatus::Submitted, 'tier' => Tier::SurauRingkas]);
    enablePages($project, ['utama', 'hubungi']);
    ProjectSection::create(['project_id' => $project->id, 'section_key' => 'step_1', 'data' => [
        'official_name' => 'Masjid Ujian', 'city' => 'KL', 'state' => 'W.P. Kuala Lumpur',
        'phone_primary' => '0341491818', 'email' => 'surau@test.my', 'logo_status' => 'teks_sahaja',
    ]]);
    ProjectSection::create(['project_id' => $project->id, 'section_key' => 'step_2', 'data' => ['mood' => 'tenang_khusyuk']]);
    ProjectSection::create(['project_id' => $project->id, 'section_key' => 'step_6', 'data' => ['hero_mode' => 'stok_sementara']]);
    $project->invitation()->update(['pic_phone' => '60123456789', 'pic_email' => 'pic@test.my']);

    return [$project, $token];
}

/** HTML draf sah dengan token placeholder. */
function validHtmlBody(): string
{
    return '<!DOCTYPE html><html lang="ms"><head><meta charset="utf-8"><title>Masjid Ujian</title>'
        .'<link href="https://fonts.googleapis.com/css2?family=Inter&display=swap" rel="stylesheet">'
        .'<style>body{font-family:Inter}</style></head>'
        .'<body><header>Masjid Ujian</header><section id="hero"><h1>Selamat Datang</h1></section>'
        .'<section id="hubungi">[[CONTACT_STRIP]]</section></body></html>';
}

/** Http::fake dua-peringkat: engineer.test (prompt), glm.test (HTML), gw.test (gateway WA). */
function fakeTwoStage(string $html, int $glmStatus = 200, string $finishReason = 'stop'): void
{
    Http::fake([
        'engineer.test/*' => Http::response(['choices' => [['message' => ['content' => 'PROMPT: bina draf HTML Masjid Ujian, warna hijau, letak [[CONTACT_STRIP]].'], 'finish_reason' => 'stop']], 'usage' => ['prompt_tokens' => 4000, 'completion_tokens' => 2000]]),
        'glm.test/*' => Http::response(['choices' => [['message' => ['content' => $html], 'finish_reason' => $finishReason]], 'usage' => ['prompt_tokens' => 3000, 'completion_tokens' => 20000]], $glmStatus),
        'gw.test/*' => Http::response(['success' => true]),
    ]);
}
