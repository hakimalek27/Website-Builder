<?php

use App\Enums\AiDriver;
use App\Enums\GenerationType;
use App\Enums\ProjectStatus;
use App\Enums\Tier;
use App\Jobs\GenerateDraftJob;
use App\Livewire\Pic\JanaHub;
use App\Models\AiProvider;
use App\Models\ProjectSection;
use App\Models\Setting;
use App\Services\DraftGenerationService;
use Database\Seeders\DesignPackageSeeder;
use Database\Seeders\VerseLibrarySeeder;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Livewire\Livewire;

// Fasa 12 W1 — deep-link WhatsApp draf-sedia (token dibawa via picBaseUrl; payload disulitkan).

function generableProject(): array
{
    [$project, $token] = picSession(['status' => ProjectStatus::Submitted, 'tier' => Tier::SurauRingkas]);
    enablePages($project, ['utama', 'hubungi']);
    ProjectSection::create(['project_id' => $project->id, 'section_key' => 'step_1', 'data' => [
        'official_name' => 'Surau Ujian', 'city' => 'KL', 'state' => 'W.P. Kuala Lumpur', 'logo_status' => 'teks_sahaja',
    ]]);
    ProjectSection::create(['project_id' => $project->id, 'section_key' => 'step_2', 'data' => ['mood' => 'tenang_khusyuk']]);
    ProjectSection::create(['project_id' => $project->id, 'section_key' => 'step_6', 'data' => ['hero_mode' => 'stok_sementara']]);
    AiProvider::factory()->default()->create(['driver' => AiDriver::Anthropic]);
    $project->invitation->update(['pic_phone' => '0123456789']);

    return [$project, $token];
}

function fakeAiAndWa(array $content): void
{
    Http::fake([
        '*api.anthropic.com*' => Http::response([
            'content' => [['type' => 'text', 'text' => json_encode($content, JSON_UNESCAPED_UNICODE)]],
            'usage' => ['input_tokens' => 100, 'output_tokens' => 50],
        ], 200),
        '*wa.test*' => Http::response(['success' => true], 200),
    ]);
}

beforeEach(function () {
    $this->seed(DesignPackageSeeder::class);
    $this->seed(VerseLibrarySeeder::class);
});

it('threads the PIC base URL from JanaHub into the generation job', function () {
    Queue::fake();
    [, $token] = generableProject();

    Livewire::test(JanaHub::class, ['token' => $token])->call('generate');

    Queue::assertPushed(GenerateDraftJob::class, fn ($job) => $job->picBaseUrl === url("/b/{$token}"));
});

it('embeds a real draft deep-link in the PIC WhatsApp message', function () {
    Setting::put('whatsapp_gateway_url', 'https://wa.test');
    fakeAiAndWa(validContent());
    [$project, $token] = generableProject();

    $gen = app(DraftGenerationService::class)->request($project, GenerationType::Initial, 'pic', picBaseUrl: url("/b/{$token}"));

    Http::assertSent(fn ($req) => str_contains($req->url(), 'wa.test')
        && str_contains((string) ($req['message'] ?? ''), url("/b/{$token}/draf/{$gen->id}")));
});

it('falls back to a menu phrase (no URL) when no picBaseUrl is given', function () {
    Setting::put('whatsapp_gateway_url', 'https://wa.test');
    fakeAiAndWa(validContent());
    [$project, $token] = generableProject();

    app(DraftGenerationService::class)->request($project, GenerationType::Initial, 'pic');

    Http::assertSent(fn ($req) => str_contains($req->url(), 'wa.test')
        && str_contains((string) ($req['message'] ?? ''), 'pautan borang')
        && ! str_contains((string) ($req['message'] ?? ''), '/draf/'));
});
