<?php

namespace App\Support;

/**
 * Penyamaran PII untuk log (§11.3). Log TIDAK PERNAH mengandungi telefon/emel/
 * token penuh (cth 0195•••294).
 */
class Mask
{
    public static function phone(?string $value): string
    {
        $value = (string) $value;
        $len = strlen($value);
        if ($len <= 7) {
            return str_repeat('•', max($len, 1));
        }

        return substr($value, 0, 4).'•••'.substr($value, -3);
    }

    public static function email(?string $value): string
    {
        $value = (string) $value;
        if (! str_contains($value, '@')) {
            return self::phone($value);
        }
        [$user, $domain] = explode('@', $value, 2);

        return substr($user, 0, 1).'•••@'.$domain;
    }

    public static function token(?string $value): string
    {
        $value = (string) $value;

        return $value === '' ? '' : substr($value, 0, 6).'…';
    }
}
