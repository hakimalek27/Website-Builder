<?php

namespace App\Http\Controllers;

use App\Enums\GenerationType;
use App\Enums\ProjectStatus;
use App\Exceptions\GateException;
use App\Models\AuditLog;
use App\Models\Project;
use App\Services\CompletenessService;
use App\Services\DraftGenerationService;
use App\Services\Notifier;
use App\Support\ProjectDataPresenter;
use App\Support\WizardSteps;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

// §5.2 P3 — Semak & Hantar.
class SemakController extends Controller
{
    public function show(Request $request, CompletenessService $completeness): View
    {
        /** @var Project $project */
        $project = $request->attributes->get('project');

        $sections = $project->sections()->get()
            ->mapWithKeys(fn ($s) => [$s->section_key => $s->data])->all();

        // Mask nombor akaun bank pada paparan (§11.1).
        $bankAccount = data_get($sections, 'step_4.panels.infaq.bank_account');
        $maskedBank = $bankAccount ? '••••'.substr((string) $bankAccount, -4) : null;

        return view('pic.semak', [
            'token' => $request->route('token'),
            'result' => $completeness->compute($project),
            'steps' => WizardSteps::all(),
            'sections' => $sections,
            'stepBlocks' => ProjectDataPresenter::all($project, maskPii: true),
            'maskedBank' => $maskedBank,
            'alreadySubmitted' => in_array($project->status, [
                ProjectStatus::Submitted, ProjectStatus::DraftReady, ProjectStatus::Approved,
            ], true),
        ]);
    }

    public function submit(Request $request, CompletenessService $completeness): RedirectResponse
    {
        /** @var Project $project */
        $project = $request->attributes->get('project');

        if ($project->isFrozen()) {
            return redirect()->route('pic.semak', ['token' => $request->route('token')])
                ->with('error', 'Draf telah diluluskan — tidak boleh diubah.');
        }

        if (! $completeness->canSubmit($project)) {
            return redirect()->route('pic.semak', ['token' => $request->route('token')])
                ->with('error', 'Sila lengkapkan semua medan wajib sebelum menghantar.');
        }

        // Transisi ke submitted (in_progress → submitted).
        if ($project->status === ProjectStatus::InProgress) {
            $project->transitionTo(ProjectStatus::Submitted, 'pic');
        }

        // Notifikasi admin (§13) — mel + WA (draf dijana automatik).
        app(Notifier::class)->submitted($project);

        AuditLog::record('pic', null, 'project.submitted', $project);

        // Auto-jana draf pertama (Fasa 11). Kes logo/hero belum dimuat naik → jana manual kemudian.
        $redirect = redirect()->route('pic.semak', ['token' => $request->route('token')]);
        try {
            app(DraftGenerationService::class)->request($project, GenerationType::Initial, 'pic', picBaseUrl: url('/b/'.$request->route('token')));

            return $redirect->with('success', 'Maklumat dihantar. Draf contoh sedang dijana secara automatik — anda akan dimaklumkan sebaik siap.');
        } catch (GateException $e) {
            return $redirect->with('warning', 'Borang dihantar. '.$e->getMessage().' Jana draf di halaman Jana Draf apabila sedia.');
        }
    }
}
