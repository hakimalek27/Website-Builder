<?php

namespace App\Services;

use App\Models\Project;
use App\Models\Verse;
use Illuminate\Support\Facades\View;

/**
 * §Fasa 13 — pasca-proses HTML draf (Peringkat 2) sebelum simpan:
 *  1. Ganti token placeholder [[...]] dengan HTML dijana LOKAL (data verbatim §12.7 —
 *     bank/AJK/hubungi/perutusan/hero + waktu solat & ayat statik masjid). BUKAN dari AI.
 *  2. Suntik semula blok wajib (bank/hubungi) jika AI terlupa letak token.
 *  3. Buang token yatim; jamin noindex + tajuk "— DRAF" + banner + watermark.
 */
class HtmlDraftFinisher
{
    public function __construct(private DesignResolver $designResolver, private DraftRenderer $renderer) {}

    public function finish(Project $project, string $html, int $version): string
    {
        $design = $this->designResolver->resolve($project);
        $t = array_merge([
            'primary' => '#1B5E3F', 'primaryDark' => '#0F3D27', 'accent' => '#C9A961',
            'ink' => '#1A1A1A', 'bg' => '#FAF7F2', 'bgAlt' => '#EFE8DC', 'radius' => '1rem',
        ], $design['tokens']);
        $arabicFont = $design['fonts']['arabic'] ?? 'Amiri';

        $verbatim = $this->renderer->verbatimFor($project);
        $heroImage = $this->renderer->heroImageFor($project);
        $hasContact = ! empty($verbatim['contact']) || ! empty($verbatim['socials']);

        // 1) Ganti placeholder.
        $html = $this->replacePlaceholders($project, $html, $t, $arabicFont, $verbatim, $heroImage, $hasContact);

        // 2) Suntik semula blok wajib yang hilang (AI tak letak token).
        $html = $this->injectMandatory($html, $t, $verbatim, $hasContact);

        // 3) Buang token yatim.
        $html = preg_replace('/\[\[[A-Z0-9_]+\]\]/', '', $html) ?? $html;

        // 4) Jaminan draf.
        $html = $this->ensureNoindex($html);
        $html = $this->ensureDraftTitle($html, $project);

        return $this->injectDraftChrome($html, $version);
    }

    /**
     * @param  array<string,string>  $t
     * @param  array<string,mixed>  $verbatim
     */
    private function replacePlaceholders(Project $project, string $html, array $t, string $arabicFont, array $verbatim, ?string $heroImage, bool $hasContact): string
    {
        $r = [];

        $r['[[BANK_BLOCK]]'] = ! empty($verbatim['bank'])
            ? $this->partial('bank', ['bank' => $verbatim['bank'], 't' => $t]) : '';

        $r['[[CONTACT_STRIP]]'] = $hasContact
            ? $this->partial('contact', ['contact' => $verbatim['contact'] ?? [], 'socials' => $verbatim['socials'] ?? [], 't' => $t]) : '';

        $r['[[AJK_GRID]]'] = ! empty($verbatim['ajk']['members'])
            ? $this->partial('ajk', ['members' => $verbatim['ajk']['members'], 'total' => $verbatim['ajk']['total'], 't' => $t]) : '';

        $r['[[PERUTUSAN_NAMA]]'] = ! empty($verbatim['perutusan'])
            ? '<p style="margin-top:16px;font-weight:700;color:'.$t['primaryDark'].'">'.e($verbatim['perutusan']['name'])
              .'</p><p style="opacity:.65;font-size:.85rem">'.e($verbatim['perutusan']['role'] ?? '').'</p>' : '';

        if ($project->tier->isMosque()) {
            $r['[[WAKTU_SOLAT]]'] = $this->partial('prayer', ['zone' => $project->jakim_zone ?: '—', 't' => $t]);
            $verse = Verse::activeSeed();
            $r['[[AYAT_ARAB]]'] = ($verse !== null && $verse->arabic_text !== 'PENDING_MANUAL_ENTRY')
                ? $this->partial('verse', ['verse' => $verse, 't' => $t, 'arabicFont' => $arabicFont]) : '';
        }

        // HERO_IMAGE: data-URI, atau buang <img> yang mengandungnya (fallback kecerunan CSS).
        if ($heroImage !== null) {
            $html = str_replace('[[HERO_IMAGE]]', $heroImage, $html);
        } else {
            $html = preg_replace('/<img\b[^>]*\[\[HERO_IMAGE\]\][^>]*>/i', '', $html) ?? $html;
        }

        return strtr($html, $r);
    }

    /**
     * @param  array<string,string>  $t
     * @param  array<string,mixed>  $verbatim
     */
    private function injectMandatory(string $html, array $t, array $verbatim, bool $hasContact): string
    {
        $inject = '';
        if ($hasContact && ! str_contains($html, 'data-reka="contact"')) {
            $inject .= $this->partial('contact', ['contact' => $verbatim['contact'] ?? [], 'socials' => $verbatim['socials'] ?? [], 't' => $t]);
        }
        if (! empty($verbatim['bank']) && ! str_contains($html, 'data-reka="bank"')) {
            $inject .= $this->partial('bank', ['bank' => $verbatim['bank'], 't' => $t]);
        }

        return $inject === '' ? $html : $this->insertBeforeBodyClose($html, $inject);
    }

    private function insertBeforeBodyClose(string $html, string $fragment): string
    {
        if (stripos($html, '</body>') !== false) {
            return preg_replace('/<\/body>/i', $fragment.'</body>', $html, 1) ?? $html;
        }

        return $html.$fragment;
    }

    private function ensureNoindex(string $html): string
    {
        if (preg_match('/<meta[^>]+name=["\']robots["\'][^>]*>/i', $html)) {
            return $html;
        }
        $meta = '<meta name="robots" content="noindex">';
        if (preg_match('/<head\b[^>]*>/i', $html)) {
            return preg_replace('/(<head\b[^>]*>)/i', '$1'.$meta, $html, 1) ?? $html;
        }

        return $meta.$html;
    }

    private function ensureDraftTitle(string $html, Project $project): string
    {
        if (preg_match('/<title>(.*?)<\/title>/is', $html, $m)) {
            $title = trim($m[1]);
            if (! str_contains($title, 'DRAF')) {
                return preg_replace('/<title>.*?<\/title>/is', '<title>'.$title.' — DRAF</title>', $html, 1) ?? $html;
            }

            return $html;
        }
        $title = '<title>'.e($project->mosque_name).' — DRAF</title>';
        if (preg_match('/<head\b[^>]*>/i', $html)) {
            return preg_replace('/(<head\b[^>]*>)/i', '$1'.$title, $html, 1) ?? $html;
        }

        return $html;
    }

    private function injectDraftChrome(string $html, int $version): string
    {
        $css = '<style>'
            .'.reka-draft-banner{position:sticky;top:0;z-index:2147483647;background:#0F3D27;color:#fff;text-align:center;font-size:.8rem;font-weight:600;padding:8px 12px;letter-spacing:.02em}'
            .'.reka-watermark{position:fixed;inset:0;z-index:2147483646;pointer-events:none;background-image:repeating-linear-gradient(-45deg,transparent 0 120px,rgba(0,0,0,.05) 120px 240px)}'
            .'</style>';
        $banner = '<div class="reka-draft-banner">'.e(trans('reka.watermark')).' · Dijana '.now()->format('d/m/Y').' · Versi '.$version.'</div>';
        $wm = '<div class="reka-watermark"></div>';

        $html = preg_match('/<\/head>/i', $html)
            ? (preg_replace('/<\/head>/i', $css.'</head>', $html, 1) ?? $html)
            : $css.$html;

        return preg_match('/<body\b[^>]*>/i', $html)
            ? (preg_replace('/(<body\b[^>]*>)/i', '$1'.$banner.$wm, $html, 1) ?? $html)
            : $banner.$wm.$html;
    }

    /** @param array<string,mixed> $data */
    private function partial(string $name, array $data): string
    {
        return View::make('draft.partials.'.$name, $data)->render();
    }
}
