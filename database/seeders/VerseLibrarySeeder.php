<?php

namespace Database\Seeders;

use App\Models\Verse;
use Illuminate\Database\Seeder;

/**
 * Seed MVP: SATU entri — Surah At-Taubah: 18 (§9.2).
 *
 * ⚠️ R6 / GUARDRAIL AGAMA: teks Arab MESTI disalin VERBATIM dari sumber yang
 * §9.2 nyatakan (docs/build-prompts.md repo mamkl, PROMPT 3). Claude Code TIADA
 * akses kepada sumber itu, maka arabic_text diisi placeholder
 * 'PENDING_MANUAL_ENTRY' — Azan WAJIB gantikan dengan teks Arab sebenar dari
 * mushaf/sumber muktamad SEBELUM go-live. JANGAN taip ayat Quran dari ingatan.
 * Renderer draf (§8.5) mesti langkau paparan Arab jika masih placeholder.
 */
class VerseLibrarySeeder extends Seeder
{
    public function run(): void
    {
        Verse::updateOrCreate(
            ['source_label' => 'Surah At-Taubah: 18'],
            [
                'arabic_text' => 'PENDING_MANUAL_ENTRY',
                'translation_bm' => 'Hanyalah yang memakmurkan masjid-masjid Allah ialah orang yang beriman kepada Allah dan Hari Akhirat…',
                'verified_by' => 'PENDING_MANUAL_ENTRY',
                'is_active' => true,
            ],
        );
    }
}
