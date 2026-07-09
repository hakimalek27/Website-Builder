<?php

namespace App\Enums;

// Status penjanaan — §4.3.
enum GenerationStatus: string
{
    case Queued = 'queued';
    case Processing = 'processing';
    case Succeeded = 'succeeded';
    case Failed = 'failed';

    /** Baris queued|processing = kunci penjanaan (§4.3). */
    public function isActive(): bool
    {
        return $this === self::Queued || $this === self::Processing;
    }
}
