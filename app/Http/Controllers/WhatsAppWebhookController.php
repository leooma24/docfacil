<?php

namespace App\Http\Controllers;

use App\Services\WhatsAppBotService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WhatsAppWebhookController extends Controller
{
    /**
     * Meta webhook verification (GET request on setup).
     */
    public function verify(Request $request)
    {
        $mode = $request->query('hub_mode');
        $token = $request->query('hub_verify_token');
        $challenge = $request->query('hub_challenge');

        $expectedToken = config('services.whatsapp.verify_token', 'docfacil-webhook-2026');

        if ($mode === 'subscribe' && $token === $expectedToken) {
            return response($challenge, 200);
        }

        return response('Forbidden', 403);
    }

    /**
     * Handle incoming WhatsApp messages from Meta.
     */
    public function handle(Request $request, WhatsAppBotService $bot)
    {
        $payload = $request->all();
        Log::info('WhatsApp webhook', $payload);

        try {
            $entry = $payload['entry'][0] ?? null;
            if (!$entry) return response()->json(['ok' => true]);

            $changes = $entry['changes'][0]['value'] ?? null;
            if (!$changes) return response()->json(['ok' => true]);

            $messages = $changes['messages'] ?? [];
            if (empty($messages)) return response()->json(['ok' => true]);

            foreach ($messages as $msg) {
                if (($msg['type'] ?? '') !== 'text') continue;

                $from = $msg['from'] ?? '';
                $text = $msg['text']['body'] ?? '';

                if (empty($from) || empty($text)) continue;

                // Process asynchronously would be better but for now inline is fine
                $bot->handleIncoming($from, $text);
            }
        } catch (\Throwable $e) {
            Log::error('WhatsApp webhook error', ['error' => $e->getMessage()]);
        }

        return response()->json(['ok' => true]);
    }
}
