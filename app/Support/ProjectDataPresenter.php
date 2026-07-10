<?php

namespace App\Support;

use App\Models\Project;
use Illuminate\Support\Str;

/**
 * Fasa 12 W2/W3 — penukar data seksyen wizard → Markdown berlabel BM.
 * Dipakai oleh ViewProject infolist (admin, penuh), halaman Semak PIC (bermask),
 * dan BriefBuilder (penuh). Step 4 dilabel dari PageCatalog::panelsFor.
 */
class ProjectDataPresenter
{
    /** Label medan langkah 0-3, 5-9 (langkah 4 dari skema panel). */
    private const LABELS = [
        'tier' => 'Jenis organisasi', 'is_gov' => 'Institusi kerajaan',
        'official_name' => 'Nama rasmi', 'short_name' => 'Nama pendek', 'address_line1' => 'Alamat', 'address_line2' => 'Alamat (baris 2)',
        'postcode' => 'Poskod', 'city' => 'Bandar', 'state' => 'Negeri', 'jakim_zone' => 'Zon JAKIM', 'authority' => 'Pihak berkuasa',
        'established_year' => 'Tahun ditubuhkan', 'capacity' => 'Kapasiti', 'gps' => 'GPS', 'phone_primary' => 'Telefon utama',
        'phone_secondary' => 'Telefon kedua', 'email' => 'E-mel', 'facebook_url' => 'Facebook', 'instagram_url' => 'Instagram',
        'youtube_url' => 'YouTube', 'tiktok_url' => 'TikTok', 'logo_status' => 'Status logo',
        'design_package' => 'Pakej reka bentuk', 'mood' => 'Nada', 'font_pair' => 'Pasangan font', 'layout_home' => 'Susun atur',
        'header_style' => 'Gaya pengepala', 'footer_style' => 'Gaya pengaki', 'card_style' => 'Gaya kad', 'divider' => 'Pembatas',
        'animations' => 'Animasi halus', 'palette_mode' => 'Mod palet', 'custom_primary' => 'Warna primer', 'custom_accent' => 'Warna aksen',
        'arabic_font' => 'Font Arab', 'islamic_elements' => 'Elemen Islamik', 'icon_style' => 'Gaya ikon',
        'pages' => 'Halaman dipilih', 'custom' => 'Halaman tersuai',
        'cms_updater' => 'Pengemas kini kandungan', 'payment_gateway' => 'Gerbang pembayaran', 'gateway_status' => 'Status gerbang',
        'whatsapp_button' => 'Butang WhatsApp', 'wa_number' => 'Nombor WhatsApp', 'whatsapp_channel' => 'Saluran WhatsApp', 'wa_channel_url' => 'URL saluran WhatsApp',
        'bilingual' => 'Dwibahasa', 'kariah_system' => 'Sistem kariah', 'kariah_url' => 'URL kariah', 'tv_display' => 'Paparan TV',
        'pwa' => 'PWA', 'wa_broadcast' => 'Siaran WhatsApp', 'add_to_calendar' => 'Tambah ke kalendar',
        'hero_mode' => 'Mod hero', 'video_url' => 'Video hero', 'hero_files' => 'Fail hero',
        'liked_refs' => 'Laman rujukan disukai', 'dislikes' => 'Perkara tidak disukai', 'url' => 'URL', 'what_liked' => 'Apa yang disukai',
        'domain_status' => 'Status domain', 'domain_name' => 'Nama domain', 'registrar' => 'Pendaftar', 'dns_access' => 'Akses DNS',
        'existing_site' => 'Laman sedia ada', 'migrate_content' => 'Pindah kandungan', 'official_email_status' => 'Status e-mel rasmi',
        'hosting' => 'Hosting', 'maintenance' => 'Pakej penyelenggaraan', 'target_live' => 'Sasaran live',
        'pic_name' => 'Nama PIC', 'pic_position' => 'Jawatan PIC', 'pic_phone' => 'Telefon PIC', 'free_notes' => 'Nota tambahan',
        'budget_hint' => 'Bajet', 'consent_pdpa' => 'Persetujuan PDPA', 'declare_truth_authority' => 'Perakuan kebenaran',
    ];

    /** Medan yang dimask bila $maskPii (paparan PIC). */
    private const MASKED = ['bank_account'];

    /**
     * Semua blok langkah tidak-kosong.
     *
     * @return array<int, array{step:int, title:string, subtitle:string, markdown:string}>
     */
    public static function all(Project $project, bool $maskPii = false): array
    {
        $sections = $project->sections()->get()->mapWithKeys(fn ($s) => [$s->section_key => $s->data])->all();

        $blocks = [];
        foreach (WizardSteps::all() as $step) {
            $data = $sections['step_'.$step['index']] ?? [];
            if (self::isBlank($data)) {
                continue;
            }
            $md = $step['index'] === 4
                ? self::step4Markdown($project, is_array($data) ? $data : [], $maskPii)
                : self::rowsMarkdown(is_array($data) ? $data : [], self::LABELS, $maskPii);

            if ($md === '') {
                continue;
            }
            $blocks[] = [
                'step' => $step['index'],
                'title' => $step['title'],
                'subtitle' => $step['subtitle'],
                'markdown' => $md,
            ];
        }

        return $blocks;
    }

    /** Markdown satu blok langkah (untuk TextEntry->markdown() atau brief). */
    public static function markdown(array $block): string
    {
        return "### {$block['title']}\n\n{$block['markdown']}";
    }

    // --- Dalaman ---

    private static function step4Markdown(Project $project, array $data, bool $maskPii): string
    {
        $panels = $data['panels'] ?? [];
        $schema = PageCatalog::panelsFor($project->tier);
        $meta = PageCatalog::metaFor($project->tier);

        $out = [];
        foreach ($panels as $panelKey => $panelData) {
            if (self::isBlank($panelData) || ! is_array($panelData)) {
                continue;
            }
            $labels = self::panelLabels($schema[$panelKey] ?? []);
            $rows = self::rowsMarkdown($panelData, $labels, $maskPii);
            if ($rows === '') {
                continue;
            }
            $heading = $meta[$panelKey]['label'] ?? Str::headline($panelKey);
            $out[] = "**{$heading}**\n{$rows}";
        }

        return implode("\n\n", $out);
    }

    /** @return array<string, string> key => label dari definisi panel (termasuk medan repeater). */
    private static function panelLabels(array $fields): array
    {
        $labels = [];
        foreach ($fields as $f) {
            if (! isset($f['key'])) {
                continue;
            }
            $labels[$f['key']] = $f['label'] ?? Str::headline($f['key']);
            foreach ($f['item'] ?? [] as $itf) {
                if (isset($itf['key'])) {
                    $labels[$itf['key']] = $itf['label'] ?? Str::headline($itf['key']);
                }
            }
        }

        return $labels;
    }

    /** @param array<string, string> $labels */
    private static function rowsMarkdown(array $data, array $labels, bool $maskPii): string
    {
        $lines = [];
        foreach ($data as $key => $value) {
            if ($key === 'panels' || self::isBlank($value)) {
                continue;
            }
            $label = $labels[$key] ?? self::LABELS[$key] ?? Str::headline((string) $key);
            $line = self::lineFor($label, (string) $key, $value, $labels, $maskPii);
            if ($line !== '') {
                $lines[] = $line;
            }
        }

        return implode("\n", $lines);
    }

    /** @param array<string, string> $labels */
    private static function lineFor(string $label, string $key, mixed $value, array $labels, bool $maskPii): string
    {
        if (! is_array($value)) {
            return "- **{$label}:** ".self::scalar($key, $value, $maskPii);
        }

        // Senarai (repeater / checklist).
        if (array_is_list($value)) {
            $items = [];
            foreach ($value as $v) {
                if (is_array($v)) {
                    $parts = [];
                    foreach ($v as $k2 => $v2) {
                        if (self::isBlank($v2) || is_array($v2)) {
                            continue;
                        }
                        $sub = $labels[$k2] ?? self::LABELS[$k2] ?? Str::headline((string) $k2);
                        $parts[] = "{$sub}: ".self::scalar((string) $k2, $v2, $maskPii);
                    }
                    if ($parts !== []) {
                        $items[] = implode(' · ', $parts);
                    }
                } elseif (! self::isBlank($v)) {
                    $items[] = (string) $v;
                }
            }

            if ($items === []) {
                return '';
            }

            return "- **{$label}:**\n".implode("\n", array_map(fn ($i) => "  - {$i}", $items));
        }

        // Objek (assoc).
        $parts = [];
        foreach ($value as $k2 => $v2) {
            if (self::isBlank($v2)) {
                continue;
            }
            $sub = $labels[$k2] ?? self::LABELS[$k2] ?? Str::headline((string) $k2);
            $parts[] = "{$sub}: ".(is_array($v2) ? implode(', ', array_filter($v2, fn ($x) => ! is_array($x))) : self::scalar((string) $k2, $v2, $maskPii));
        }

        return $parts === [] ? '' : "- **{$label}:** ".implode(' · ', $parts);
    }

    private static function scalar(string $key, mixed $value, bool $maskPii): string
    {
        if (is_bool($value)) {
            return $value ? 'Ya' : 'Tidak';
        }
        $s = trim((string) $value);
        if ($maskPii && in_array($key, self::MASKED, true) && $s !== '') {
            return '••••'.substr($s, -4);
        }

        return $s;
    }

    private static function isBlank(mixed $value): bool
    {
        return $value === null || $value === '' || $value === [] || $value === false;
    }
}
