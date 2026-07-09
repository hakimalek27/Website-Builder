<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

// §10 settings — key/value; nilai boleh disulitkan (§5.3 Settings page).
class Setting extends Model
{
    use HasFactory, HasUlids;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'is_encrypted' => 'boolean',
        ];
    }

    /** Baca nilai setting (nyahsulit jika perlu). */
    public static function get(string $key, ?string $default = null): ?string
    {
        $setting = static::query()->where('key', $key)->first();

        if ($setting === null) {
            return $default;
        }

        if ($setting->is_encrypted && $setting->value !== null) {
            return Crypt::decryptString($setting->value);
        }

        return $setting->value;
    }

    /** Tulis nilai setting (sulit jika $encrypted). */
    public static function put(string $key, ?string $value, bool $encrypted = false): self
    {
        return static::updateOrCreate(
            ['key' => $key],
            [
                'value' => ($encrypted && $value !== null) ? Crypt::encryptString($value) : $value,
                'is_encrypted' => $encrypted,
            ],
        );
    }
}
