<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Security headers untuk SEMUA respons (§11.3).
 *
 * Pengajaran code-review mamkl: header keselamatan hilang → JANGAN ulang.
 *
 * Reka bentuk: setiap header hanya ditetapkan jika belum wujud pada respons.
 * Ini membenarkan route tertentu (cth P6 pemapar draf, Fasa 8) menetapkan
 * CSP / X-Frame-Options tersendiri SEBELUM sampai ke middleware ini —
 * middleware TIDAK akan menindih nilai yang telah ditetapkan controller.
 * Dengan itu pengecualian boleh ditambah nanti tanpa mengubah kelas ini.
 *
 * HSTS TIDAK ditetapkan di sini — ia diuruskan di lapisan nginx (produksi).
 */
class SecurityHeaders
{
    /**
     * CSP asas aplikasi (§11.3) — halaman statik awam.
     * Route draf (P6) akan melonggarkan ini untuk Google Fonts (Fasa 8).
     */
    protected string $baseCsp = "default-src 'self'; img-src 'self' data:; style-src 'self' 'unsafe-inline'; script-src 'self'";

    /**
     * CSP untuk route interaktif (Filament /admin & wizard PIC /b/*).
     *
     * Nota (R1/R2): Livewire + Alpine (dipakai Filament & wizard) memerlukan
     * 'unsafe-eval' (penilaian ungkapan Alpine) + 'unsafe-inline' (skrip config
     * awal Livewire). CSP `script-src 'self'` sahaja akan memecahkan UI ini.
     * Halaman statik awam kekal CSP ketat §11.3.
     */
    protected string $interactiveCsp = "default-src 'self'; img-src 'self' data:; style-src 'self' 'unsafe-inline'; script-src 'self' 'unsafe-eval' 'unsafe-inline'; font-src 'self' data:; connect-src 'self'";

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $this->setIfAbsent($response, 'X-Content-Type-Options', 'nosniff');
        $this->setIfAbsent($response, 'Referrer-Policy', 'strict-origin-when-cross-origin');
        $this->setIfAbsent($response, 'X-Frame-Options', 'DENY');
        $this->setIfAbsent($response, 'Content-Security-Policy', $this->cspFor($request));

        return $response;
    }

    protected function cspFor(Request $request): string
    {
        if ($request->is('admin', 'admin/*') || $request->is('b/*') || $request->is('livewire/*')) {
            return $this->interactiveCsp;
        }

        return $this->baseCsp;
    }

    protected function setIfAbsent(Response $response, string $name, string $value): void
    {
        if (! $response->headers->has($name)) {
            $response->headers->set($name, $value);
        }
    }
}
