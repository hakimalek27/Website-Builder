<?php

namespace App\Http\Controllers;

use App\Models\Generation;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

// §5.2 P5/P6 — pemapar draf.
class DraftController extends Controller
{
    public function show(Request $request, string $token, Generation $generation): View
    {
        $this->authorizeGeneration($request, $generation);

        return view('pic.draf', [
            'token' => $token,
            'generation' => $generation,
        ]);
    }

    /** P6 — HTML draf mentah dengan CSP ketat + noindex (§5.2 P6, §7.4). */
    public function raw(Request $request, string $token, Generation $generation): Response
    {
        $this->authorizeGeneration($request, $generation);

        $html = ($generation->rendered_path && Storage::disk('local')->exists($generation->rendered_path))
            ? Storage::disk('local')->get($generation->rendered_path)
            : '<!doctype html><title>Draf tidak tersedia</title><p>Draf belum siap.</p>';

        return response($html, 200, [
            'Content-Type' => 'text/html; charset=UTF-8',
            // Pengecualian SecurityHeaders yang ditangguh dari Fasa 0 (§5.2 P6, §7.4).
            'Content-Security-Policy' => "default-src 'none'; img-src 'self' data:; style-src 'unsafe-inline' https://fonts.googleapis.com; font-src https://fonts.gstatic.com; frame-ancestors 'self'",
            'X-Frame-Options' => 'SAMEORIGIN', // benarkan iframe P5 (asal sama)
            'X-Robots-Tag' => 'noindex',
        ]);
    }

    private function authorizeGeneration(Request $request, Generation $generation): void
    {
        $project = $request->attributes->get('project');
        abort_unless($generation->project_id === $project->id, 404);
    }
}
