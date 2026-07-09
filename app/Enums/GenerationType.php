<?php

namespace App\Enums;

// Jenis penjanaan — §10 generations.type.
enum GenerationType: string
{
    case Initial = 'initial';
    case ContentTweak = 'content_tweak';
    case DesignRender = 'design_render';

    /** Jenis yang menggunakan kuota AI (§8.7). design_render TIDAK guna AI. */
    public function usesAiQuota(): bool
    {
        return $this === self::Initial || $this === self::ContentTweak;
    }
}
