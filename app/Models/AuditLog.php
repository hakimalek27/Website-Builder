<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;

/**
 * §10 audit_logs — jejak peristiwa. Peristiwa minimum disenaraikan §10.
 */
class AuditLog extends Model
{
    use HasUlids;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'meta' => 'array',
        ];
    }

    /**
     * Helper rakam peristiwa audit.
     *
     * @param  'admin'|'pic'|'system'  $actorType
     * @param  array<string, mixed>  $meta
     */
    public static function record(
        string $actorType,
        ?string $actorId,
        string $action,
        ?Model $subject = null,
        array $meta = [],
        ?string $ip = null,
    ): self {
        return static::create([
            'actor_type' => $actorType,
            'actor_id' => $actorId,
            'action' => $action,
            'subject_type' => $subject !== null ? $subject::class : null,
            'subject_id' => $subject?->getKey(),
            'meta' => $meta !== [] ? $meta : null,
            'ip' => $ip,
        ]);
    }
}
