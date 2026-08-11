<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    /**
     * Send a WhatsApp message via the configured gateway.
     * Returns false (silently) when the gateway is not configured yet.
     */
    public function send(string $phone, string $message): bool
    {
        $url = setting('whatsapp.api_url', '');

        if (! $url || ! $phone) {
            return false;
        }

        try {
            $response = Http::timeout(10)
                ->asForm()
                ->post($url, [
                    'token' => setting('whatsapp.api_token', ''),
                    'phone' => $phone,
                    'message' => $message,
                ]);

            return $response->successful();
        } catch (\Throwable $e) {
            Log::warning('WhatsApp send failed', ['phone' => $phone, 'error' => $e->getMessage()]);

            return false;
        }
    }
}