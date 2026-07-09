<?php

namespace App\Notifications;

use App\Models\Invitation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Jemputan PIC (§13 invitation.sent). Dihantar melalui antara muka Notification
 * supaya saluran WhatsApp mudah ditambah pada Fasa 9 TANPA mengubah kod pemanggil.
 */
class InvitationNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Invitation $invitation,
        public string $token,
        public string $mosqueName,
    ) {}

    /**
     * Saluran. MVP Fasa 2: mail sahaja. Fasa 9 tambah 'whatsapp'.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function link(): string
    {
        return url('/b/'.$this->token);
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Jemputan membina laman web '.$this->mosqueName)
            ->greeting('Assalamualaikum & Salam Sejahtera')
            ->line("Anda dijemput untuk mengisi maklumat bagi laman web {$this->mosqueName}.")
            ->line('Sila simpan pautan ini — ia adalah akses anda ke borang (jangan kongsi kepada pihak yang tidak berkenaan).')
            ->action('Buka Borang', $this->link())
            ->line('Pautan ini akan luput pada '.$this->invitation->expires_at->format('d/m/Y').'.')
            ->salutation('Terima kasih.');
    }

    /**
     * Payload untuk log/saluran lain (§13).
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'event' => 'invitation.sent',
            'link' => $this->link(),
            'mosque_name' => $this->mosqueName,
        ];
    }
}
