<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

// §10 design_packages — 5 pakej reka bentuk (§7.2).
class DesignPackage extends Model
{
    use HasFactory, HasUlids;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'tokens' => 'array',
            'fonts' => 'array',
            'icon_style' => 'array',
            'variants' => 'array',
            'preview_meta' => 'array',
            'is_active' => 'boolean',
        ];
    }
}
