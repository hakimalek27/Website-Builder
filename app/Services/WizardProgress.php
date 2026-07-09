<?php

namespace App\Services;

use App\Models\Project;
use App\Support\WizardSteps;

/**
 * Kira status setiap langkah wizard untuk P1 (§5.2). Status ringkas berdasarkan
 * kewujudan & kelengkapan project_sections. Skor kelengkapan penuh (gate) =
 * CompletenessService (Fasa 6).
 */
class WizardProgress
{
    /**
     * @return array{steps: array<int, array{index:int,title:string,subtitle:string,status:string}>, completed: int, total: int, percent: int, resume_step: int}
     */
    public function forProject(Project $project): array
    {
        $sections = $project->sections()->get()->keyBy('section_key');

        $steps = [];
        $completed = 0;
        $resume = null;

        foreach (WizardSteps::all() as $step) {
            $key = WizardSteps::sectionKey($step['index']);
            $section = $sections->get($key);

            $status = 'empty';
            if ($section !== null) {
                if ($section->completed_at !== null) {
                    $status = 'complete';
                } elseif (! empty($section->data)) {
                    $status = 'partial';
                }
            }

            if ($status === 'complete') {
                $completed++;
            } elseif ($resume === null) {
                $resume = $step['index'];
            }

            $steps[] = $step + ['status' => $status];
        }

        $total = WizardSteps::count();

        return [
            'steps' => $steps,
            'completed' => $completed,
            'total' => $total,
            'percent' => (int) round(100 * $completed / $total),
            'resume_step' => $resume ?? 0,
        ];
    }
}
