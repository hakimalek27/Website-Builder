<?php

namespace App\Services;

use App\Models\NotificationLog;
use App\Models\Setting;
use App\Support\Mask;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * §13 — adapter WhatsApp generik. POST {settings.whatsapp_gateway_url} JSON
 * {phone, message} + header X-Gateway-Secret. Timeout 10s. Sepadan gaya
 * wassap.wehdah.my. Melog ke notification_logs.
 */
class WhatsappGateway
{
    public function send(string $phone, string $message, ?string $projectId = null, string $event = 'whatsapp'): bool
    {
        $url = Setting::get('whatsapp_gateway_url');
        $secret = Setting::get('whatsapp_gateway_secret');

        if (blank($url)) {
            $this->log($projectId, $event, $phone, 'failed', 'Gateway URL tidak dikonfigurasi.');

            return false;
        }

        try {
            $response = Http::timeout(10)
                ->withHeaders(['X-Gateway-Secret' => (string) $secret])
                ->post($url, ['phone' => $phone, 'message' => $message]);

            $ok = $response->successful();
            $this->log($projectId, $event, $phone, $ok ? 'sent' : 'failed', $ok ? null : 'HTTP '.$response->status());

            return $ok;
        } catch (Throwable $e) {
            Log::warning('WhatsApp gateway ralat', ['recipient' => Mask::phone($phone), 'error' => $e->getMessage()]);
            $this->log($projectId, $event, $phone, 'failed', $e->getMessage());

            return false;
        }
    }

    private function log(?string $projectId, string $event, string $phone, string $status, ?string $error): void
    {
        NotificationLog::create([
            'project_id' => $projectId,
            'event' => $event,
            'channel' => 'whatsapp',
            'recipient' => $phone,
            'payload' => ['event' => $event],
            'status' => $status,
            'error' => $error,
            'sent_at' => now(),
        ]);
    }
}
