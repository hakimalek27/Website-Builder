<?php

namespace App\Services;

use App\Models\Project;
use App\Models\Verse;
use App\Support\DraftKit;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\View;

/**
 * §Fasa 13 — pasca-proses HTML draf (Peringkat 2) sebelum simpan.
 * §Fasa 15 — Kit Reka Premium: suntik <style id="reka-kit">, tunaikan janji imej PIC
 *   (logo, foto stok bertema, hero berbilang), betulkan pepijat kemasan (© tahun, jawatan
 *   berganda, rangka kosong). Data verbatim PII (§12.7) kekal disisip LOKAL, bukan AI.
 */
class HtmlDraftFinisher
{
    /** Belanjawan kasar saiz fail akhir (data-URI imej besar) — langkau imej tambahan bila lebih. */
    private const SIZE_BUDGET = 1_200_000;

    public function __construct(
        private DesignResolver $designResolver,
        private DraftRenderer $renderer,
        private DraftStyleDirector $director,
    ) {}

    public function finish(Project $project, string $html, int $version): string
    {
        $design = $this->designResolver->resolve($project);
        $t = array_merge([
            'primary' => '#1B5E3F', 'primaryDark' => '#0F3D27', 'accent' => '#C9A961',
            'ink' => '#1A1A1A', 'bg' => '#FAF7F2', 'bgAlt' => '#EFE8DC', 'radius' => '1rem',
        ], $design['tokens']);
        $arabicFont = $design['fonts']['arabic'] ?? 'Amiri';

        $verbatim = $this->renderer->verbatimFor($project);
        $hasContact = ! empty($verbatim['contact']) || ! empty($verbatim['socials']);

        // §Fasa 15 — imej (janji PIC yang dahulu tidak ditunaikan).
        $kit = DraftKit::styleTag($design);
        $logo = $this->logoDataUri($project);
        $images = $this->resolveImages($project, $t);

        // 1) Ganti placeholder (data verbatim + imej).
        $html = $this->replacePlaceholders($project, $html, $t, $arabicFont, $verbatim, $images, $logo, $hasContact);

        // 2) Suntik semula blok wajib yang hilang (AI tak letak token).
        $html = $this->injectMandatory($html, $t, $verbatim, $hasContact);

        // 3) §Fasa 15 — betulkan pepijat kemasan auto-jana.
        $html = $this->stripDuplicateRole($html, $verbatim['perutusan']['role'] ?? null);
        $html = $this->fixCopyrightYear($html);
        $html = $this->removeEmptyScaffold($html);

        // 4) Buang token yatim.
        $html = preg_replace('/\[\[[A-Z0-9_]+\]\]/', '', $html) ?? $html;

        // 5) Suntik kit + jaminan draf.
        $html = $this->injectKit($html, $kit);
        $html = $this->ensureNoindex($html);
        $html = $this->ensureDraftTitle($html, $project);

        return $this->injectDraftChrome($html, $version);
    }

    /**
     * Selesaikan imej hero + seksyen (data-URI). Hero: muat naik (re-encode >1.5MB) ATAU
     * foto stok bertema diwarna palet (stok_sementara/perlu_fotografi). Hero ke-2/3 → seksyen,
     * tertakluk belanjawan saiz.
     *
     * @param  array<string,string>  $t
     * @return array{hero:?string, section1:?string, section2:?string, video:?string}
     */
    private function resolveImages(Project $project, array $t): array
    {
        $sections = $project->sections()->get()->mapWithKeys(fn ($s) => [$s->section_key => $s->data])->all();
        $heroMode = data_get($sections, 'step_6.hero_mode');
        $uploads = $this->renderer->heroImagesForHtml($project);   // [] jika bukan upload

        $hero = $uploads[0] ?? null;
        if ($hero === null && in_array($heroMode, ['stok_sementara', 'perlu_fotografi'], true)) {
            $hero = $this->director->heroStockDataUri($project, $t);   // scene SVG bertema (kecil)
        }

        // Belanjawan: langkau imej seksyen jika fail akhir bakal terlalu besar.
        $used = strlen((string) $hero);
        $section1 = null;
        $section2 = null;
        if (isset($uploads[1]) && $used + strlen($uploads[1]) < self::SIZE_BUDGET) {
            $section1 = $uploads[1];
            $used += strlen($section1);
        }
        if (isset($uploads[2]) && $used + strlen($uploads[2]) < self::SIZE_BUDGET) {
            $section2 = $uploads[2];
        }

        $video = $this->videoAnchor(data_get($sections, 'step_6.video_url'));

        return ['hero' => $hero, 'section1' => $section1, 'section2' => $section2, 'video' => $video];
    }

    /**
     * @param  array<string,string>  $t
     * @param  array<string,mixed>  $verbatim
     * @param  array{hero:?string, section1:?string, section2:?string, video:?string}  $images
     */
    private function replacePlaceholders(Project $project, string $html, array $t, string $arabicFont, array $verbatim, array $images, ?string $logo, bool $hasContact): string
    {
        $r = [];

        $r['[[BANK_BLOCK]]'] = ! empty($verbatim['bank'])
            ? $this->partial('bank', ['bank' => $verbatim['bank'], 't' => $t]) : '';

        $r['[[CONTACT_STRIP]]'] = $hasContact
            ? $this->partial('contact', ['contact' => $verbatim['contact'] ?? [], 'socials' => $verbatim['socials'] ?? [], 't' => $t]) : '';

        $r['[[AJK_GRID]]'] = ! empty($verbatim['ajk']['members'])
            ? $this->partial('ajk', ['members' => $verbatim['ajk']['members'], 'total' => $verbatim['ajk']['total'], 't' => $t]) : '';

        // Perutusan: pelayan suntik nama + jawatan (dibalut penanda untuk fix duplikasi).
        $r['[[PERUTUSAN_NAMA]]'] = ! empty($verbatim['perutusan'])
            ? '<!--rk-per--><p class="rk-mt-3" style="font-weight:700;color:'.$t['primaryDark'].'">'.e($verbatim['perutusan']['name'])
              .'</p><p class="rk-muted" style="font-size:.9rem">'.e($verbatim['perutusan']['role'] ?? '').'</p><!--/rk-per-->' : '';

        if ($project->tier->isMosque()) {
            $r['[[WAKTU_SOLAT]]'] = $this->partial('prayer', ['zone' => $project->jakim_zone ?: '—', 't' => $t]);
            $verse = Verse::activeSeed();
            $r['[[AYAT_ARAB]]'] = ($verse !== null && $verse->arabic_text !== 'PENDING_MANUAL_ENTRY')
                ? $this->partial('verse', ['verse' => $verse, 't' => $t, 'arabicFont' => $arabicFont]) : '';
        }

        $r['[[VIDEO_LINK]]'] = $images['video'] ?? '';

        $html = strtr($html, $r);

        // Imej (nilai src) — ganti atau buang <img> yang mengandunginya.
        $html = $this->replaceOrStripImage($html, '[[LOGO]]', $logo);
        $html = $this->replaceOrStripImage($html, '[[HERO_IMAGE]]', $images['hero']);
        $html = $this->replaceOrStripImage($html, '[[IMG_SECTION_1]]', $images['section1']);
        $html = $this->replaceOrStripImage($html, '[[IMG_SECTION_2]]', $images['section2']);

        return $html;
    }

    /** Ganti token dgn data-URI (nilai src), atau buang <img> yang memuatkannya bila null. */
    private function replaceOrStripImage(string $html, string $token, ?string $uri): string
    {
        if ($uri !== null && $uri !== '') {
            return str_replace($token, $uri, $html);
        }
        $quoted = preg_quote($token, '/');

        return preg_replace('/<img\b[^>]*'.$quoted.'[^>]*>/i', '', $html) ?? $html;
    }

    /** Logo muat naik → data-URI (kekalkan lutsinar; SVG sudah disanitasi semasa upload). */
    private function logoDataUri(Project $project): ?string
    {
        $asset = $project->assets()->where('kind', 'logo')->orderBy('sort')->first();
        if ($asset === null || ! Storage::disk('local')->exists($asset->path)) {
            return null;
        }

        $mime = (string) ($asset->mime ?: '');
        $bytes = Storage::disk('local')->get($asset->path);
        if (str_contains($mime, 'svg')) {
            return 'data:image/svg+xml;base64,'.base64_encode($bytes);
        }

        return 'data:'.($mime ?: 'image/png').';base64,'.base64_encode($bytes);
    }

    /** Pautan video PIC (step_6.video_url) → butang kaca (tiada embed — CSP sandbox). */
    private function videoAnchor(?string $url): ?string
    {
        $url = trim((string) $url);
        if ($url === '' || ! preg_match('#^https?://#i', $url)) {
            return null;
        }

        return '<a class="rk-btn rk-btn--kaca" href="'.e($url).'" target="_blank" rel="noopener">Tonton Video Pengenalan</a>';
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

    /** §Fasa 15 — buang jawatan berganda: elemen jiran selepas blok perutusan yg teksnya = jawatan. */
    private function stripDuplicateRole(string $html, ?string $role): string
    {
        $role = trim((string) $role);
        if ($role !== '') {
            $rx = '/(<!--\/rk-per-->)\s*<(p|span|div|h[1-6])[^>]*>\s*'.preg_quote($role, '/').'\s*<\/\2>/iu';
            $html = preg_replace($rx, '$1', $html, 1) ?? $html;
        }

        return str_replace(['<!--rk-per-->', '<!--/rk-per-->'], '', $html);
    }

    /** §Fasa 15 — betulkan tahun hak cipta yang direka model (cth © 2024) → tahun semasa. */
    private function fixCopyrightYear(string $html): string
    {
        return preg_replace_callback(
            '/((?:&copy;|©|[Hh]ak\s*[Cc]ipta)[^<]{0,40}?)(20\d{2})/u',
            fn ($m) => $m[1].now()->year,
            $html,
        ) ?? $html;
    }

    /** §Fasa 15 — buang rangka kosong "menunggu data" (tbody kosong / komen 'akan diisi'). */
    private function removeEmptyScaffold(string $html): string
    {
        $html = preg_replace('/<table\b[^>]*>\s*(<thead\b.*?<\/thead>)?\s*<tbody\b[^>]*>\s*(<!--.*?-->)?\s*<\/tbody>\s*<\/table>/is', '', $html) ?? $html;

        return preg_replace('/<!--[^>]*?akan diisi[^>]*?-->/i', '', $html) ?? $html;
    }

    /** §Fasa 15 — suntik <style id="reka-kit"> selepas tag buka <head> (0 token AI). */
    private function injectKit(string $html, string $kit): string
    {
        if (str_contains($html, 'id="reka-kit"')) {
            return $html;
        }
        if (preg_match('/<head\b[^>]*>/i', $html)) {
            return preg_replace('/(<head\b[^>]*>)/i', '$1'.$kit, $html, 1) ?? $html;
        }

        return $kit.$html;
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
