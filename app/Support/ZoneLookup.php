<?php

namespace App\Support;

use App\Models\JakimZone;

/**
 * Tapis zon JAKIM ikut negeri (§6 L1). 3 WP dropdown → zon "Wilayah Persekutuan".
 */
class ZoneLookup
{
    /**
     * @return array<string, string> code => "CODE — label"
     */
    public static function forState(?string $state): array
    {
        if (blank($state)) {
            return [];
        }

        $zoneState = config('reka.state_to_zone_state')[$state] ?? $state;

        return JakimZone::query()
            ->where('state', $zoneState)
            ->orderBy('code')
            ->get()
            ->mapWithKeys(fn (JakimZone $z) => [$z->code => $z->displayLabel()])
            ->all();
    }
}
