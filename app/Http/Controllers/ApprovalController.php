<?php

namespace App\Http\Controllers;

use App\Enums\GenerationStatus;
use App\Exceptions\GateException;
use App\Services\ApprovalService;
use App\Services\Notifier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

// §5.2 P9 — kelulusan draf (snapshot beku).
class ApprovalController extends Controller
{
    public function show(Request $request): View
    {
        $project = $request->attributes->get('project');

        return view('pic.lulus', [
            'token' => $request->route('token'),
            'invitation' => $project->invitation,
            'latestDraft' => $project->generations()->where('status', GenerationStatus::Succeeded)->latest()->first(),
        ]);
    }

    public function store(Request $request, ApprovalService $service): RedirectResponse
    {
        $project = $request->attributes->get('project');

        $data = $request->validate([
            'pic_name' => ['required', 'string', 'max:100'],
            'pic_position' => ['required', 'string', 'max:100'],
            'pic_phone' => ['required', 'string', 'max:20'],
            'consent_pdpa' => ['accepted'],
            'declare_authority' => ['accepted'],
        ]);

        $generation = $project->generations()->where('status', GenerationStatus::Succeeded)->latest()->first();
        if ($generation === null) {
            return back()->with('error', 'Tiada draf untuk diluluskan.');
        }

        try {
            $service->approve($project, $generation, [
                'pic_name' => $data['pic_name'],
                'pic_position' => $data['pic_position'],
                'pic_phone' => $data['pic_phone'],
            ], (string) $request->ip(), (string) $request->userAgent());
        } catch (GateException $e) {
            return back()->with('error', $e->getMessage());
        }

        app(Notifier::class)->approved($project);

        return redirect()->route('pic.status', ['token' => $request->route('token')])
            ->with('success', 'Draf telah diluluskan. Terima kasih!');
    }
}
