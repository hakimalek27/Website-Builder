<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

// Tier organisasi — §6 Langkah 0. Masjid/surau + NGO/pertubuhan (Fasa 11).
enum Tier: string implements HasLabel
{
    case SurauRingkas = 'surau_ringkas';
    case MasjidKariah = 'masjid_kariah';
    case MasjidBesar = 'masjid_besar';
    case NgoKomuniti = 'ngo_komuniti';
    case NgoPenuh = 'ngo_penuh';

    public function label(): string
    {
        return match ($this) {
            self::SurauRingkas => 'Surau / Masjid Ringkas',
            self::MasjidKariah => 'Masjid Kariah',
            self::MasjidBesar => 'Masjid Besar',
            self::NgoKomuniti => 'Pertubuhan / NGO (Komuniti)',
            self::NgoPenuh => 'Pertubuhan / NGO (Penuh)',
        };
    }

    public function isNgo(): bool
    {
        return $this === self::NgoKomuniti || $this === self::NgoPenuh;
    }

    public function isMosque(): bool
    {
        return ! $this->isNgo();
    }

    /** Kata nama organisasi (untuk label dinamik wizard). */
    public function orgNoun(): string
    {
        return match ($this) {
            self::SurauRingkas => 'surau',
            self::MasjidKariah, self::MasjidBesar => 'masjid',
            self::NgoKomuniti, self::NgoPenuh => 'pertubuhan',
        };
    }

    /** @return array<int, string> */
    public static function values(): array
    {
        return array_map(fn (self $t) => $t->value, self::cases());
    }

    /** Filament HasLabel. */
    public function getLabel(): string
    {
        return $this->label();
    }
}
