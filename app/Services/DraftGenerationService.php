<?php

namespace App\Services;

use App\Enums\GenerationStatus;
use App\Enums\GenerationType;
use App\Enums\ProjectStatus;
use App\Exceptions\GateException;
use App\Jobs\GenerateDraftJob;
use App\Models\AiProvider;
use App\Models\AuditLog;
use App\Models\Generation;
use App\Models\Project;
use App\Models\Setting;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * §8.6 langkah 1 — TX lock projek → semak gate/kuota/cooldown/kunci/siling →
 * cipta generations{queued} → dispatch GenerateDraftJob.
 *
 * Kuota TIDAK ditolak di sini (hanya SELEPAS berjaya, dalam Job).
 */
class DraftGenerationService
{
    public function __construct(private CompletenessService $completeness) {}

    private const DAILY_CEILING = 10; // §11.2

    /**
     * @param  array<string,mixed>|null  $tweak
     *
     * @throws GateException
     */
    public function request(Project $project, GenerationType $type, string $createdBy = 'pic', ?array $tweak = null, ?string $picBaseUrl = null): Generation
    {
        $generation = DB::transaction(function () use ($project, $type, $createdBy) {
            /** @var Project $locked */
            $locked = Project::query()->whereKey($project->id)->lockForUpdate()->firstOrFail();

            // Status ≥ submitted (§6.12 gate Jana).
            if (! in_array($locked->status, [ProjectStatus::Submitted, ProjectStatus::DraftReady], true)) {
                throw new GateException('Projek belum dihantar.');
            }

            // Gate logo/hero.
            if ($type->usesAiQuota() && ! $this->completeness->canGenerate($locked)) {
                throw new GateException('Logo atau imej hero belum lengkap.');
            }

            // KUNCI: tiada generation aktif (§4.3).
            if ($locked->generations()->whereIn('status', [GenerationStatus::Queued, GenerationStatus::Processing])->exists()) {
                throw new GateException('Penjanaan sedang berjalan.');
            }

            // Siling harian 10/projek (§11.2).
            if ($locked->generations()->where('created_at', '>=', now()->startOfDay())->count() >= self::DAILY_CEILING) {
                throw new GateException('Had harian penjanaan dicapai.');
            }

            if ($type->usesAiQuota()) {
                // Kuota AI.
                if ($locked->quota_ai_used >= $locked->quota_ai_total) {
                    throw new GateException('Kuota AI telah habis.');
                }
                // Cooldown selepas jana AI terakhir.
                $this->assertCooldown($locked);
            }

            $generation = $locked->generations()->create([
                'ai_provider_id' => AiProvider::default()?->id,
                'type' => $type,
                'status' => GenerationStatus::Queued,
                'created_by' => $createdBy,
            ]);

            AuditLog::record($createdBy, null, 'generation.requested', $generation, ['type' => $type->value]);

            return $generation;
        });

        // Dispatch SELEPAS commit (elak job berjalan dalam transaksi kunci).
        GenerateDraftJob::dispatch($generation->id, $tweak, $picBaseUrl)->onQueue('ai');

        return $generation;
    }

    private function assertCooldown(Project $project): void
    {
        $cooldownMin = (int) (Setting::get('gen_cooldown_minutes') ?? 5);

        $lastAi = $project->generations()
            ->whereIn('type', [GenerationType::Initial, GenerationType::ContentTweak])
            ->whereNotNull('finished_at')
            ->latest('finished_at')
            ->first();

        if ($lastAi !== null) {
            $readyAt = Carbon::parse($lastAi->finished_at)->addMinutes($cooldownMin);
            if ($readyAt->isFuture()) {
                throw new GateException('Sila tunggu sebelum menjana semula (cooldown).');
            }
        }
    }
}
