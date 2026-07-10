<?php

namespace App\Support;

/**
 * §12.7 — buang PII kasar daripada teks bebas (nota PIC) sebelum dihantar ke AI.
 * Emel & larian digit panjang (telefon/akaun/IC) → placeholder. Tahun/harga pendek kekal.
 */
final class PiiScrubber
{
    public static function scrub(string $text): string
    {
        // Emel.
        $text = preg_replace('/[A-Za-z0-9._%+\-]+@[A-Za-z0-9.\-]+\.[A-Za-z]{2,}/u', '[emel dibuang]', $text) ?? $text;

        // Larian digit ≥8 (benar satu ruang/sengkang antara digit) — telefon, akaun, IC.
        $text = preg_replace('/\d(?:[\s\-]?\d){7,}/', '[nombor dibuang]', $text) ?? $text;

        return $text;
    }
}
