<?php

namespace App\Models;

use App\Enums\AiDriver;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

// §10 ai_providers — konfigurasi AI Sistem. api_key = encrypted cast (§11.5).
class AiProvider extends Model
{
    use HasFactory, HasUlids;

    protected $guarded = [];

    protected $hidden = ['api_key'];

    protected function casts(): array
    {
        return [
            'driver' => AiDriver::class,
            'api_key' => 'encrypted',
            'max_tokens' => 'integer',
            'temperature' => 'decimal:1',
            'timeout_s' => 'integer',
            'meta' => 'array',
            'is_active' => 'boolean',
            'is_default' => 'boolean',
        ];
    }

    public function generations(): HasMany
    {
        return $this->hasMany(Generation::class);
    }

    /** Provider default aktif (§8). */
    public static function default(): ?self
    {
        return static::query()->where('is_default', true)->where('is_active', true)->first();
    }
}
