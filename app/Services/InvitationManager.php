<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\Invitation;
use App\Notifications\InvitationNotification;
use Illuminate\Support\Facades\Notification;

/**
 * Pengurusan jemputan PIC (§5.2 InvitationResource).
 *
 * Nota (R1 / §11.1): token plaintext TIDAK disimpan (hash SHA-256 sahaja), maka
 * "Hantar semula" & "Salin pautan" MESTI menjana token BAHARU (menggantikan hash
 * lama) — pautan lama menjadi tidak sah. Ini postur keselamatan yang betul.
 */
class InvitationManager
{
    /** Jana token baharu menggantikan hash lama. Pulangkan plaintext. */
    public function regenerate(Invitation $invitation): string
    {
        $token = Invitation::generateToken();

        $invitation->update([
            'token_hash' => Invitation::hashToken($token),
        ]);

        AuditLog::record('admin', null, 'invitation.regenerated', $invitation);

        return $token;
    }

    /** Jana token baharu + hantar notifikasi jemputan. Pulangkan plaintext. */
    public function resend(Invitation $invitation): string
    {
        $token = $this->regenerate($invitation);

        if (filled($invitation->pic_email)) {
            Notification::route('mail', $invitation->pic_email)
                ->notify(new InvitationNotification($invitation, $token, $invitation->project->mosque_name));
            AuditLog::record('admin', null, 'invitation.sent', $invitation, ['channel' => 'mail', 'resend' => true]);
        }

        return $token;
    }

    /** Lanjutkan tempoh luput (+N hari). Token/hash kekal — pautan sedia ada masih sah. */
    public function extend(Invitation $invitation, int $days): void
    {
        $base = $invitation->expires_at->isFuture() ? $invitation->expires_at : now();
        $invitation->update(['expires_at' => $base->copy()->addDays($days)]);

        AuditLog::record('admin', null, 'invitation.extended', $invitation, ['days' => $days]);
    }

    /** Batalkan jemputan serta-merta (revoke). */
    public function revoke(Invitation $invitation): void
    {
        $invitation->update(['revoked_at' => now()]);

        AuditLog::record('admin', null, 'invitation.revoked', $invitation);
    }

    public function urlFor(string $token): string
    {
        return url('/b/'.$token);
    }
}
