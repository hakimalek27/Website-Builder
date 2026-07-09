<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

// §10 invitations — token PIC (§11.1). Plaintext token TIDAK disimpan; hash SHA-256 sahaja.
class Invitation extends Model
{
    use HasFactory, HasUlids;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'revoked_at' => 'datetime',
            'opened_at' => 'datetime',
            'last_active_at' => 'datetime',
            'opens_count' => 'integer',
        ];
    }

    /** Jana token 40-aksara (§11.1). */
    public static function generateToken(): string
    {
        return Str::random(40);
    }

    /** Hash SHA-256 token untuk simpanan. */
    public static function hashToken(string $token): string
    {
        return hash('sha256', $token);
    }

    public function isRevoked(): bool
    {
        return $this->revoked_at !== null;
    }

    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    public function isUsable(): bool
    {
        return ! $this->isRevoked() && ! $this->isExpired();
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }
}
