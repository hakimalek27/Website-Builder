<?php

namespace App\Services;

use Illuminate\Support\Str;

/**
 * §14.4 — sanity-seed.ndjson. Satu dokumen JSON per baris; _type/_id deterministik
 * ({type}-{slug}-{n}). Pemetaan ke skema mamkl (siteSettings, service, facility,
 * committee, quranClass, faq, announcement, weeklyKuliahSlot, historyArticle...).
 */
class SanitySeedBuilder
{
    public function build(array $spec): string
    {
        $slug = Str::slug($spec['mosque']['short_name'] ?? $spec['mosque']['official_name'] ?? 'masjid') ?: 'masjid';
        $lines = [];

        // siteSettings (singleton).
        $lines[] = [
            '_type' => 'siteSettings',
            '_id' => 'siteSettings',
            'title' => $spec['mosque']['official_name'] ?? null,
            'prayerZone' => $spec['features']['prayer']['zone'] ?? null,
            'contact' => [
                'phones' => $spec['mosque']['phones'] ?? [],
                'email' => $spec['mosque']['email'] ?? null,
                'address' => $spec['mosque']['address'] ?? null,
            ],
            'bankInfo' => [
                'bankName' => $spec['content']['infaq']['bank_name'] ?? null,
                'accountNumber' => $spec['content']['infaq']['bank_account'] ?? null,
                'accountHolder' => $spec['content']['infaq']['account_holder'] ?? null,
            ],
            'whatsappChannel' => $spec['features']['wa_button'] ?? null,
            'officeHours' => $spec['content']['visitor']['visiting_hours'] ?? [],
            'infaqCategories' => $spec['content']['infaq']['categories'] ?? [],
        ];

        // service × n.
        foreach (($spec['content']['services'] ?? []) as $i => $svc) {
            $lines[] = [
                '_type' => 'service',
                '_id' => "service-{$slug}-".($i + 1),
                'name' => $svc['key'] ?? null,
                'slug' => ['_type' => 'slug', 'current' => Str::slug($svc['key'] ?? 'service-'.($i + 1))],
                'shortDescription' => $svc['short_desc'] ?? null,
                'fullDescription' => $svc['full_desc'] ?? null,
                'requirements' => array_values($svc['requirements'] ?? []),
                'documents' => array_values($svc['documents'] ?? []),
                'fee' => $svc['fee'] ?? null,
                'applyMethod' => $svc['apply_method'] ?? null,
                'order' => $i + 1,
            ];
        }

        // facility × n.
        foreach (($spec['content']['facilities'] ?? []) as $i => $fac) {
            $lines[] = ['_type' => 'facility', '_id' => "facility-{$slug}-".($i + 1), 'name' => is_array($fac) ? ($fac['name'] ?? null) : $fac, 'order' => $i + 1];
        }

        // committee × n.
        foreach (($spec['content']['ajk']['members'] ?? []) as $i => $m) {
            $lines[] = [
                '_type' => 'committee', '_id' => "committee-{$slug}-".($i + 1),
                'name' => $m['name'] ?? null, 'position' => $m['position'] ?? null,
                'group' => $m['group'] ?? 'pengurusan', 'order' => $i + 1,
            ];
        }

        // quranClass × n (enum level 1:1).
        foreach (($spec['content']['quran_classes'] ?? []) as $i => $c) {
            $lines[] = [
                '_type' => 'quranClass', '_id' => "quranClass-{$slug}-".($i + 1),
                'name' => $c['name'] ?? null, 'level' => $c['level'] ?? null,
                'days' => $c['days'] ?? null, 'time' => $c['time'] ?? null,
                'focus' => $c['focus'] ?? null, 'fee' => $c['fee'] ?? null, 'order' => $i + 1,
            ];
        }

        // weeklyKuliahSlot × n.
        foreach (($spec['content']['kuliah'] ?? []) as $i => $k) {
            $lines[] = [
                '_type' => 'weeklyKuliahSlot', '_id' => "weeklyKuliahSlot-{$slug}-".($i + 1),
                'day' => $k['day'] ?? null, 'time' => $k['time'] ?? null,
                'topic' => $k['topic'] ?? null, 'speaker' => $k['speaker'] ?? null,
                'kitab' => $k['kitab'] ?? null, 'session' => $k['session'] ?? null, 'order' => $i + 1,
            ];
        }

        // faq × n.
        foreach (($spec['content']['faq'] ?? []) as $i => $f) {
            $lines[] = [
                '_type' => 'faq', '_id' => "faq-{$slug}-".($i + 1),
                'category' => $f['category'] ?? 'umum', 'question' => $f['q'] ?? null, 'answer' => $f['a'] ?? null, 'order' => $i + 1,
            ];
        }

        // announcement × n (seed items).
        foreach (($spec['content']['news_seed'] ?? []) as $i => $n) {
            $lines[] = [
                '_type' => 'announcement', '_id' => "announcement-{$slug}-".($i + 1),
                'title' => $n['tajuk'] ?? null, 'date' => $n['tarikh'] ?? null, 'excerpt' => $n['ringkasan'] ?? null, 'order' => $i + 1,
            ];
        }

        // historyArticle (jika mode tulis penuh).
        if (($spec['content']['sejarah']['mode'] ?? null) === 'tulis_penuh') {
            $lines[] = [
                '_type' => 'historyArticle', '_id' => "historyArticle-{$slug}-1",
                'body' => $spec['content']['sejarah']['full_text'] ?? null,
            ];
        }

        return implode("\n", array_map(
            fn ($doc) => json_encode($doc, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            $lines,
        ))."\n";
    }
}
