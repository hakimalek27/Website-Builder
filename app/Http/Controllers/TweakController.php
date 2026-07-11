<?php

namespace App\Http\Controllers;

use App\Enums\GenerationStatus;
use App\Enums\GenerationType;
use App\Exceptions\GateException;
use App\Models\AuditLog;
use App\Models\Generation;
use App\Models\Project;
use App\Models\TweakRequest;
use App\Services\DesignRerenderService;
use App\Services\DraftGenerationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

// §5.2 P7/P8 — tweak reka bentuk (percuma) & tweak kandungan (AI).
class TweakController extends Controller
{
    public function reka(Request $request): View|RedirectResponse
    {
        if ($r = $this->guardTemplateMode($request)) {
            return $r;
        }

        return view('pic.tweak-reka', ['token' => $request->route('token')]);
    }

    public function rekaRender(Request $request, DesignRerenderService $service): RedirectResponse
    {
        if ($r = $this->guardTemplateMode($request)) {
            return $r;
        }

        $project = $request->attributes->get('project');
        try {
            $generation = $service->rerender($project, 'pic');

            return redirect()->route('pic.draf', ['token' => $request->route('token'), 'generation' => $generation->id]);
        } catch (GateException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function kandungan(Request $request): View|RedirectResponse
    {
        if ($r = $this->guardTemplateMode($request)) {
            return $r;
        }

        $project = $request->attributes->get('project');

        return view('pic.tweak-kandungan', [
            'token' => $request->route('token'),
            'isHtml' => ($this->lastDraft($project)?->input_snapshot['pipeline'] ?? null) === 'html',
        ]);
    }

    public function kandunganSubmit(Request $request, DraftGenerationService $service): RedirectResponse
    {
        if ($r = $this->guardTemplateMode($request)) {
            return $r;
        }

        $project = $request->attributes->get('project');

        $data = $request->validate([
            'categories' => ['array'],
            'categories.*' => ['string'],
            'message' => ['required', 'string', 'max:600'],
        ]);

        $last = $this->lastDraft($project);
        $isHtml = ($last?->input_snapshot['pipeline'] ?? null) === 'html';

        // Saluran html: hantar base_generation_id (job baca HTML dari rendered_path — payload kecil).
        // Saluran shell (legasi): hantar output_json semasa.
        $payload = $isHtml
            ? ['categories' => $data['categories'] ?? [], 'message' => $data['message'], 'base_generation_id' => $last?->id]
            : ['categories' => $data['categories'] ?? [], 'message' => $data['message'], 'current_json' => $last?->output_json ?? []];

        try {
            $generation = $service->request($project, GenerationType::ContentTweak, 'pic', $payload,
                picBaseUrl: url('/b/'.$request->route('token')));

            TweakRequest::create([
                'project_id' => $project->id,
                'base_generation_id' => $last?->id ?? $generation->id,
                'categories' => $data['categories'] ?? [],
                'message' => $data['message'],
                'result_generation_id' => $generation->id,
            ]);

            AuditLog::record('pic', null, 'tweak.requested', $generation, ['categories' => $data['categories'] ?? []]);

            return redirect()->route('pic.jana', ['token' => $request->route('token')]);
        } catch (GateException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /** §Fasa 16 — mod templat tiada tweak AI/reka; alih PIC ke Status. */
    private function guardTemplateMode(Request $request): ?RedirectResponse
    {
        if (DraftGenerationService::pipelineMode() === 'template') {
            return redirect()->route('pic.status', ['token' => $request->route('token')])
                ->with('info', 'Mod templat aktif — pasukan REKA sedang membina laman anda secara manual.');
        }

        return null;
    }

    /** Draf berjaya terkini (mana-mana saluran — ada rendered_path). */
    private function lastDraft(Project $project): ?Generation
    {
        return $project->generations()
            ->where('status', GenerationStatus::Succeeded)
            ->whereNotNull('rendered_path')
            ->latest()->first();
    }
}
