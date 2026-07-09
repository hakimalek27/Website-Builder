<?php

namespace App\Console\Commands;

use App\Enums\GenerationStatus;
use App\Enums\GenerationType;
use App\Enums\ProjectStatus;
use App\Models\Lead;
use App\Models\Note;
use App\Models\Project;
use App\Models\ProjectSection;
use App\Services\DraftRenderer;
use App\Services\LeadQualifier;
use Illuminate\Console\Command;

/**
 * Jana sesi PIC demo lengkap (projek + token + draf sebenar) untuk ujian smoke Playwright.
 * Idempoten: buang demo terdahulu (penanda e-mel) sebelum cipta yang baharu.
 * Output JSON {token, generation} ke stdout supaya boleh dibaca oleh global-setup.
 *
 * BUKAN untuk produksi — hanya alat pembangunan/ujian.
 */
class DemoTokenCommand extends Command
{
    protected $signature = 'reka:demo-token';

    protected $description = 'Jana sesi PIC demo (token + draf) untuk ujian smoke Playwright';

    private const MARKER_EMAIL = 'demo-playwright@reka.test';

    public function handle(DraftRenderer $renderer): int
    {
        // 1. Bersih demo terdahulu (best-effort).
        try {
            $leadIds = Lead::where('pic_email', self::MARKER_EMAIL)->pluck('id');
            Project::whereIn('lead_id', $leadIds)->get()->each->delete();
            Lead::whereIn('id', $leadIds)->delete();
        } catch (\Throwable $e) {
            // abaikan — cipta baharu tetap diteruskan
        }

        // 2. Lead → qualify → projek + token.
        $lead = Lead::create([
            'mosque_name' => 'Masjid Al-Hidayah Demo',
            'state' => 'W.P. Kuala Lumpur',
            'pic_name' => 'Ustaz Kamal',
            'pic_phone' => '0129876543',
            'pic_email' => self::MARKER_EMAIL,
        ]);

        $result = app(LeadQualifier::class)->qualify($lead, self::MARKER_EMAIL, 30, 3);
        /** @var Project $project */
        $project = $result['project'];
        $token = $result['token'];
        $project->update(['jakim_zone' => 'WLY01']);

        // 3. Isi semua langkah wajib (skor 100%) + hidupkan halaman untuk render draf.
        $sort = 0;
        foreach (['utama', 'hubungi', 'infaq'] as $pageKey) {
            $project->pages()->updateOrCreate(['page_key' => $pageKey], ['enabled' => true, 'sort' => $sort++]);
        }
        $sections = [
            'step_0' => ['tier' => 'masjid_kariah'],
            'step_1' => ['official_name' => 'Masjid Al-Hidayah', 'address_line1' => 'Jalan Melawati 3', 'postcode' => '53100', 'city' => 'Kuala Lumpur', 'state' => 'W.P. Kuala Lumpur', 'jakim_zone' => 'WLY01', 'authority' => 'MAIWP', 'gps' => '3.19, 101.73', 'phone_primary' => '0341234567', 'email' => 'masjid@demo.test', 'logo_status' => 'teks_sahaja'],
            'step_2' => ['mood' => 'mesra_keluarga', 'design_package' => 'warisan_hijau'],
            'step_4' => ['panels' => ['hubungi' => ['form_recipient_email' => 'masjid@demo.test'], 'infaq' => ['bank_name' => 'Maybank', 'bank_account' => '1234567890', 'account_holder' => 'Masjid Al-Hidayah', 'categories' => [['title' => 'Infaq Am']]]]],
            'step_5' => ['cms_updater' => 'urus_azan', 'payment_gateway' => 'manual_bank'],
            'step_6' => ['hero_mode' => 'stok_sementara'],
            'step_8' => ['domain_status' => 'belum'],
            'step_9' => ['pic_name' => 'Ustaz Kamal', 'pic_position' => 'Setiausaha', 'pic_phone' => '0129876543', 'consent_pdpa' => true, 'declare_truth_authority' => true],
        ];
        foreach ($sections as $key => $data) {
            ProjectSection::updateOrCreate(['project_id' => $project->id, 'section_key' => $key], ['data' => $data]);
        }

        // 4. Draf sebenar (render + simpan) supaya halaman P5/P6 boleh dipapar.
        $content = [
            'meta' => ['title' => 'Masjid Al-Hidayah', 'description' => 'Laman rasmi Masjid Al-Hidayah.'],
            'hero' => ['eyebrow' => 'Selamat Datang', 'headline' => 'Masjid Al-Hidayah', 'subheadline' => 'Memakmurkan masjid bersama komuniti.', 'cta_primary_label' => 'Infaq', 'cta_secondary_label' => 'Hubungi'],
            'about' => ['heading' => 'Tentang Kami', 'paragraphs' => ['Masjid ini berkhidmat untuk komuniti setempat.'], 'stats' => [['label' => 'Ditubuhkan', 'value' => '1987']]],
            'footer_description' => 'Masjid Al-Hidayah — memakmurkan syiar Islam.',
        ];
        $gen = $project->generations()->create([
            'type' => GenerationType::Initial,
            'status' => GenerationStatus::Succeeded,
            'created_by' => 'pic',
            'output_json' => $content,
            'progress_step' => 7,
        ]);
        $gen->update(['rendered_path' => $renderer->renderAndStore($project, $gen, $content, 1)]);

        // 5. Status DraftReady + satu nota admin (thread status).
        $project->update(['status' => ProjectStatus::DraftReady]);
        Note::create([
            'project_id' => $project->id, 'author' => 'admin', 'author_name' => 'Admin REKA',
            'kind' => 'general', 'body' => 'Terima kasih! Draf pertama anda telah dijana — sila semak.',
        ]);

        $this->line(json_encode(['token' => $token, 'generation' => $gen->id], JSON_UNESCAPED_SLASHES));

        return self::SUCCESS;
    }
}
