<?php

namespace App\Services;

use App\Jobs\SendWhatsappJob;
use App\Mail\AdminAlertMail;
use App\Mail\ApprovedMail;
use App\Mail\GenerationFailedMail;
use App\Mail\NewLeadMail;
use App\Mail\SubmittedMail;
use App\Models\Generation;
use App\Models\Lead;
use App\Models\Note;
use App\Models\NotificationLog;
use App\Models\Project;
use App\Models\Setting;
use Illuminate\Contracts\Mail\Mailable;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

/**
 * §13 — event notifikasi (9 asal + 3 Fasa 11). Saluran WA melalui gateway
 * (queued + fallback mail); mail melalui queue. Direkod dalam notification_logs.
 */
class Notifier
{
    public function __construct(private WhatsappGateway $gateway) {}

    private function adminEmail(): ?string
    {
        return Setting::get('admin_notify_email') ?: config('reka.admin_notify_email');
    }

    private function picPhone(Project $project): ?string
    {
        return $project->invitation?->pic_phone;
    }

    private function picEmail(Project $project): ?string
    {
        return $project->invitation?->pic_email;
    }

    /** Hantar WA (dengan fallback mail) — direkod. */
    private function whatsapp(?string $phone, string $message, ?Project $project, string $event, ?Mailable $fallback = null, ?string $fallbackTo = null): void
    {
        if (blank($phone)) {
            return;
        }
        SendWhatsappJob::dispatch($phone, $message, $project?->id, $event, $fallback, $fallbackTo);
    }

    /** Hantar mail — direkod. */
    private function mail(?string $to, Mailable $mailable, ?Project $project, string $event): void
    {
        if (blank($to)) {
            return;
        }
        Mail::to($to)->queue($mailable);
        $this->record($project, $event, 'mail', $to);
    }

    private function record(?Project $project, string $event, string $channel, string $recipient, string $status = 'sent'): void
    {
        NotificationLog::create([
            'project_id' => $project?->id,
            'event' => $event,
            'channel' => $channel,
            'recipient' => $recipient,
            'payload' => ['event' => $event],
            'status' => $status,
            'sent_at' => now(),
        ]);
    }

    // --- 9 event §13 ---

    public function invitationSent(Project $project, string $link): void
    {
        $this->whatsapp($this->picPhone($project), "Salam. Borang laman {$project->mosque_name}: {$link} (simpan pautan ini).", $project, 'invitation.sent');
    }

    public function wizardReminder(Project $project, string $link): void
    {
        $this->whatsapp($this->picPhone($project), "Borang laman {$project->mosque_name} menunggu — sambung di sini: {$link}", $project, 'wizard.reminder');
    }

    public function submitted(Project $project): void
    {
        $this->mail($this->adminEmail(), new SubmittedMail($project), $project, 'submitted');
        // Fasa 11: makluman WA segera kepada admin (60189030363).
        $msg = "REKA: {$project->mosque_name} telah menghantar borang — draf dijana automatik.";
        $this->whatsapp($this->adminPhoneOrNull(), $msg, $project, 'submitted', new AdminAlertMail('Borang dihantar', $msg), $this->adminEmail());
    }

    public function generationSucceeded(Project $project, Generation $generation, string $link): void
    {
        $this->whatsapp($this->picPhone($project), "Draf laman {$project->mosque_name} sedia — lihat & beri maklum balas: {$link}", $project, 'generation.succeeded');
        if (filled($this->picEmail($project))) {
            $this->record($project, 'generation.succeeded', 'mail', $this->picEmail($project));
        }
    }

    public function generationFailed(Generation $generation): void
    {
        $this->mail($this->adminEmail(), new GenerationFailedMail($generation), $generation->project, 'generation.failed');
        // §Fasa 13: makluman WA segera kepada admin (draf gagal — mungkin perlu betulkan Penyedia AI).
        $name = $generation->project?->mosque_name ?? 'projek';
        $msg = "REKA: Penjanaan draf {$name} GAGAL — ".Str::limit((string) $generation->error, 100).'. Semak Penyedia AI.';
        $this->whatsapp($this->adminPhoneOrNull(), $msg, $generation->project, 'generation.failed',
            new AdminAlertMail('Penjanaan gagal', $msg), $this->adminEmail());
    }

    public function quotaExhausted(Project $project, string $note): void
    {
        $this->whatsapp($this->adminPhoneOrNull(), "Nota kuota dari {$project->mosque_name}: {$note}", $project, 'quota.exhausted_note');
        $this->mail($this->adminEmail(), new SubmittedMail($project), $project, 'quota.exhausted_note');
    }

    public function approved(Project $project): void
    {
        $this->mail($this->adminEmail(), new ApprovedMail($project), $project, 'approved');
    }

    public function buildUpdated(Project $project, string $statusLabel, string $link): void
    {
        $this->whatsapp($this->picPhone($project), "Kemas kini {$project->mosque_name}: {$statusLabel}. Lihat: {$link}", $project, 'status.build_updated');
    }

    public function tokenExpiring(Project $project): void
    {
        $this->mail($this->adminEmail(), new SubmittedMail($project), $project, 'token.expiring');
    }

    // --- Event Fasa 11 (WA → admin 60189030363) ---

    /** Lead baharu dari borang minat (§5.1). */
    public function leadReceived(Lead $lead): void
    {
        $msg = "REKA: Lead baharu — {$lead->mosque_name} ({$lead->state}).";
        $this->whatsapp($this->adminPhoneOrNull(), $msg, null, 'lead.received', new AdminAlertMail('Lead baharu', $msg), $this->adminEmail());
        $this->mail($this->adminEmail(), new NewLeadMail($lead), null, 'lead.received');
    }

    /** Nota/soalan baharu dari PIC (§5.2 P11). */
    public function noteAdded(Project $project, Note $note): void
    {
        $msg = "REKA: Nota baharu dari {$project->mosque_name}: ".Str::limit($note->body, 80);
        $this->whatsapp($this->adminPhoneOrNull(), $msg, $project, 'note.added', new AdminAlertMail('Nota PIC', $msg), $this->adminEmail());
    }

    /** Balasan admin kepada nota PIC (§5.2 P11, Fasa 12 W2) → WA kepada PIC. */
    public function adminReplied(Project $project, Note $note): void
    {
        $msg = "REKA: Balasan admin untuk {$project->mosque_name} — ".Str::limit($note->body, 120)
            .'. Lihat penuh di menu "Status & Nota" pautan borang anda.';
        $this->whatsapp($this->picPhone($project), $msg, $project, 'note.admin_replied',
            new AdminAlertMail('Balasan admin', $msg), $this->picEmail($project));
    }

    private function adminPhoneOrNull(): ?string
    {
        return Setting::get('admin_notify_phone') ?: null;
    }
}
