<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

/**
 * Status projek — state machine §4.2. transitionTo() menguatkuasakan laluan ini.
 */
enum ProjectStatus: string implements HasColor, HasLabel
{
    case Invited = 'invited';
    case InProgress = 'in_progress';
    case Submitted = 'submitted';
    case DraftReady = 'draft_ready';
    case Approved = 'approved';
    case HandoverExported = 'handover_exported';
    case InBuild = 'in_build';
    case InReview = 'in_review';
    case Live = 'live';
    case Archived = 'archived';
    case Cancelled = 'cancelled';
    case Expired = 'expired';

    /**
     * Laluan transisi SAH sahaja (§4.2). Sebarang laluan lain = exception.
     *
     * @return array<int, self>
     */
    public function allowedTransitions(): array
    {
        return match ($this) {
            self::Invited => [self::InProgress, self::Cancelled, self::Expired],
            self::InProgress => [self::Submitted, self::Cancelled, self::Expired],
            // §Fasa 16 — mod templat: Submitted → InBuild terus (tiada draf AI/kelulusan).
            self::Submitted => [self::DraftReady, self::InBuild, self::Cancelled],
            // Tweak AI berjaya kekalkan draft_ready (versi generation baharu).
            self::DraftReady => [self::DraftReady, self::Approved, self::Cancelled],
            self::Approved => [self::HandoverExported],
            self::HandoverExported => [self::InBuild],
            self::InBuild => [self::InReview],
            self::InReview => [self::Live],
            self::Live => [self::Archived],
            // Admin boleh lanjut token → projek luput kembali aktif.
            self::Expired => [self::Invited, self::InProgress],
            self::Archived, self::Cancelled => [],
        };
    }

    public function canTransitionTo(self $to): bool
    {
        return in_array($to, $this->allowedTransitions(), true);
    }

    /**
     * TITIK BEKU (§4.2): selepas approved, wizard baca-sahaja untuk PIC.
     */
    public function isFrozen(): bool
    {
        return match ($this) {
            self::Approved, self::HandoverExported, self::InBuild,
            self::InReview, self::Live, self::Archived => true,
            default => false,
        };
    }

    /** Label BM (§16.B). */
    public function label(): string
    {
        return match ($this) {
            self::Invited => 'Dijemput',
            self::InProgress => 'Sedang Diisi',
            self::Submitted => 'Telah Dihantar',
            self::DraftReady => 'Draf Sedia',
            self::Approved => 'Diluluskan',
            self::HandoverExported => 'Pakej Dieksport',
            self::InBuild => 'Dalam Pembinaan',
            self::InReview => 'Semakan Akhir',
            self::Live => 'Live',
            self::Archived => 'Diarkib',
            self::Cancelled => 'Dibatalkan',
            self::Expired => 'Luput',
        };
    }

    /** Filament HasLabel — badge lencana guna label BM. */
    public function getLabel(): string
    {
        return $this->label();
    }

    /** Warna lencana Filament (§16.B). */
    public function color(): string
    {
        return match ($this) {
            self::Invited => 'gray',
            self::InProgress => 'info',
            self::Submitted => 'warning',
            self::DraftReady => 'info',
            self::Approved => 'success',
            self::HandoverExported => 'success',
            self::InBuild => 'primary',
            self::InReview => 'warning',
            self::Live => 'success',
            self::Archived => 'gray',
            self::Cancelled, self::Expired => 'danger',
        };
    }

    /** Filament HasColor. */
    public function getColor(): string
    {
        return $this->color();
    }
}
