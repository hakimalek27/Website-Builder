<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

// §10 project_design — pilihan reka bentuk PIC.
class ProjectDesign extends Model
{
    use HasFactory, HasUlids;

    protected $table = 'project_design';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'overrides' => 'array',
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }
}
