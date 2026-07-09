<?php

namespace App\Models;

use App\Enums\ProjectStatus;
use App\Enums\Tier;
use App\Exceptions\InvalidStatusTransitionException;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * §10 projects — satu masjid = satu project. Status state machine §4.2.
 */
class Project extends Model
{
    use HasFactory, HasUlids, SoftDeletes;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'tier' => Tier::class,
            'status' => ProjectStatus::class,
            'is_gov' => 'boolean',
            'quota_ai_total' => 'integer',
            'quota_ai_used' => 'integer',
            'quota_design_used' => 'integer',
            'submitted_at' => 'datetime',
            'approved_at' => 'datetime',
        ];
    }

    /**
     * Transisi status — HANYA laluan sah §4.2. Laluan lain → exception + audit.
     *
     * @param  array<string, mixed>  $meta
     */
    public function transitionTo(ProjectStatus $to, string $actorType = 'system', ?string $actorId = null, array $meta = []): void
    {
        $from = $this->status;

        if (! $from->canTransitionTo($to)) {
            AuditLog::record($actorType, $actorId, 'project.status_change_rejected', $this, [
                'from' => $from->value,
                'to' => $to->value,
            ]);

            throw new InvalidStatusTransitionException($from, $to);
        }

        $this->status = $to;

        if ($to === ProjectStatus::Submitted && $this->submitted_at === null) {
            $this->submitted_at = now();
        }
        if ($to === ProjectStatus::Approved && $this->approved_at === null) {
            $this->approved_at = now();
        }

        $this->save();

        AuditLog::record($actorType, $actorId, 'project.status_changed', $this, [
            'from' => $from->value,
            'to' => $to->value,
        ] + $meta);
    }

    public function isFrozen(): bool
    {
        return $this->status->isFrozen();
    }

    public function remainingAiQuota(): int
    {
        return max(0, $this->quota_ai_total - $this->quota_ai_used);
    }

    /** Top-up kuota AI (+N) oleh admin — dengan audit (§8.7). */
    public function topUpAiQuota(int $amount, ?string $actorId = null): void
    {
        $this->increment('quota_ai_total', $amount);

        AuditLog::record('admin', $actorId, 'quota.topup', $this, ['amount' => $amount]);
    }

    // --- Relationships ---

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    public function invitations(): HasMany
    {
        return $this->hasMany(Invitation::class);
    }

    public function invitation(): HasOne
    {
        return $this->hasOne(Invitation::class)->latestOfMany();
    }

    public function sections(): HasMany
    {
        return $this->hasMany(ProjectSection::class);
    }

    public function pages(): HasMany
    {
        return $this->hasMany(ProjectPage::class);
    }

    public function assets(): HasMany
    {
        return $this->hasMany(Asset::class);
    }

    public function design(): HasOne
    {
        return $this->hasOne(ProjectDesign::class);
    }

    public function generations(): HasMany
    {
        return $this->hasMany(Generation::class);
    }

    public function tweakRequests(): HasMany
    {
        return $this->hasMany(TweakRequest::class);
    }

    public function approval(): HasOne
    {
        return $this->hasOne(Approval::class);
    }

    public function handoverExports(): HasMany
    {
        return $this->hasMany(HandoverExport::class);
    }

    public function notes(): HasMany
    {
        return $this->hasMany(Note::class);
    }
}
