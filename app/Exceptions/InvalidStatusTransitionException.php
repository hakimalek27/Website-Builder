<?php

namespace App\Exceptions;

use App\Enums\ProjectStatus;
use RuntimeException;

// Dilempar bila transisi status projek di luar laluan sah §4.2.
class InvalidStatusTransitionException extends RuntimeException
{
    public function __construct(
        public readonly ProjectStatus $from,
        public readonly ProjectStatus $to,
    ) {
        parent::__construct("Transisi status tidak sah: {$from->value} → {$to->value}");
    }
}
