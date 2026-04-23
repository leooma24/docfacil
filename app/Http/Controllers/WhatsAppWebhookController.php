<?php

namespace App\Http\Controllers;

use App\Services\WhatsAppBotService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WhatsAppWebhookController extends Controller
{
    /**
     * Meta webhook verification (GET request on setup).
     * Fails closed if WHATSAPP_VERIFY_TOKEN no esta configurado — no hay default.
     */
    public function verify(Request $request)
    {
        $mode = $request->query('hub_mode');
        $token = $request->query('hub_verify_token');
        $challenge = $request->query('hub_challenge');

        $expectedToken = config('services.whatsapp.verify_token');

        if (empty($expectedToken)) {
            Log::error('WhatsApp webhook: WHATSAPP_VERIFY_TOKEN no configurado');
            return response('Server misconfigured', 500);
        }

        if ($mode === 'subscribe' && hash_equals($expectedToken, (string) $token)) {
            return response($challenge, 200);
        }

        return response('Forbidden', 403);
    }

    /**
     * Handle incoming WhatsApp messages from Meta.
     * Requires valid X-Hub-Signature-256 firmada con el App Secret.
     * El payload contiene PHI (telefonos + mensajes), asi que solo
     * loggeamos metadata (type, message_id) — nunca el cuerpo del mensaje.
     */
    public function handle(Request $request, WhatsAppBotService $bot)
    {
        $appSecret = config('services.whatsapp.app_secret');
        if (empty($appSecret)) {
            Log::error('WhatsApp webhook: WHATSAPP_APP_SECRET no configurado');
            return response()->json(['ok' => false], 500);
        }

        $rawBody = $request->getContent();
        $signature = $request->header('X-Hub-Signature-256', '');
        if (!$this->isValidSignature($rawBody, $signature, $appSecret)) {
            Log::warning('WhatsApp webhook: firma invalida', [
                'ip' => $request->ip(),
                'signature_present' => !empty($signature),
            ]);
            return response()->json(['ok' => false], 403);
        }

        $payload = $request->all();

        try {
            $entry = $payload['entry'][0] ?? null;
            if (!$entry) return response()->json(['ok' => true]);

            $changes = $entry['changes'][0]['value'] ?? null;
            if (!$changes) return response()->json(['ok' => true]);

            $messages = $changes['messages'] ?? [];
            if (empty($messages)) return response()->json(['ok' => true]);

            foreach ($messages as $msg) {
                $type = $msg['type'] ?? '';
                $msgId = $msg['id'] ?? 'unknown';

                // Metadata only — NEVER el body del mensaje (PHI + LFPDPPP)
                Log::info('WhatsApp webhook msg', ['type' => $type, 'id' => $msgId]);

                if ($type !== 'text') continue;

                $from = $msg['from'] ?? '';
                $text = $msg['text']['body'] ?? '';

                if (empty($from) || empty($text)) continue;

                $bot->handleIncoming($from, $text);
            }
        } catch (\Throwable $e) {
            Log::error('WhatsApp webhook error', ['error' => $e->getMessage()]);
        }

        return response()->json(['ok' => true]);
    }

    private function isValidSignature(string $rawBody, string $header, string $secret): bool
    {
        if (!str_starts_with($header, 'sha256=')) return false;
        $provided = substr($header, 7);
        $expected = hash_hmac('sha256', $rawBody, $secret);
        return hash_equals($expected, $provided);
    }
}
