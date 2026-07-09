<?php

namespace App\Services;

use App\Models\Approval;
use App\Models\Project;
use Illuminate\Support\Str;

/**
 * §14.2 — spec.json kanonik. Struktur kunci peringkat atas TEPAT (kontrak dengan
 * build-brief). Dibekukan verbatim dalam approvals.snapshot.
 */
class SpecBuilder
{
    public function __construct(private DesignResolver $designResolver) {}

    public function build(Project $project, ?Approval $approval = null): array
    {
        $sections = $project->sections()->get()->mapWithKeys(fn ($s) => [$s->section_key => $s->data])->all();
        $l1 = $sections['step_1'] ?? [];
        $l4 = $sections['step_4']['panels'] ?? [];
        $l5 = $sections['step_5'] ?? [];
        $l7 = $sections['step_7'] ?? [];
        $l8 = $sections['step_8'] ?? [];
        $l9 = $sections['step_9'] ?? [];

        $design = $this->designResolver->resolve($project);

        return [
            'reka_spec_version' => '1.0',
            'generated_at' => now()->toIso8601String(),
            'approval' => $approval !== null ? [
                'pic_name' => $approval->pic_name,
                'pic_position' => $approval->pic_position,
                'approved_at' => $approval->approved_at?->toIso8601String(),
            ] : null,
            'meta' => [
                'tier' => $project->tier->value,
                'is_gov' => (bool) $project->is_gov,
            ],
            'mosque' => [
                'official_name' => $l1['official_name'] ?? $project->mosque_name,
                'short_name' => $l1['short_name'] ?? $project->short_name,
                'address' => [
                    'line1' => $l1['address_line1'] ?? null,
                    'line2' => $l1['address_line2'] ?? null,
                    'postcode' => $l1['postcode'] ?? null,
                    'city' => $l1['city'] ?? null,
                ],
                'state' => $l1['state'] ?? $project->state,
                'jakim_zone' => $project->jakim_zone,
                'authority' => $l1['authority'] ?? null,
                'established_year' => $l1['established_year'] ?? null,
                'capacity' => $l1['capacity'] ?? null,
                'gps' => $this->parseGps($l1['gps'] ?? null),
                'maps_url' => $l1['google_maps_url'] ?? null,
                'phones' => array_values(array_filter([$l1['phone_primary'] ?? null, $l1['phone_secondary'] ?? null])),
                'email' => $l1['email'] ?? null,
                'socials' => array_filter([
                    'facebook' => $l1['facebook_url'] ?? null,
                    'instagram' => $l1['instagram_url'] ?? null,
                    'youtube' => $l1['youtube_url'] ?? null,
                    'tiktok' => $l1['tiktok_url'] ?? null,
                ]),
            ],
            'design' => [
                'package' => $project->design?->package_key ?? 'warisan_hijau',
                'tokens' => $design['tokens'],
                'fonts' => $design['fonts'],
                'icon_style' => $design['icon_style'],
                'layout' => $design['layout'],
                'islamic_elements' => $sections['step_2']['islamic_elements'] ?? [],
                'mood' => $sections['step_2']['mood'] ?? null,
            ],
            'pages' => $project->pages()->orderBy('sort')->get()
                ->map(fn ($p) => ['key' => $p->page_key, 'enabled' => (bool) $p->enabled, 'custom_name' => $p->custom_name])
                ->all(),
            'content' => $project->tier->isNgo() ? $this->ngoContent($l4) : [
                'sejarah' => $this->withOrigin($l4['sejarah'] ?? null),
                'ajk' => $l4['ajk'] ?? null,
                'services' => $this->services($l4),
                'quran_classes' => $l4['kelas_quran']['classes'] ?? [],
                'kuliah' => $l4['kuliah_mingguan']['sessions'] ?? [],
                'faq' => $l4['soalan_lazim']['faqs'] ?? [],
                'infaq' => $l4['infaq'] ?? null,
                'facilities' => $l4['fasiliti']['items'] ?? [],
                'visitor' => $l4['info_pelawat'] ?? null,
                'khairat' => $l4['khairat'] ?? null,
                'news_seed' => array_merge($l4['berita']['seed_items'] ?? [], $l4['pengumuman']['seed_items'] ?? []),
            ],
            'features' => [
                'payment' => ['gateway' => $l5['payment_gateway'] ?? null, 'status' => $l5['gateway_status'] ?? null],
                'cms' => $l5['cms_updater'] ?? null,
                'i18n' => (bool) ($l5['bilingual'] ?? $project->is_gov),
                'prayer' => $project->tier->isMosque()
                    ? ['zone' => $project->jakim_zone, 'show_countdown' => $l4['waktu_solat']['show_countdown'] ?? true]
                    : null,
                'live' => $l4['live_streaming'] ?? null,
                'wa_button' => ['enabled' => (bool) ($l5['whatsapp_button'] ?? false), 'number' => $l5['wa_number'] ?? null],
                'kariah' => ['mode' => $l5['kariah_system'] ?? null, 'url' => $l5['kariah_url'] ?? null],
                'flags' => [
                    'tv_display' => (bool) ($l5['tv_display'] ?? false),
                    'pwa' => (bool) ($l5['pwa'] ?? false),
                    'wa_broadcast' => (bool) ($l5['wa_broadcast'] ?? false),
                ],
            ],
            'assets' => $this->assets($project),
            'references' => [
                'liked' => $l7['liked_refs'] ?? [],
                'dislikes' => $l7['dislikes'] ?? null,
            ],
            'technical' => [
                'domain' => ['status' => $l8['domain_status'] ?? null, 'name' => $l8['domain_name'] ?? null, 'wishes' => $l8['domain_wishes'] ?? []],
                'legacy' => ['url' => $l8['existing_site'] ?? null, 'migrate' => (bool) ($l8['migrate_content'] ?? false)],
                'email' => $l8['official_email_status'] ?? null,
                'hosting' => $l8['hosting'] ?? null,
                'maintenance' => $l8['maintenance'] ?? null,
                'target_date' => $l8['target_live'] ?? null,
            ],
            'notes' => [
                'free_notes' => $l9['free_notes'] ?? null,
                'budget_hint' => $l9['budget_hint'] ?? null,
                'tweak_history' => $project->tweakRequests()->latest()->get()
                    ->map(fn ($t) => ['categories' => $t->categories, 'message' => $t->message])->all(),
            ],
            'ai_flags' => $this->aiFlags($l4),
        ];
    }

    private function services(array $l4): array
    {
        $out = [];
        foreach (['nikah', 'jenazah', 'tahlil_doa', 'khidmat_nasihat', 'sewa_dewan'] as $key) {
            if (! empty($l4[$key] ?? [])) {
                $out[] = ['key' => $key] + $l4[$key];
            }
        }

        return $out;
    }

    /** Blok content NGO/pertubuhan (Fasa 11) — struktur berlainan dari masjid. */
    private function ngoContent(array $l4): array
    {
        return [
            'profil' => $this->withOrigin($l4['profil'] ?? null),
            'ajk' => $l4['ajk'] ?? null,
            'programs' => $l4['program_utama']['programs'] ?? [],
            'volunteer' => $l4['sukarelawan'] ?? null,
            'membership' => $l4['keahlian'] ?? null,
            'derma' => $l4['derma'] ?? null,
            'faq' => $l4['soalan_lazim']['faqs'] ?? [],
            'news_seed' => array_merge(
                $l4['berita']['seed_items'] ?? [],
                $l4['pengumuman']['seed_items'] ?? [],
                $l4['program_akan_datang']['seed_items'] ?? [],
            ),
        ];
    }

    private function withOrigin(?array $data): ?array
    {
        if ($data === null) {
            return null;
        }
        $data['origin'] = ($data['mode'] ?? null) === 'butir_ringkas' ? 'ai' : 'human';

        return $data;
    }

    private function aiFlags(array $l4): array
    {
        $flags = [];
        foreach (['sejarah', 'perutusan', 'profil'] as $page) {
            if (($l4[$page]['mode'] ?? null) === 'butir_ringkas') {
                $flags[] = ['path' => "content.{$page}", 'reviewed_by_pic' => false];
            }
        }

        return $flags;
    }

    private function assets(Project $project): array
    {
        $counters = [];

        return $project->assets()->orderBy('kind')->orderBy('sort')->get()->map(function ($a) use (&$counters, $project) {
            $counters[$a->kind] = ($counters[$a->kind] ?? 0) + 1;
            $ext = pathinfo($a->path, PATHINFO_EXTENSION) ?: 'bin';
            $slug = Str::slug($project->short_name ?: $project->mosque_name) ?: 'masjid';

            return [
                'kind' => $a->kind,
                'file' => sprintf('assets/%s-%02d-%s.%s', $a->kind, $counters[$a->kind], $slug, $ext),
                'source_path' => $a->path,
                'caption' => $a->caption,
            ];
        })->all();
    }

    private function parseGps(?string $gps): ?array
    {
        if (blank($gps) || ! str_contains($gps, ',')) {
            return null;
        }
        [$lat, $lng] = array_map('trim', explode(',', $gps, 2));

        return is_numeric($lat) && is_numeric($lng) ? ['lat' => (float) $lat, 'lng' => (float) $lng] : null;
    }
}
