<?php

namespace App\Services;

use App\Models\NotificationLog;
use App\Models\Setting;
use App\Support\Mask;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * §13 — adapter WhatsApp untuk gateway wassap.wehdah.my (produk Wehdah Solution).
 * POST {base}/v1/messages/send, header X-API-Key, body {to, message, session_id?}.
 * Kejayaan = HTTP 2xx DAN response.success === true. Melog ke notification_logs.
 * Tandatangan send() KEKAL supaya SendWhatsappJob tidak berubah.
 */
class WhatsappGateway
{
    public function send(string $phone, string $message, ?string $projectId = null, string $event = 'whatsapp'): bool
    {
        $base = Setting::get('whatsapp_gateway_url');
        $apiKey = Setting::get('whatsapp_api_key');
        $to = $this->normalize($phone);

        if (blank($base)) {
            $this->log($projectId, $event, $to, 'failed', 'Gateway URL tidak dikonfigurasi.');

            return false;
        }

        $payload = ['to' => $to, 'message' => $message];
        if (filled($sessionId = Setting::get('whatsapp_session_id'))) {
            $payload['session_id'] = $sessionId;   // peranti penghantar (kosong = round-robin gateway)
        }

        try {
            $response = Http::timeout(10)
                ->withHeaders(['X-API-Key' => (string) $apiKey])
                ->post(rtrim($base, '/').'/v1/messages/send', $payload);

            $ok = $response->successful() && $response->json('success') === true;
            $this->log($projectId, $event, $to, $ok ? 'sent' : 'failed', $ok ? null : 'HTTP '.$response->status(), $response->json('data.message_id'));

            return $ok;
        } catch (Throwable $e) {
            Log::warning('WhatsApp gateway ralat', ['recipient' => Mask::phone($to), 'error' => $e->getMessage()]);
            $this->log($projectId, $event, $to, 'failed', $e->getMessage());

            return false;
        }
    }

    /** Normalkan nombor Malaysia ke msisdn (01x → 601x). */
    private function normalize(string $phone): string
    {
        $digits = preg_replace('/\D+/', '', $phone) ?: $phone;

        return str_starts_with($digits, '0') ? '6'.$digits : $digits;
    }

    private function log(?string $projectId, string $event, string $phone, string $status, ?string $error, ?string $messageId = null): void
    {
        NotificationLog::create([
            'project_id' => $projectId,
            'event' => $event,
            'channel' => 'whatsapp',
            'recipient' => $phone,
            'payload' => array_filter(['event' => $event, 'message_id' => $messageId]),
            'status' => $status,
            'error' => $error,
            'sent_at' => now(),
        ]);
    }
}
