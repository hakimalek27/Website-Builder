<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Note;
use App\Models\Project;
use App\Services\WizardProgress;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

// §5.2 P1 — Selamat datang / sambung wizard PIC.
class PicController extends Controller
{
    public function home(Request $request, WizardProgress $progress): View
    {
        /** @var Project $project */
        $project = $request->attributes->get('project');

        return view('pic.home', [
            'progress' => $progress->forProject($project),
            'token' => $request->route('token'),
        ]);
    }

    /** §5.2 P11 — simpan nota PIC (thread dua hala). Notifikasi admin ditambah Fasa 9. */
    public function storeNote(Request $request): RedirectResponse
    {
        /** @var Project $project */
        $project = $request->attributes->get('project');

        $data = $request->validate([
            'body' => ['required', 'string', 'max:2000'],
            'kind' => ['nullable', 'in:general,quota_request,build_update'],
        ]);

        $note = Note::create([
            'project_id' => $project->id,
            'author' => 'pic',
            'author_name' => $project->invitation?->pic_name ?? 'PIC',
            'kind' => $data['kind'] ?? 'general',
            'body' => $data['body'],
        ]);

        AuditLog::record('pic', null, 'note.created', $note);

        return back()->with('success', 'Nota dihantar.');
    }
}
