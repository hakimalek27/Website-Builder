<?php

namespace App\Jobs;

use App\Enums\GenerationStatus;
use App\Enums\ProjectStatus;
use App\Mail\GenerationFailedMail;
use App\Models\AiProvider;
use App\Models\AuditLog;
use App\Models\Generation;
use App\Services\Ai\AiClientFactory;
use App\Services\Ai\AiException;
use App\Services\Ai\DraftContentValidator;
use App\Services\Ai\DraftValidationException;
use App\Services\Ai\PromptBuilder;
use App\Services\DraftRenderer;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

/**
 * §8.6 — TUJUH langkah. $tries=1 (retry MANUAL dalam handle 30s/90s).
 * Kuota AI ditolak HANYA selepas berjaya. Gagal muktamad → refund (tidak disentuh) + mail admin.
 */
class GenerateDraftJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 1;

    public int $timeout = 300;

    /** @param array<string,mixed>|null $tweak */
    public function __construct(public string $generationId, public ?array $tweak = null) {}

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

        // Langkah 2: processing, bina prompt.
        $generation->update(['status' => GenerationStatus::Processing, 'progress_step' => 1, 'started_at' => now()]);

        $built = $promptBuilder->build($project, $generation->type->value, $this->tweak);
        $generation->update([
            'progress_step' => 2,
            'input_snapshot' => [
                'system' => $built['system'],
                'user' => $built['user'],
                'requested_keys' => $built['requested_keys'],
                'service_keys' => $built['service_keys'],
            ],
        ]);

        $client = $factory->for($provider);
        $delays = [0, 30, 90]; // percubaan 1..3
        $lastError = 'Tidak diketahui';

        for ($attempt = 1; $attempt <= 3; $attempt++) {
            $generation->update(['attempt' => $attempt, 'progress_step' => 2]);

            if ($attempt > 1) {
                $this->pauseBetweenRetries($delays[$attempt - 1]);
            }

            try {
                $result = $client->complete($built['system'], $built['user'], $provider);

                // Langkah 4: validasi (gagal = gagal percubaan).
                $generation->update(['progress_step' => 3]);
                $content = $validator->validate($result->content, $built['requested_keys'], $built['service_keys']);

                // Langkah 5: render + simpan.
                $generation->update(['progress_step' => 4]);
                $version = $project->generations()->where('status', GenerationStatus::Succeeded)->count() + 1;
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

                // Langkah 6: kuota HANYA selepas berjaya.
                if ($generation->type->usesAiQuota()) {
                    $project->increment('quota_ai_used');
                }

                if (in_array($project->status, [ProjectStatus::Submitted, ProjectStatus::DraftReady], true)) {
                    $project->transitionTo(ProjectStatus::DraftReady, 'system');
                }

                AuditLog::record('system', null, 'generation.succeeded', $generation, [
                    'tokens_in' => $result->tokensIn, 'tokens_out' => $result->tokensOut,
                ]);

                return;
            } catch (DraftValidationException|AiException $e) {
                $lastError = $e->getMessage();
            }
        }

        // Langkah 7: semua percubaan gagal.
        $this->fail($generation, $lastError);
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

        $adminEmail = config('reka.admin_notify_email');
        if (filled($adminEmail)) {
            Mail::to($adminEmail)->queue(new GenerationFailedMail($generation));
        }
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
