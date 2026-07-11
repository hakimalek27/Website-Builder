<?php

namespace App\Services;

use App\Models\Generation;
use App\Models\Project;
use App\Models\Verse;
use App\Support\PageCatalog;
use App\Support\StockLibrary;
use Illuminate\Support\Facades\Storage;

/**
 * §8.5 — render draf melalui Blade shell deterministik. AI TIDAK menulis HTML.
 * Suntikan server (bukan pilihan): banner + watermark "DRAF" + noindex.
 * Waktu solat = blok STATIK berlabel (TIADA panggilan API semasa jana, §9.3).
 * Ayat Arab HANYA dari verse_library aktif (§9.2) — dilangkau jika placeholder.
 */
class DraftRenderer
{
    public function __construct(private DesignResolver $designResolver) {}

    /**
     * @param  array<string,mixed>  $content  Output AI yang telah divalidasi (§8.4).
     */
    public function render(Project $project, array $content, int $version): string
    {
        $design = $this->designResolver->resolve($project);

        $verse = Verse::activeSeed();
        $showVerse = $verse !== null && $verse->arabic_text !== 'PENDING_MANUAL_ENTRY';

        $pages = $project->pages()->where('enabled', true)->orderBy('sort')->get()
            ->map(fn ($p) => [
                'key' => $p->page_key,
                'label' => $p->custom_name ?: (PageCatalog::meta()[$p->page_key]['label'] ?? $p->page_key),
            ])->all();

        // Medan asal AI (mode butir_ringkas) untuk penanda "✎ Dijana AI".
        $aiFlags = $this->aiFlaggedSections($project);

        $sections = $project->sections()->get()->mapWithKeys(fn ($s) => [$s->section_key => $s->data])->all();

        return view('draft.shell', [
            'verbatim' => $this->verbatimData($project, $sections),   // AJK/bank/hubungi dari wizard (bukan AI) — render LOKAL
            'heroImage' => $this->heroImage($project, $sections),
            'project' => $project,
            'content' => $content,
            'tokens' => $design['tokens'],
            'fonts' => $design['fonts'],
            'layout' => $design['layout'],
            'iconStyle' => $design['icon_style'],
            'header' => $design['header'],
            'footer' => $design['footer'],
            'cardStyle' => $design['card'],
            'divider' => $design['divider'],
            'animations' => $design['animations'],
            'showPrayer' => $project->tier->isMosque(),   // NGO tiada kad waktu solat
            'verse' => $verse,
            'showVerse' => $showVerse,
            'zone' => $project->jakim_zone ?: '—',
            'pages' => $pages,
            'version' => $version,
            'generatedAt' => now()->format('d/m/Y'),
            'aiFlags' => $aiFlags,
        ])->render();
    }

    /** Render + simpan snapshot (§8.5). Pulangkan path relatif. */
    public function renderAndStore(Project $project, Generation $generation, array $content, int $version): string
    {
        $html = $this->render($project, $content, $version);
        $path = "drafts/{$project->id}/{$generation->id}.html";
        Storage::disk('local')->put($path, $html);

        return $path;
    }

    /**
     * Data verbatim (BUKAN AI) untuk saluran HTML (§Fasa 13) — bungkus verbatimData().
     * Guna semula logik sama supaya bank/AJK/hubungi identik dengan shell.
     *
     * @return array<string,mixed>
     */
    public function verbatimFor(Project $project): array
    {
        $sections = $project->sections()->get()->mapWithKeys(fn ($s) => [$s->section_key => $s->data])->all();

        return $this->verbatimData($project, $sections);
    }

    /** Imej hero data-URI untuk saluran HTML (§Fasa 13) — bungkus heroImage(). */
    public function heroImageFor(Project $project): ?string
    {
        $sections = $project->sections()->get()->mapWithKeys(fn ($s) => [$s->section_key => $s->data])->all();

        return $this->heroImage($project, $sections);
    }

    /**
     * §Fasa 15 — SEMUA imej hero muat naik untuk saluran HTML, sebagai data-URI.
     * Berbeza heroImage(): (a) re-encode aset >1.5MB (BUKAN jatuh ke gradien — punca hero
     * senyap dahulu); (b) pulangkan kesemua (hero ke-2/3 → imej seksyen). Shell TIDAK guna
     * ini (kekal byte-identik).
     *
     * @return array<int,string>
     */
    public function heroImagesForHtml(Project $project): array
    {
        $sections = $project->sections()->get()->mapWithKeys(fn ($s) => [$s->section_key => $s->data])->all();
        if (data_get($sections, 'step_6.hero_mode') !== 'upload') {
            return [];
        }

        $out = [];
        foreach ($project->assets()->where('kind', 'hero')->orderBy('sort')->get() as $asset) {
            if (! Storage::disk('local')->exists($asset->path)) {
                continue;
            }
            if ((int) $asset->size <= 1_500_000) {
                $out[] = 'data:'.($asset->mime ?: 'image/jpeg').';base64,'.base64_encode(Storage::disk('local')->get($asset->path));
            } elseif (($uri = StockLibrary::reencodeToDataUri($asset->path, 1600, 300)) !== null) {
                $out[] = $uri;   // >1.5MB → re-encode (bukan buang)
            }
        }

        return $out;
    }

    /**
     * Data verbatim (BUKAN AI) dirender LOKAL sahaja dalam draf: perutusan nama/jawatan,
     * AJK (cap 12), bank infaq/derma, hubungi + sosial (step_1). TIDAK dihantar ke AI.
     *
     * @param  array<string,mixed>  $sections
     * @return array<string,mixed>
     */
    private function verbatimData(Project $project, array $sections): array
    {
        $l1 = $sections['step_1'] ?? [];
        $l4 = $sections['step_4']['panels'] ?? [];
        $out = [];

        if (filled($l4['perutusan']['name'] ?? null)) {
            $out['perutusan'] = ['name' => $l4['perutusan']['name'], 'role' => $l4['perutusan']['role'] ?? null];
        }

        $members = $l4['ajk']['members'] ?? [];
        if (! empty($members)) {
            $out['ajk'] = [
                'members' => array_map(
                    fn ($m) => ['name' => $m['name'] ?? null, 'position' => $m['position'] ?? null],
                    array_slice(array_values($members), 0, 12),
                ),
                'total' => count($members),
            ];
        }

        foreach (['infaq', 'derma'] as $bp) {
            if (filled($l4[$bp]['bank_account'] ?? null)) {
                $out['bank'] = [
                    'bank_name' => $l4[$bp]['bank_name'] ?? null,
                    'bank_account' => $l4[$bp]['bank_account'] ?? null,
                    'account_holder' => $l4[$bp]['account_holder'] ?? null,
                ];
                break;
            }
        }

        $address = trim(implode(', ', array_filter([$l1['address_line1'] ?? null, $l1['city'] ?? null, $l1['state'] ?? null])));
        $out['contact'] = array_filter([
            'phone' => $l1['phone_primary'] ?? null,
            'email' => $l1['email'] ?? null,
            'address' => $address ?: null,
        ], fn ($v) => filled($v));

        $out['socials'] = array_filter([
            'facebook' => $l1['facebook_url'] ?? null,
            'instagram' => $l1['instagram_url'] ?? null,
            'youtube' => $l1['youtube_url'] ?? null,
            'tiktok' => $l1['tiktok_url'] ?? null,
        ], fn ($v) => filled($v));

        return $out;
    }

    /**
     * Imej hero sebagai data-URI (snapshot serba-lengkap) bila hero_mode=upload & aset ≤1.5MB.
     * Lebih besar / tiada → null (fallback kecerunan CSS).
     *
     * @param  array<string,mixed>  $sections
     */
    private function heroImage(Project $project, array $sections): ?string
    {
        if (data_get($sections, 'step_6.hero_mode') !== 'upload') {
            return null;
        }

        $asset = $project->assets()->where('kind', 'hero')->orderBy('sort')->first();
        if ($asset === null || (int) $asset->size > 1_500_000 || ! Storage::disk('local')->exists($asset->path)) {
            return null;
        }

        return 'data:'.($asset->mime ?: 'image/jpeg').';base64,'.base64_encode(Storage::disk('local')->get($asset->path));
    }

    /** Seksyen yang mengandungi kandungan mode AI (§9.4). */
    private function aiFlaggedSections(Project $project): array
    {
        $panels = data_get(
            $project->sections()->where('section_key', 'step_4')->value('data'),
            'panels',
            [],
        );

        $flags = [];
        foreach (['sejarah', 'perutusan'] as $page) {
            if (($panels[$page]['mode'] ?? null) === 'butir_ringkas') {
                $flags[] = $page;
            }
        }

        return $flags;
    }
}
