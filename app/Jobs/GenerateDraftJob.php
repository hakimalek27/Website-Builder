<?php

namespace App\Jobs;

use App\Enums\GenerationStatus;
use App\Enums\GenerationType;
use App\Enums\ProjectStatus;
use App\Models\AiProvider;
use App\Models\AuditLog;
use App\Models\Generation;
use App\Models\Project;
use App\Models\Setting;
use App\Services\Ai\AiClientFactory;
use App\Services\Ai\AiException;
use App\Services\Ai\DraftContentValidator;
use App\Services\Ai\DraftValidationException;
use App\Services\Ai\HtmlDraftValidator;
use App\Services\Ai\HtmlPromptBuilder;
use App\Services\Ai\PromptBuilder;
use App\Services\DraftQaService;
use App\Services\DraftRenderer;
use App\Services\HtmlDraftFinisher;
use App\Services\Notifier;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeEncrypted;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

/**
 * §8.6 — TUJUH langkah. $tries=1 (retry MANUAL dalam handle 30s/90s).
 * Kuota AI ditolak HANYA selepas berjaya. Gagal muktamad → refund (tidak disentuh) + mail admin.
 *
 * §Fasa 13 — dua saluran (input_snapshot['pipeline']): 'shell' (JSON → Blade shell, lama) atau
 * 'html' (Peringkat 1 jurutera prompt → Peringkat 2 jana HTML → HtmlDraftFinisher).
 */
class GenerateDraftJob implements ShouldBeEncrypted, ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 1;

    public int $timeout = 300;

    private const RETRY_DELAYS = [0, 30, 90]; // percubaan 1..3

    /**
     * @param  array<string,mixed>|null  $tweak
     * @param  string|null  $picBaseUrl  URL asas PIC (cth /b/{token}) untuk deep-link WA — payload disulitkan (ShouldBeEncrypted).
     */
    public function __construct(public string $generationId, public ?array $tweak = null, public ?string $picBaseUrl = null) {}

    public function handle(PromptBuilder $promptBuilder, DraftContentValidator $validator, DraftRenderer $renderer, AiClientFactory $factory): void
    {
        /** @var Generation|null $generation */
        $generation = Generation::with('project')->find($this->generationId);
        if ($generation === null || $generation->status !== GenerationStatus::Queued) {
            return; // sudah diproses / hilang
        }

        $project = $generation->project;
        $provider = $generation->aiProvider ?: AiProvider::default();

        if ($provider === null) {
            $this->fail($generation, 'Tiada penyedia AI aktif dikonfigurasi.');

            return;
        }

        $pipeline = $generation->input_snapshot['pipeline'] ?? 'shell';

        if ($pipeline === 'html') {
            $this->handleHtml($generation, $project, $provider, $factory);

            return;
        }

        $this->handleShell($generation, $project, $provider, $promptBuilder, $validator, $renderer, $factory);
    }

    /** Saluran klasik: prompt JSON → validasi → Blade shell deterministik. */
    private function handleShell(Generation $generation, Project $project, AiProvider $provider, PromptBuilder $promptBuilder, DraftContentValidator $validator, DraftRenderer $renderer, AiClientFactory $factory): void
    {
        $generation->update(['status' => GenerationStatus::Processing, 'progress_step' => 1, 'started_at' => now()]);

        $built = $promptBuilder->build($project, $generation->type->value, $this->tweak);
        $this->snapshotMerge($generation, [
            'system' => $built['system'],
            'user' => $built['user'],
            'requested_keys' => $built['requested_keys'],
            'service_keys' => $built['service_keys'],
        ]);
        $generation->update(['progress_step' => 2]);

        $client = $factory->for($provider);
        $lastError = 'Tidak diketahui';

        for ($attempt = 1; $attempt <= 3; $attempt++) {
            $generation->update(['attempt' => $attempt, 'progress_step' => 2]);
            if ($attempt > 1) {
                $this->pauseBetweenRetries(self::RETRY_DELAYS[$attempt - 1]);
            }

            try {
                $result = $client->complete($built['system'], $built['user'], $provider);

                $generation->update(['progress_step' => 3]);
                $content = $validator->validate($result->content, $built['requested_keys'], $built['service_keys']);

                $generation->update(['progress_step' => 4]);
                $version = $this->nextVersion($project);
                $path = $renderer->renderAndStore($project, $generation, $content, $version);

                $generation->update([
                    'status' => GenerationStatus::Succeeded,
                    'output_json' => $content,
                    'rendered_path' => $path,
                    'tokens_in' => $result->tokensIn,
                    'tokens_out' => $result->tokensOut,
                    'cost_estimate' => $this->cost($result->tokensIn, $result->tokensOut, $provider),
                    'finished_at' => now(),
                ]);

                $this->succeedCommon($project, $generation);

                return;
            } catch (DraftValidationException|AiException $e) {
                $lastError = $e->getMessage();
            }
        }

        $this->fail($generation, $lastError);
    }

    /**
     * Saluran HTML (§Fasa 13): Peringkat 1 jurutera prompt (Initial sahaja) → Peringkat 2 jana
     * HTML (retry HANYA peringkat 2 — jimat token P1). Placeholder verbatim disisip HtmlDraftFinisher.
     */
    private function handleHtml(Generation $generation, Project $project, AiProvider $provider, AiClientFactory $factory): void
    {
        $generation->update(['status' => GenerationStatus::Processing, 'progress_step' => 1, 'started_at' => now()]);

        $builder = app(HtmlPromptBuilder::class);
        $tokensIn = 0;
        $tokensOut = 0;
        $costTotal = 0.0;

        // --- PERINGKAT 1 — jana prompt lengkap (Initial). Tweak guna HTML semasa (tiada P1). ---
        if ($generation->type === GenerationType::Initial) {
            $generation->update(['progress_step' => 2]);
            $engineer = AiProvider::promptEngineer();
            if ($engineer === null) {
                $this->fail($generation, 'Penyedia Jurutera Prompt belum diset — sila set di Penyedia AI.');

                return;
            }

            try {
                $req1 = $builder->engineerRequest($project);
                $res1 = $factory->for($engineer)->complete($req1['system'], $req1['user'], $engineer, ['json' => false]);
            } catch (AiException $e) {
                $this->fail($generation, 'Peringkat 1 (jurutera prompt) gagal: '.$e->getMessage());

                return;
            }

            $prompt = trim($res1->content);
            if ($prompt === '') {
                $this->fail($generation, 'Peringkat 1 (jurutera prompt) tidak menghasilkan prompt.');

                return;
            }

            $costP1 = $this->cost($res1->tokensIn, $res1->tokensOut, $engineer);
            $tokensIn += $res1->tokensIn;
            $tokensOut += $res1->tokensOut;
            $costTotal += $costP1;
            $this->snapshotMerge($generation, [
                'engineered_prompt' => $prompt,
                'stage1' => ['source' => 'ai', 'provider' => $engineer->name, 'model' => $engineer->model, 'tokens_in' => $res1->tokensIn, 'tokens_out' => $res1->tokensOut, 'cost' => $costP1, 'finish_reason' => $res1->finishReason],
            ]);
            $req2 = $builder->stage2Request($project, $prompt);
        } else {
            $base = Generation::find($this->tweak['base_generation_id'] ?? null);
            // Guna HTML MENTAH bertoken (tiada PII) — BUKAN draf siap — supaya bank/telefon/nama
            // TIDAK dihantar ke AI semasa tweak (§12.7).
            $rawPath = $base?->input_snapshot['raw_path'] ?? null;
            if ($base === null || blank($rawPath) || ! Storage::disk('local')->exists($rawPath)) {
                $this->fail($generation, 'Draf asas untuk tweak tidak ditemui.');

                return;
            }
            $currentHtml = Storage::disk('local')->get($rawPath);
            $this->snapshotMerge($generation, [
                'stage1' => ['source' => 'tweak', 'base_generation_id' => $base->id],
                'tweak' => ['categories' => $this->tweak['categories'] ?? [], 'message' => $this->tweak['message'] ?? ''],
            ]);
            $req2 = $builder->stage2TweakRequest($project, $currentHtml, $this->tweak ?? []);
        }

        // --- PERINGKAT 2 — jana HTML (retry manual 3×). ---
        $maxTokens = (int) (Setting::get('html_max_tokens') ?? 30000);
        $validator = app(HtmlDraftValidator::class);
        $finisher = app(HtmlDraftFinisher::class);
        $client = $factory->for($provider);
        $lastError = 'Tidak diketahui';

        for ($attempt = 1; $attempt <= 3; $attempt++) {
            $generation->update(['attempt' => $attempt, 'progress_step' => 3]);
            if ($attempt > 1) {
                $this->pauseBetweenRetries(self::RETRY_DELAYS[$attempt - 1]);
            }

            try {
                $res2 = $client->complete($req2['system'], $req2['user'], $provider, ['json' => false, 'max_tokens' => $maxTokens]);
                $tokensIn += $res2->tokensIn;
                $tokensOut += $res2->tokensOut;
                $costTotal += $this->cost($res2->tokensIn, $res2->tokensOut, $provider);

                // Kesan terpotong AWAL (§Fasa 14): finish_reason=length bermakna had token dicapai
                // → jangan bazir masa validasi struktur; jatuh ke retry seperti kegagalan validasi.
                if ($res2->finishReason === 'length') {
                    throw new DraftValidationException('Output HTML terpotong (had token model dicapai) — cuba jana semula.');
                }

                $clean = $validator->validate($res2->content);   // HTML mentah bertoken (tiada PII)

                $generation->update(['progress_step' => 4]);
                $version = $this->nextVersion($project);

                // Simpan HTML mentah bertoken untuk tweak masa depan (elak hantar PII ke AI, §12.7).
                $rawPath = "drafts/{$project->id}/{$generation->id}.raw.html";
                Storage::disk('local')->put($rawPath, $clean);

                // Draf akhir: sisip data verbatim (bank/AJK/hubungi) — render LOKAL sahaja.
                $final = $finisher->finish($project, $clean, $version);
                $path = "drafts/{$project->id}/{$generation->id}.html";
                Storage::disk('local')->put($path, $final);

                // QA auto (§Fasa 14) — WAJIB Throwable-safe: bug QA TIDAK boleh menggagalkan
                // draf yang sudah sah/tersimpan atau membakar kuota. Dipanggil selepas fail disimpan.
                $qa = null;
                try {
                    $qa = app(DraftQaService::class)->analyse($project, $final);
                } catch (Throwable $e) {
                    report($e);
                }

                $generation->update([
                    'status' => GenerationStatus::Succeeded,
                    'output_json' => null,
                    'rendered_path' => $path,
                    'tokens_in' => $tokensIn,
                    'tokens_out' => $tokensOut,
                    'cost_estimate' => round($costTotal, 4),
                    'finished_at' => now(),
                ]);
                $this->snapshotMerge($generation, array_filter([
                    'raw_path' => $rawPath,
                    'stage2' => ['provider' => $provider->name, 'model' => $provider->model, 'tokens_in' => $res2->tokensIn, 'tokens_out' => $res2->tokensOut, 'attempts' => $attempt, 'finish_reason' => $res2->finishReason],
                    'qa' => $qa,
                ], fn ($v) => $v !== null));

                $this->succeedCommon($project, $generation);

                if (! empty($qa['issues'])) {
                    app(Notifier::class)->qaFlagged($generation, $qa['issues']);
                }

                return;
            } catch (DraftValidationException|AiException $e) {
                $lastError = $e->getMessage();
            }
        }

        // Gagal muktamad — rekod kos terbazir (lejar jujur); kuota TIDAK disentuh.
        $generation->update(['tokens_in' => $tokensIn, 'tokens_out' => $tokensOut, 'cost_estimate' => round($costTotal, 4)]);
        $this->fail($generation, $lastError);
    }

    /** Langkah 6 dikongsi: kuota (selepas berjaya) + transisi + audit + notify PIC (deep-link). */
    private function succeedCommon(Project $project, Generation $generation): void
    {
        if ($generation->type->usesAiQuota()) {
            $project->increment('quota_ai_used');
        }

        if (in_array($project->status, [ProjectStatus::Submitted, ProjectStatus::DraftReady], true)) {
            $project->transitionTo(ProjectStatus::DraftReady, 'system');
        }

        AuditLog::record('system', null, 'generation.succeeded', $generation, [
            'tokens_in' => $generation->tokens_in, 'tokens_out' => $generation->tokens_out,
        ]);

        // §8.6 langkah 6 — notifikasi PIC (WA + mail). Deep-link ke draf bila URL asas PIC
        // dihantar oleh pemanggil (token plaintext tidak disimpan, §11.1).
        $link = $this->picBaseUrl
            ? rtrim($this->picBaseUrl, '/')."/draf/{$generation->id}"
            : 'pautan borang anda (menu Jana Draf)';
        app(Notifier::class)->generationSucceeded($project, $generation, $link);
    }

    private function nextVersion(Project $project): int
    {
        return $project->generations()->where('status', GenerationStatus::Succeeded)->count() + 1;
    }

    /** Gabung ke input_snapshot sedia ada (kekalkan 'pipeline' + kunci peringkat lain). */
    private function snapshotMerge(Generation $generation, array $new): void
    {
        $generation->update(['input_snapshot' => array_merge($generation->input_snapshot ?? [], $new)]);
    }

    private function fail(Generation $generation, string $error): void
    {
        $generation->update([
            'status' => GenerationStatus::Failed,
            'error' => $error,
            'finished_at' => now(),
        ]);

        // Kuota TIDAK disentuh (refund = tidak pernah dicaj).
        AuditLog::record('system', null, 'generation.failed', $generation, ['error' => Str::limit($error, 200)]);

        // Notifier: mail + WA admin + NotificationLog (§Fasa 13).
        app(Notifier::class)->generationFailed($generation);
    }

    /** §8.8 — cost = tokensIn×rate_in + tokensOut×rate_out (kadar dari meta; JANGAN hard-code). */
    private function cost(int $tokensIn, int $tokensOut, AiProvider $provider): float
    {
        $rateIn = (float) ($provider->meta['rate_in_per_mtok'] ?? 0);
        $rateOut = (float) ($provider->meta['rate_out_per_mtok'] ?? 0);

        return round(($tokensIn * $rateIn + $tokensOut * $rateOut) / 1_000_000, 4);
    }

    /** Tidur antara percubaan retry — dilangkau dalam ujian. */
    private function pauseBetweenRetries(int $seconds): void
    {
        if ($seconds > 0 && ! app()->runningUnitTests()) {
            sleep($seconds);
        }
    }
}
