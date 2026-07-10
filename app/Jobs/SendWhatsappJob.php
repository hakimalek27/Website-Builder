<?php

namespace App\Jobs;

use App\Services\WhatsappGateway;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Mail\Mailable;
use Illuminate\Contracts\Queue\ShouldBeEncrypted;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

/**
 * §13 — hantar WhatsApp melalui queue (tries=3, backoff=60). Kegagalan muktamad →
 * fallback mail + log (gateway sudah log). TIDAK menyekat aliran utama.
 */
class SendWhatsappJob implements ShouldBeEncrypted, ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $backoff = 60;

    public function __construct(
        public string $phone,
        public string $message,
        public ?string $projectId,
        public string $event,
        public ?Mailable $fallbackMail = null,
        public ?string $fallbackTo = null,
    ) {}

    public function handle(WhatsappGateway $gateway): void
    {
        if ($gateway->send($this->phone, $this->message, $this->projectId, $this->event)) {
            return;
        }

        // Percubaan ini gagal — retry sehingga habis tries.
        if ($this->attempts() < $this->tries) {
            $this->release($this->backoff);

            return;
        }

        // Kegagalan muktamad → fallback mail.
        $this->sendFallback();
    }

    public function failed(): void
    {
        $this->sendFallback();
    }

    private function sendFallback(): void
    {
        if ($this->fallbackMail !== null && filled($this->fallbackTo)) {
            Mail::to($this->fallbackTo)->queue($this->fallbackMail);
        }
    }
}
