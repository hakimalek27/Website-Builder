<?php

namespace App\Livewire\Pic;

use App\Enums\GenerationStatus;
use App\Enums\GenerationType;
use App\Exceptions\GateException;
use App\Models\Invitation;
use App\Models\Project;
use App\Services\CompletenessService;
use App\Services\DraftGenerationService;
use Livewire\Attributes\Computed;
use Livewire\Component;

/**
 * §5.2 P4 — Hab penjanaan. Kad kuota, cooldown, butang dengan sebab dinyahaktif,
 * progres 4 peringkat (poll 3s), senarai versi.
 */
class JanaHub extends Component
{
    public string $token;

    public function mount(string $token): void
    {
        $this->token = $token;
    }

    protected function resolveProject(): Project
    {
        $invitation = Invitation::query()
            ->where('token_hash', Invitation::hashToken($this->token))
            ->whereNull('revoked_at')
            ->where('expires_at', '>', now())
            ->firstOrFail();

        return $invitation->project;
    }

    #[Computed]
    public function project(): Project
    {
        return $this->resolveProject();
    }

    #[Computed]
    public function activeGeneration()
    {
        return $this->project->generations()
            ->whereIn('status', [GenerationStatus::Queued, GenerationStatus::Processing])
            ->latest()->first();
    }

    #[Computed]
    public function generations()
    {
        return $this->project->generations()->latest()->take(10)->get();
    }

    /** Sebab butang jana dinyahaktif (null = boleh jana). */
    #[Computed]
    public function disabledReason(): ?string
    {
        $project = $this->project;

        if (! in_array($project->status->value, ['submitted', 'draft_ready'], true)) {
            return 'Sila hantar borang dahulu.';
        }
        if ($this->activeGeneration) {
            return 'Penjanaan sedang berjalan.';
        }
        if ($project->quota_ai_used >= $project->quota_ai_total) {
            return 'Kuota AI telah habis. Hubungi admin untuk top-up.';
        }
        if (! app(CompletenessService::class)->canGenerate($project)) {
            return 'Logo atau imej hero belum lengkap.';
        }

        return null;
    }

    public function generate(): void
    {
        try {
            app(DraftGenerationService::class)->request($this->project, GenerationType::Initial, 'pic', picBaseUrl: url('/b/'.$this->token));
            unset($this->activeGeneration, $this->generations);
        } catch (GateException $e) {
            $this->addError('gate', $e->getMessage());
        }
    }

    public function render()
    {
        // Label progres ikut saluran generation aktif (§Fasa 13).
        $isHtml = ($this->activeGeneration?->input_snapshot['pipeline'] ?? null) === 'html';

        return view('livewire.pic.jana-hub', [
            'progressSteps' => $isHtml ? trans('reka.progress_steps_html') : trans('reka.progress_steps'),
        ]);
    }
}
