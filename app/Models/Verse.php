<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * §10 verse_library — teks Arab HANYA dari sini (§9.1/§9.2), direkod manusia.
 */
class Verse extends Model
{
    use HasFactory, HasUlids;

    protected $table = 'verse_library';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public static function activeSeed(): ?self
    {
        return static::query()->where('is_active', true)->first();
    }
}
