<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

// §10 tweak_requests — permohonan tweak PIC (§5.2 P7/P8).
class TweakRequest extends Model
{
    use HasFactory, HasUlids;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'categories' => 'array',
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function baseGeneration(): BelongsTo
    {
        return $this->belongsTo(Generation::class, 'base_generation_id');
    }

    public function resultGeneration(): BelongsTo
    {
        return $this->belongsTo(Generation::class, 'result_generation_id');
    }
}
