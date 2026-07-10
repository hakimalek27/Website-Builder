<?php

namespace App\Services\Ai;

/**
 * §8.4 — validasi output AI dalam URUTAN spek:
 * (1) strip fence ```json → json_decode strict;
 * (2) reject jika mengandungi aksara Arab (penguatkuasaan mekanikal §9.1);
 * (3) semak kunci wajib wujud & tiada kunci asing;
 * (4) had panjang (potong lembut +10% toleransi, gagal jika >25%);
 * (5) semak services[].key ⊆ kunci input.
 * Gagal mana-mana → DraftValidationException (gagal percubaan penuh, bukan baiki).
 */
class DraftContentValidator
{
    /** Julat aksara Arab & bentuk persembahan (§8.4). */
    private const ARABIC_REGEX = '/[\x{0600}-\x{06FF}\x{0750}-\x{077F}\x{08A0}-\x{08FF}\x{FB50}-\x{FDFF}\x{FE70}-\x{FEFF}]/u';

    /** Had panjang aksara medan (§8.2). */
    private const LIMITS = [
        'meta' => ['title' => 60, 'description' => 160],
        'hero' => ['eyebrow' => 40, 'headline' => 60, 'subheadline' => 140, 'cta_primary_label' => 20, 'cta_secondary_label' => 20],
        'about' => ['heading' => 60, '_items' => ['stats' => ['label' => 20, 'value' => 12]]],
        'services' => ['_each' => ['title' => 40, 'blurb' => 160]],
        'facilities' => ['_each' => ['title' => 40, 'blurb' => 140]],
        'kuliah' => ['heading' => 60, 'intro' => 200],
        'infaq' => ['heading' => 60, 'paragraph' => 240],
        'announcements' => ['_each' => ['title' => 70, 'date_label' => 20, 'excerpt' => 140]],
        'visitor_info' => ['heading' => 60, 'paragraph' => 240],
        // NGO / pertubuhan (Fasa 11) — aditif; dikuatkuasa hanya bila diminta.
        'programs' => ['_each' => ['title' => 40, 'blurb' => 160]],
        // Perenggan NGO 240→1000: GLM-5.2 hasilkan perenggan lebih panjang (Derma/Sukarelawan/
        // Keahlian). schemaFor kekal ≤240 (AI dipandu ringkas); validator kini terima ≤1000.
        // Medan masjid kekal 240 (byte-identik).
        'volunteer' => ['heading' => 60, 'paragraph' => 1000, 'cta_label' => 20],
        'membership' => ['heading' => 60, 'paragraph' => 1000],
        'donate' => ['heading' => 60, 'paragraph' => 1000],
        'footer_description' => 200,
    ];

    /**
     * @param  array<int,string>  $requestedKeys
     * @param  array<int,string>  $serviceKeys
     * @return array<string,mixed> kandungan sah (dipotong lembut)
     *
     * @throws DraftValidationException
     */
    public function validate(string $raw, array $requestedKeys, array $serviceKeys): array
    {
        // (1) Strip fence + decode strict.
        $clean = trim($raw);
        $clean = preg_replace('/^```(?:json)?\s*|\s*```$/m', '', $clean) ?? $clean;
        $clean = trim($clean);

        $content = json_decode($clean, true);
        if (! is_array($content) || json_last_error() !== JSON_ERROR_NONE) {
            throw new DraftValidationException('Output bukan JSON sah.');
        }

        // (2) Reject aksara Arab.
        if (preg_match(self::ARABIC_REGEX, $clean)) {
            throw new DraftValidationException('Output mengandungi aksara Arab (dilarang §9.1).');
        }

        // (3) Kunci wajib wujud & tiada kunci asing.
        $contentKeys = array_keys($content);
        $missing = array_diff($requestedKeys, $contentKeys);
        $foreign = array_diff($contentKeys, $requestedKeys);
        if ($missing !== []) {
            throw new DraftValidationException('Kunci wajib hilang: '.implode(', ', $missing));
        }
        if ($foreign !== []) {
            throw new DraftValidationException('Kunci asing tidak dibenarkan: '.implode(', ', $foreign));
        }

        // (4) Had panjang (potong lembut / gagal jika >25%).
        $content = $this->enforceLengths($content);

        // (5) services[].key ⊆ input.
        if (isset($content['services']) && is_array($content['services'])) {
            foreach ($content['services'] as $service) {
                $key = $service['key'] ?? null;
                if ($key !== null && ! in_array($key, $serviceKeys, true)) {
                    throw new DraftValidationException("services[].key '{$key}' bukan kunci input sah.");
                }
            }
        }

        return $content;
    }

    private function enforceLengths(array $content): array
    {
        foreach (self::LIMITS as $key => $spec) {
            if (! array_key_exists($key, $content)) {
                continue;
            }

            if (is_int($spec)) {
                $content[$key] = $this->cap($content[$key], $spec, $key);

                continue;
            }

            if (isset($spec['_each']) && is_array($content[$key])) {
                foreach ($content[$key] as $i => $item) {
                    $content[$key][$i] = $this->capFields($item, $spec['_each'], "{$key}[{$i}]");
                }

                continue;
            }

            $content[$key] = $this->capFields($content[$key], $spec, $key);
        }

        return $content;
    }

    private function capFields(array $item, array $fieldLimits, string $parent = ''): array
    {
        foreach ($fieldLimits as $field => $limit) {
            if ($field === '_items' && is_array($limit)) {
                foreach ($limit as $subKey => $subLimits) {
                    if (isset($item[$subKey]) && is_array($item[$subKey])) {
                        foreach ($item[$subKey] as $j => $sub) {
                            $item[$subKey][$j] = $this->capFields($sub, $subLimits, "{$parent}.{$subKey}[{$j}]");
                        }
                    }
                }

                continue;
            }
            if (isset($item[$field]) && is_string($item[$field])) {
                $item[$field] = $this->cap($item[$field], $limit, $parent !== '' ? "{$parent}.{$field}" : $field);
            }
        }

        return $item;
    }

    /** Potong lembut ke had; gagal jika >125% had (§8.4). */
    private function cap(mixed $value, int $limit, string $field = '?'): string
    {
        $value = (string) $value;
        $len = mb_strlen($value);

        if ($len > (int) round($limit * 1.25)) {
            throw new DraftValidationException("Medan '{$field}' melebihi 125% had ({$len} > {$limit}).");
        }

        return $len > $limit ? mb_substr($value, 0, $limit) : $value;
    }
}
