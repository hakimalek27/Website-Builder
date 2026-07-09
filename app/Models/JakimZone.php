<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

// §10 jakim_zones — 59 zon (§16.A).
class JakimZone extends Model
{
    use HasFactory, HasUlids;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'verified_at' => 'datetime',
        ];
    }

    /** Paparan "KOD — label" (§6 L1). */
    public function displayLabel(): string
    {
        return "{$this->code} — {$this->districts_label}";
    }
}
