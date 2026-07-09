<?php

namespace App\Enums;

// Status lead — §10 leads.status.
enum LeadStatus: string
{
    case New = 'new';
    case Contacted = 'contacted';
    case Qualified = 'qualified';
    case Rejected = 'rejected';

    public function label(): string
    {
        return match ($this) {
            self::New => 'Baharu',
            self::Contacted => 'Dihubungi',
            self::Qualified => 'Layak',
            self::Rejected => 'Ditolak',
        };
    }
}
