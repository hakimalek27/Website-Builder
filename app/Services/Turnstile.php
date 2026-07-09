<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

/**
 * Cloudflare Turnstile — pengesahan anti-bot (§5.1).
 * Env-gated: jika TURNSTILE_SITE_KEY kosong → dilangkau sepenuhnya (sentiasa lulus).
 */
class Turnstile
{
    public static function isEnabled(): bool
    {
        return filled(config('reka.turnstile.site_key')) && filled(config('reka.turnstile.secret_key'));
    }

    /**
     * Sahkan token Turnstile. Jika tidak dikonfigurasi → true (langkau).
     */
    public static function verify(?string $token, ?string $ip = null): bool
    {
        if (! self::isEnabled()) {
            return true;
        }

        if (blank($token)) {
            return false;
        }

        $response = Http::asForm()->post('https://challenges.cloudflare.com/turnstile/v0/siteverify', [
            'secret' => config('reka.turnstile.secret_key'),
            'response' => $token,
            'remoteip' => $ip,
        ]);

        return $response->successful() && $response->json('success') === true;
    }
}
