<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppExtractor
{
    private const USER_AGENT = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0 Safari/537.36';

    /**
     * Visita la web de una clínica y extrae el primer número de WhatsApp que encuentre.
     * Devuelve los últimos 10 dígitos (formato MX) o null.
     */
    public function extractFromUrl(string $url): ?string
    {
        $url = $this->normalizeUrl($url);
        if (!$url) return null;

        try {
            $response = Http::withHeaders(['User-Agent' => self::USER_AGENT])
                ->timeout(12)
                ->withOptions(['allow_redirects' => true, 'verify' => false])
                ->get($url);
        } catch (\Throwable $e) {
            Log::info('WhatsAppExtractor: fetch failed', ['url' => $url, 'error' => $e->getMessage()]);
            return null;
        }

        if (!$response->successful()) {
            return null;
        }

        $html = $response->body();
        return $this->extractFromHtml($html);
    }

    public function extractFromHtml(string $html): ?string
    {
        $patterns = [
            '/wa\.me\/(?:\+?52)?1?(\d{10,13})/i',
            '/api\.whatsapp\.com\/send\?phone=(?:\+?52)?1?(\d{10,13})/i',
            '/web\.whatsapp\.com\/send\?phone=(?:\+?52)?1?(\d{10,13})/i',
            '/whatsapp[^0-9]{0,30}(\d{2,3}[\s\-]?\d{3,4}[\s\-]?\d{4})/i',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $html, $matches)) {
                $digits = preg_replace('/\D+/', '', $matches[1]);
                if (strlen($digits) >= 10) {
                    return substr($digits, -10);
                }
            }
        }

        return null;
    }

    private function normalizeUrl(?string $url): ?string
    {
        if (!$url) return null;
        $url = trim($url);
        if (!preg_match('/^https?:\/\//i', $url)) {
            $url = 'https://' . $url;
        }
        return filter_var($url, FILTER_VALIDATE_URL) ? $url : null;
    }
}
