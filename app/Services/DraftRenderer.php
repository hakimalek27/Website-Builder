<?php

namespace App\Services;

use App\Models\Generation;
use App\Models\Project;
use App\Models\Verse;
use App\Support\PageCatalog;
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

        return view('draft.shell', [
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
            'showPrayer' => true,   // masjid; ditetapkan ikut tier di Fasa 11 NGO
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
