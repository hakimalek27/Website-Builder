<?php

namespace App\Http\Controllers;

use App\Enums\GenerationStatus;
use App\Enums\GenerationType;
use App\Exceptions\GateException;
use App\Models\AuditLog;
use App\Models\TweakRequest;
use App\Services\DesignRerenderService;
use App\Services\DraftGenerationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

// §5.2 P7/P8 — tweak reka bentuk (percuma) & tweak kandungan (AI).
class TweakController extends Controller
{
    public function reka(Request $request): View
    {
        return view('pic.tweak-reka', ['token' => $request->route('token')]);
    }

    public function rekaRender(Request $request, DesignRerenderService $service): RedirectResponse
    {
        $project = $request->attributes->get('project');
        try {
            $generation = $service->rerender($project, 'pic');

            return redirect()->route('pic.draf', ['token' => $request->route('token'), 'generation' => $generation->id]);
        } catch (GateException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function kandungan(Request $request): View
    {
        return view('pic.tweak-kandungan', ['token' => $request->route('token')]);
    }

    public function kandunganSubmit(Request $request, DraftGenerationService $service): RedirectResponse
    {
        $project = $request->attributes->get('project');

        $data = $request->validate([
            'categories' => ['array'],
            'categories.*' => ['string'],
            'message' => ['required', 'string', 'max:600'],
        ]);

        $last = $project->generations()
            ->where('status', GenerationStatus::Succeeded)->whereNotNull('output_json')->latest()->first();

        try {
            $generation = $service->request($project, GenerationType::ContentTweak, 'pic', [
                'categories' => $data['categories'] ?? [],
                'message' => $data['message'],
                'current_json' => $last?->output_json ?? [],
            ]);

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
}
