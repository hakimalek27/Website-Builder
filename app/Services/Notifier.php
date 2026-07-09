<?php

namespace App\Services;

use App\Jobs\SendWhatsappJob;
use App\Mail\ApprovedMail;
use App\Mail\GenerationFailedMail;
use App\Mail\SubmittedMail;
use App\Models\Generation;
use App\Models\NotificationLog;
use App\Models\Project;
use Illuminate\Contracts\Mail\Mailable;
use Illuminate\Support\Facades\Mail;

/**
 * §13 — 9 event notifikasi. Saluran WA melalui gateway (queued + fallback mail);
 * mail melalui queue. Setiap hantaran direkod dalam notification_logs.
 */
class Notifier
{
    public function __construct(private WhatsappGateway $gateway) {}

    private function adminEmail(): ?string
    {
        return config('reka.admin_notify_email');
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

    private function adminPhoneOrNull(): ?string
    {
        // Admin phone tidak disimpan berasingan; guna null (mail sahaja).
        return null;
    }
}
