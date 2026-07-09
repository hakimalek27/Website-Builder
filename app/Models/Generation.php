<?php

namespace App\Models;

use App\Enums\GenerationStatus;
use App\Enums\GenerationType;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

// §10 generations — setiap panggilan jana/tweak/render. Ledger kos §8.8.
class Generation extends Model
{
    use HasFactory, HasUlids;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'type' => GenerationType::class,
            'status' => GenerationStatus::class,
            'progress_step' => 'integer',
            'input_snapshot' => 'array',
            'output_json' => 'array',
            'tokens_in' => 'integer',
            'tokens_out' => 'integer',
            'cost_estimate' => 'decimal:4',
            'attempt' => 'integer',
            'started_at' => 'datetime',
            'finished_at' => 'datetime',
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function aiProvider(): BelongsTo
    {
        return $this->belongsTo(AiProvider::class);
    }
}
