<?php

namespace App\Services;

use App\Enums\GenerationStatus;
use App\Enums\GenerationType;
use App\Exceptions\GateException;
use App\Models\AuditLog;
use App\Models\Generation;
use App\Models\Project;
use App\Models\Setting;

/**
 * §8.7 / P7 — Tweak reka bentuk: render SEMULA draf dengan tokens baharu.
 * SIFAR panggilan AI, sifar kuota AI. quota_design_used ≤ 5.
 */
class DesignRerenderService
{
    public function __construct(private DraftRenderer $renderer) {}

    public function rerender(Project $project, string $createdBy = 'pic'): Generation
    {
        if ($project->isFrozen()) {
            throw new GateException('Draf telah diluluskan — baca-sahaja.');
        }

        // Saluran HTML (§Fasa 13) tiada output_json untuk render semula — perubahan reka bentuk
        // dibuat melalui Tweak Kandungan (AI) yang mengubah HTML terus.
        $latestDraft = $project->generations()
            ->where('status', GenerationStatus::Succeeded)
            ->whereNotNull('rendered_path')
            ->latest()->first();
        if ($latestDraft && ($latestDraft->input_snapshot['pipeline'] ?? null) === 'html') {
            throw new GateException('Tweak reka bentuk tidak tersedia untuk draf HTML — guna Tweak Kandungan (AI).');
        }

        $cap = (int) (Setting::get('default_design_quota') ?? 5);
        if ($project->quota_design_used >= $cap) {
            throw new GateException("Kuota render reka bentuk ({$cap}) telah habis.");
        }

        $last = $project->generations()
            ->where('status', GenerationStatus::Succeeded)
            ->whereNotNull('output_json')
            ->latest()
            ->first();

        if ($last === null) {
            throw new GateException('Tiada draf sedia untuk di-render semula.');
        }

        $version = $project->generations()->where('status', GenerationStatus::Succeeded)->count() + 1;

        $generation = $project->generations()->create([
            'type' => GenerationType::DesignRender,
            'status' => GenerationStatus::Succeeded,
            'progress_step' => 4,
            'output_json' => $last->output_json,   // guna kandungan sedia ada
            'created_by' => $createdBy,
            'started_at' => now(),
            'finished_at' => now(),
        ]);

        $path = $this->renderer->renderAndStore($project, $generation, $last->output_json, $version);
        $generation->update(['rendered_path' => $path]);

        $project->increment('quota_design_used');

        AuditLog::record($createdBy, null, 'generation.design_render', $generation);

        return $generation;
    }
}
