<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

// Tier organisasi — §6 Langkah 0.
enum Tier: string implements HasLabel
{
    case SurauRingkas = 'surau_ringkas';
    case MasjidKariah = 'masjid_kariah';
    case MasjidBesar = 'masjid_besar';

    public function label(): string
    {
        return match ($this) {
            self::SurauRingkas => 'Surau / Masjid Ringkas',
            self::MasjidKariah => 'Masjid Kariah',
            self::MasjidBesar => 'Masjid Besar',
        };
    }

    /** Filament HasLabel. */
    public function getLabel(): string
    {
        return $this->label();
    }
}
