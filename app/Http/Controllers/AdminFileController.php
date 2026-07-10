<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\Generation;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Fasa 12 W2 — hidangkan aset & draf kepada admin (dalam panel, di luar token PIC).
 * Guard sesi web + semakan auth (403 untuk tetamu). noindex + CSP sama seperti DraftController::raw.
 */
class AdminFileController extends Controller
{
    public function asset(Asset $asset): StreamedResponse
    {
        abort_unless(Auth::check(), 403);
        abort_unless(in_array($asset->kind, ['hero', 'logo', 'gallery'], true), 404);
        abort_unless(Storage::disk('local')->exists($asset->path), 404);

        return Storage::disk('local')->response($asset->path, null, [
            'Content-Type' => $asset->mime ?: 'application/octet-stream',
            'X-Robots-Tag' => 'noindex',
            'Cache-Control' => 'private, max-age=300',
        ]);
    }

    public function draft(Generation $generation): Response
    {
        abort_unless(Auth::check(), 403);
        abort_unless($generation->rendered_path && Storage::disk('local')->exists($generation->rendered_path), 404);

        return response(Storage::disk('local')->get($generation->rendered_path), 200, [
            'Content-Type' => 'text/html; charset=UTF-8',
            'Content-Security-Policy' => "default-src 'none'; img-src 'self' data:; style-src 'unsafe-inline' https://fonts.googleapis.com; font-src https://fonts.gstatic.com; frame-ancestors 'self'",
            'X-Frame-Options' => 'SAMEORIGIN',
            'X-Robots-Tag' => 'noindex',
        ]);
    }

    /** Muat turun prompt jurutera (§Fasa 13) yang dijana untuk saluran HTML. */
    public function prompt(Generation $generation): Response
    {
        abort_unless(Auth::check(), 403);
        $prompt = $generation->input_snapshot['engineered_prompt'] ?? null;
        abort_unless(filled($prompt), 404);

        return response($prompt, 200, [
            'Content-Type' => 'text/markdown; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="prompt-'.$generation->id.'.md"',
            'X-Robots-Tag' => 'noindex',
        ]);
    }

    /** Muat turun fail HTML draf sebagai lampiran (§Fasa 13). */
    public function draftDownload(Generation $generation): StreamedResponse
    {
        abort_unless(Auth::check(), 403);
        abort_unless($generation->rendered_path && Storage::disk('local')->exists($generation->rendered_path), 404);

        return Storage::disk('local')->download($generation->rendered_path, 'draf-'.$generation->id.'.html', [
            'X-Robots-Tag' => 'noindex',
        ]);
    }
}
