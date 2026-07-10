<?php

namespace App\Models;

use App\Enums\AiDriver;
use App\Enums\AiVendor;
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
            'vendor' => AiVendor::class,
            'driver' => AiDriver::class,
            'api_key' => 'encrypted',
            'max_tokens' => 'integer',
            'temperature' => 'decimal:1',
            'timeout_s' => 'integer',
            'meta' => 'array',
            'is_active' => 'boolean',
            'is_default' => 'boolean',
            'is_prompt_engineer' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::saved(function (self $provider) {
            // Hanya satu provider boleh jadi default (§5.3).
            if ($provider->is_default) {
                static::query()->whereKeyNot($provider->getKey())
                    ->where('is_default', true)
                    ->update(['is_default' => false]);
            }
            // Hanya satu provider boleh jadi Jurutera Prompt (§Fasa 13).
            if ($provider->is_prompt_engineer) {
                static::query()->whereKeyNot($provider->getKey())
                    ->where('is_prompt_engineer', true)
                    ->update(['is_prompt_engineer' => false]);
            }
        });
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

    /** Provider Jurutera Prompt aktif — Peringkat 1 saluran HTML (§Fasa 13). */
    public static function promptEngineer(): ?self
    {
        return static::query()->where('is_prompt_engineer', true)->where('is_active', true)->first();
    }
}
