<?php

namespace App\Support;

/**
 * Nada penulisan (§6 L2 / §8.3) — SATU sumber: label wizard, arahan prompt AI, ayat contoh pratonton.
 */
class Moods
{
    private const DEFAULT = 'tenang_khusyuk';

    /** @var array<string, array{label:string, prompt:string, sample:string}> */
    private const MOODS = [
        'tenang_khusyuk' => [
            'label' => 'Tenang & khusyuk',
            'prompt' => 'tenang, khusyuk, merendah',
            'sample' => 'Memakmurkan rumah Allah dengan penuh ketenangan dan keberkatan.',
        ],
        'mesra_keluarga' => [
            'label' => 'Mesra keluarga',
            'prompt' => 'mesra, hangat, komuniti',
            'sample' => 'Selamat datang ke keluarga besar kami — ada tempat untuk semua.',
        ],
        'megah_berwibawa' => [
            'label' => 'Megah & berwibawa',
            'prompt' => 'formal, berwibawa, meyakinkan',
            'sample' => 'Sebuah institusi yang teguh, berwibawa, dan dipercayai masyarakat.',
        ],
        'profesional_ringkas' => [
            'label' => 'Profesional & ringkas',
            'prompt' => 'profesional, ringkas, terus',
            'sample' => 'Maklumat lengkap. Urusan mudah. Sentiasa telus.',
        ],
        'bersemangat_muda' => [
            'label' => 'Bersemangat & muda',
            'prompt' => 'bersemangat, muda, positif',
            'sample' => 'Jom sama-sama gerakkan program komuniti kita!',
        ],
    ];

    /** @return array<int, string> */
    public static function keys(): array
    {
        return array_keys(self::MOODS);
    }

    /** @return array<string, string> key => label (radio wizard). */
    public static function options(): array
    {
        return array_map(fn ($m) => $m['label'], self::MOODS);
    }

    /** Arahan nada untuk {{MOOD}} dalam prompt AI. */
    public static function prompt(string $key): string
    {
        return (self::MOODS[$key] ?? self::MOODS[self::DEFAULT])['prompt'];
    }

    /** Ayat contoh untuk pratonton wizard. */
    public static function sample(string $key): string
    {
        return (self::MOODS[$key] ?? self::MOODS[self::DEFAULT])['sample'];
    }
}
