<?php

namespace App\Http\Middleware;

use App\Models\Invitation;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

/**
 * Resolusi token PIC (§5.2 middleware, §11.1).
 *
 * - hash SHA-256 token URL → padan invitations (belum revoked, belum luput).
 * - Gagal: SATU halaman ralat mesra generik (JANGAN bezakan sebab — jangan bocor
 *   sama ada token wujud/luput/revoked) + rate limit resolusi gagal 10/min/IP → 429.
 * - Berjaya: kemas kini opened_at (kali pertama), last_active_at, opens_count++;
 *   kongsi $project & $invitation ke view + request attributes.
 */
class ResolveInvitation
{
    private const MAX_FAILURES = 10;

    public function handle(Request $request, Closure $next): Response
    {
        $token = (string) $request->route('token');
        $hash = Invitation::hashToken($token);

        $invitation = Invitation::query()
            ->where('token_hash', $hash)
            ->whereNull('revoked_at')
            ->where('expires_at', '>', now())
            ->first();

        if ($invitation === null) {
            return $this->handleFailure($request);
        }

        // Kemas kini jejak akses.
        $invitation->forceFill([
            'opened_at' => $invitation->opened_at ?? now(),
            'last_active_at' => now(),
            'opens_count' => $invitation->opens_count + 1,
        ])->save();

        $project = $invitation->project;

        // Kongsi ke view & request.
        $request->attributes->set('invitation', $invitation);
        $request->attributes->set('project', $project);
        view()->share('invitation', $invitation);
        view()->share('project', $project);
        view()->share('token', $token);

        return $next($request);
    }

    private function handleFailure(Request $request): Response
    {
        $key = 'invite-fail:'.$request->ip();

        $attempts = RateLimiter::hit($key, 60);

        if ($attempts > self::MAX_FAILURES) {
            abort(429, 'Terlalu banyak percubaan. Sila cuba lagi sebentar nanti.');
        }

        // Mesej generik — tidak membezakan sebab (§5.2).
        return response()->view('pic.invalid', [], 403);
    }
}
