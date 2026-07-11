<?php

namespace App\Services;

use App\Models\Project;
use App\Support\PageCatalog;

/**
 * Skor kelengkapan & gate (§6.12). Set medan wajib AKTIF dikira dinamik
 * (bergantung halaman ditanda & mod dipilih) → skor.
 */
class CompletenessService
{
    /**
     * @return array{score:int, total:int, filled:int, missing:array<int, array{label:string, step:int, key:string}>}
     */
    public function compute(Project $project): array
    {
        $sections = $project->sections()->get()
            ->mapWithKeys(fn ($s) => [$s->section_key => $s->data])
            ->all();

        $get = fn (int $step, string $key) => data_get($sections, "step_{$step}.{$key}");
        $getPanel = fn (string $page, string $key) => data_get($sections, "step_4.panels.{$page}.{$key}");

        $req = [];
        $add = function (string $label, int $step, string $key, bool $filled) use (&$req) {
            $req[] = ['label' => $label, 'step' => $step, 'key' => $key, 'filled' => $filled];
        };

        // --- Global (L0, L1, L2 mood, L9) ---
        $add('Jenis organisasi', 0, 'tier', filled($get(0, 'tier')));

        $l1 = [
            'official_name' => 'Nama rasmi organisasi', 'address_line1' => 'Alamat', 'postcode' => 'Poskod',
            'city' => 'Bandar', 'state' => 'Negeri', 'authority' => 'Pihak berkuasa',
            'gps' => 'Koordinat GPS', 'phone_primary' => 'Telefon utama', 'email' => 'E-mel', 'logo_status' => 'Status logo',
        ];
        // Zon solat JAKIM hanya wajib untuk masjid/surau (NGO tiada waktu solat).
        if ($project->tier->isMosque()) {
            $l1['jakim_zone'] = 'Zon solat';
        }
        foreach ($l1 as $key => $label) {
            $add($label, 1, $key, filled($get(1, $key)));
        }

        $add('Nada penulisan', 2, 'mood', filled($get(2, 'mood')));

        // §Fasa 16 — mod templat: templat rujukan ATAU link contoh wajib (gate submit).
        if (DraftGenerationService::pipelineMode() === 'template') {
            $add('Templat rujukan / link contoh', 2, 'template_choice',
                filled($get(2, 'template_id')) || filled($get(2, 'template_custom_url')));
        }

        $l9 = [
            'pic_name' => 'Nama PIC', 'pic_position' => 'Jawatan PIC', 'pic_phone' => 'Telefon PIC',
            'consent_pdpa' => 'Persetujuan PDPA', 'declare_truth_authority' => 'Perakuan kuasa',
        ];
        foreach ($l9 as $key => $label) {
            $add($label, 9, $key, (bool) $get(9, $key));
        }

        // --- L4 bersyarat: medan wajib setiap panel aktif (ikut tier) ---
        $activePages = $project->pages()->where('enabled', true)->pluck('page_key')->all();
        $panels = PageCatalog::panelsFor($project->tier);
        $meta = PageCatalog::meta();

        foreach (array_intersect($activePages, array_keys($panels)) as $page) {
            foreach ($panels[$page] as $field) {
                if (($field['required'] ?? false) && in_array($field['type'], ['text', 'textarea', 'select', 'radio', 'email', 'url', 'number'], true)) {
                    $label = ($meta[$page]['label'] ?? $page).': '.$field['label'];
                    $add($label, 4, "{$page}.{$field['key']}", filled($getPanel($page, $field['key'])));
                }
            }

            // Galeri: consent WAJIB jika ada fail.
            if ($page === 'galeri' && ! empty($getPanel('galeri', 'images'))) {
                $add('Galeri: kebenaran gambar', 4, 'galeri.consent', (bool) $getPanel('galeri', 'consent'));
            }
        }

        // --- L5 ---
        $add('Kemas kini kandungan (CMS)', 5, 'cms_updater', filled($get(5, 'cms_updater')));
        if (in_array('infaq', $activePages, true)) {
            $add('Kaedah pembayaran', 5, 'payment_gateway', filled($get(5, 'payment_gateway')));
        }

        // --- L6 hero_mode ---
        $add('Mod imej hero', 6, 'hero_mode', filled($get(6, 'hero_mode')));

        // --- L8 domain_status ---
        $add('Status domain', 8, 'domain_status', filled($get(8, 'domain_status')));

        $total = count($req);
        $filledCount = count(array_filter($req, fn ($r) => $r['filled']));
        $missing = array_values(array_map(
            fn ($r) => ['label' => $r['label'], 'step' => $r['step'], 'key' => $r['key']],
            array_filter($req, fn ($r) => ! $r['filled']),
        ));

        return [
            'score' => $total > 0 ? (int) round(100 * $filledCount / $total) : 0,
            'total' => $total,
            'filled' => $filledCount,
            'missing' => $missing,
        ];
    }

    /** Gate Hantar (P3): skor = 100 (§6.12). */
    public function canSubmit(Project $project): bool
    {
        return $this->compute($project)['score'] === 100;
    }

    /**
     * Gate Jana (P4): status ≥ submitted DAN logo ok DAN hero ok (§6.12).
     */
    public function canGenerate(Project $project): bool
    {
        $sections = $project->sections()->get()->mapWithKeys(fn ($s) => [$s->section_key => $s->data])->all();

        $logoStatus = data_get($sections, 'step_1.logo_status');
        $logoOk = $logoStatus !== 'ada' || $project->assets()->where('kind', 'logo')->exists();

        $heroMode = data_get($sections, 'step_6.hero_mode');
        $heroOk = $heroMode !== 'upload' || $project->assets()->where('kind', 'hero')->exists();

        return $logoOk && $heroOk;
    }
}
