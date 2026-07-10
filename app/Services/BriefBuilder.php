<?php

namespace App\Services;

use App\Models\Project;
use App\Support\ProjectDataPresenter;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Str;

/**
 * Fasa 12 W3 — brief Markdown LENGKAP (semua butiran PIC + nota + sejarah jana) untuk
 * admin/AI membina laman pengeluaran. Guna semula SpecBuilder (bentuk spec.json tidak
 * berubah) + ProjectDataPresenter (verbatim, tidak bermask — dokumen dalaman).
 */
class BriefBuilder
{
    public function __construct(private SpecBuilder $specBuilder) {}

    public function markdown(Project $project): string
    {
        $generations = $project->generations()->latest()->get();
        // Prompt jurutera terkini (saluran HTML §Fasa 13) — untuk admin bina laman pengeluaran.
        $engineeredPrompt = $generations
            ->map(fn ($g) => $g->input_snapshot['engineered_prompt'] ?? null)
            ->filter()->first();

        return View::make('brief::full-brief', [
            'project' => $project,
            'spec' => $this->specBuilder->build($project, $project->approval),
            'blocks' => ProjectDataPresenter::all($project),   // penuh, tidak bermask
            'notes' => $project->notes()->oldest()->get(),
            'generations' => $generations,
            'tweaks' => $project->tweakRequests()->oldest()->get(),
            'engineeredPrompt' => $engineeredPrompt,
        ])->render();
    }

    public function fileName(Project $project): string
    {
        $slug = Str::slug($project->short_name ?: $project->mosque_name) ?: 'projek';

        return "brief-{$slug}-".now()->format('Ymd').'.md';
    }
}
