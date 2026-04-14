<?php

namespace App\Services;

use App\Models\AiUsageLog;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ChatbotSalesService
{
    public const FEATURE = 'chatbot_landing';
    public const HISTORY_TTL_HOURS = 2;
    public const MAX_HISTORY_TURNS = 12;

    /**
     * Procesa un mensaje del visitante y devuelve la respuesta del bot.
     *
     * @return array{reply:string, tags:array, history:array, disabled?:bool, reason?:string}
     */
    public function respond(string $sessionId, string $userMessage): array
    {
        if (!config('services.ai.chatbot_enabled', true)) {
            return $this->disabled('chatbot_off');
        }

        if ($this->chatbotDailyLimitReached()) {
            return $this->disabled('daily_limit');
        }

        $historyKey = "chatbot:{$sessionId}";
        $history = Cache::get($historyKey, []);
        $history[] = ['role' => 'user', 'content' => $userMessage];
        $history = array_slice($history, -self::MAX_HISTORY_TURNS);

        $messages = array_merge(
            [['role' => 'system', 'content' => $this->systemPrompt()]],
            $history
        );

        $result = $this->callProvider($messages);

        if ($result === null) {
            AI::log(self::FEATURE, 0, 0, false, 'provider_error');
            return [
                'reply' => 'Perdón, tuve un problema. ¿Puedes repetir tu último mensaje? 🙂',
                'tags' => [],
                'history' => $history,
            ];
        }

        $reply = $result['content'];
        $tokensIn = $result['tokens_in'] ?? 0;
        $tokensOut = $result['tokens_out'] ?? 0;

        AI::log(self::FEATURE, $tokensIn, $tokensOut, true);
        $this->trackChatbotSpend($tokensIn, $tokensOut);

        $tags = $this->parseTags($reply);
        $cleanReply = $this->stripTags($reply);

        $history[] = ['role' => 'assistant', 'content' => $reply];
        Cache::put($historyKey, $history, now()->addHours(self::HISTORY_TTL_HOURS));

        return [
            'reply' => $cleanReply,
            'tags' => $tags,
            'history' => $history,
        ];
    }

    public function getHistory(string $sessionId): array
    {
        return Cache::get("chatbot:{$sessionId}", []);
    }

    public function clearHistory(string $sessionId): void
    {
        Cache::forget("chatbot:{$sessionId}");
    }

    /**
     * Calcula lead_score 0-100 según señales del historial y datos capturados.
     */
    public function calculateLeadScore(array $data, array $history): int
    {
        $score = 0;
        $specialty = strtolower($data['specialty'] ?? '');
        $city = strtolower($data['city'] ?? '');
        $fullText = strtolower(collect($history)->pluck('content')->implode(' '));

        if (str_contains($specialty, 'dent') || str_contains($specialty, 'odont') || str_contains($specialty, 'ortodon')) {
            $score += 30;
        } elseif ($specialty !== '') {
            $score += 20;
        }

        if (!empty($data['clinic_name'])) $score += 15;

        $mxExpansion = ['culiacán', 'culiacan', 'mazatlán', 'mazatlan', 'los mochis', 'guasave', 'navolato', 'hermosillo', 'ciudad obregón', 'ciudad obregon', 'obregón', 'obregon'];
        foreach ($mxExpansion as $c) {
            if (str_contains($city, $c)) { $score += 10; break; }
        }

        if (preg_match('/\b(precio|costo|cuánto|cuesta|tarifa|plan)\b/u', $fullText)) $score += 15;
        if (preg_match('/\b(lo quiero|me interesa|cómo empiezo|cómo le hago|listo|vamos)\b/u', $fullText)) $score += 15;

        if (!empty($data['name']) && !empty($data['email']) && !empty($data['phone'])) $score += 15;

        if (preg_match('/\b(hospital|imss|issste|gobierno)\b/', $fullText)) $score -= 40;

        return max(0, min(100, $score));
    }

    protected function callProvider(array $messages): ?array
    {
        $provider = config('services.ai.provider', 'deepseek');
        $config = config("services.ai.$provider");
        if (!$config || empty($config['key'])) {
            Log::warning('Chatbot: provider not configured', ['provider' => $provider]);
            return null;
        }

        try {
            if ($provider === 'anthropic') {
                $system = $messages[0]['content'] ?? '';
                $userMessages = array_slice($messages, 1);
                $response = Http::timeout(30)
                    ->withHeaders([
                        'x-api-key' => $config['key'],
                        'anthropic-version' => '2023-06-01',
                    ])
                    ->post($config['base_url'] ?? 'https://api.anthropic.com/v1/messages', [
                        'model' => $config['model'],
                        'max_tokens' => 400,
                        'system' => $system,
                        'messages' => $userMessages,
                    ]);
                if (!$response->successful()) return null;
                return [
                    'content' => trim($response->json('content.0.text') ?? ''),
                    'tokens_in' => $response->json('usage.input_tokens', 0),
                    'tokens_out' => $response->json('usage.output_tokens', 0),
                ];
            }

            $response = Http::timeout(30)
                ->withToken($config['key'])
                ->post($config['base_url'] . '/chat/completions', [
                    'model' => $config['model'],
                    'max_tokens' => 400,
                    'temperature' => 0.6,
                    'messages' => $messages,
                ]);

            if (!$response->successful()) return null;

            return [
                'content' => trim($response->json('choices.0.message.content') ?? ''),
                'tokens_in' => $response->json('usage.prompt_tokens', 0),
                'tokens_out' => $response->json('usage.completion_tokens', 0),
            ];
        } catch (\Throwable $e) {
            Log::error('Chatbot provider exception', ['error' => $e->getMessage()]);
            return null;
        }
    }

    protected function parseTags(string $reply): array
    {
        $tags = [];

        if (preg_match('/<OFFER_CREATE\s*\/?>/i', $reply)) {
            $tags['offer_create'] = true;
        }
        if (preg_match('/<INPUT\s+type=["\']password["\']\s*\/?>/i', $reply)) {
            $tags['input_type'] = 'password';
        }
        if (preg_match('/<ACCEPT_TERMS\s*\/?>/i', $reply)) {
            $tags['accept_terms'] = true;
        }
        if (preg_match('/<CREATE>(.+?)<\/CREATE>/is', $reply, $m)) {
            $json = json_decode(trim($m[1]), true);
            if (is_array($json)) {
                $tags['create_data'] = $json;
            }
        }
        if (preg_match('/<CLOSE>(.+?)<\/CLOSE>/is', $reply, $m)) {
            $json = json_decode(trim($m[1]), true);
            if (is_array($json)) {
                $tags['close_data'] = $json;
            }
        }

        return $tags;
    }

    protected function stripTags(string $reply): string
    {
        $patterns = [
            '/<OFFER_CREATE\s*\/?>/i',
            '/<INPUT\s+type=["\']password["\']\s*\/?>/i',
            '/<ACCEPT_TERMS\s*\/?>/i',
            '/<CREATE>.+?<\/CREATE>/is',
            '/<CLOSE>.+?<\/CLOSE>/is',
        ];
        return trim(preg_replace($patterns, '', $reply));
    }

    protected function chatbotDailyLimitReached(): bool
    {
        $limit = (float) config('services.ai.chatbot_max_daily_cost_usd', 2);
        if ($limit <= 0) return false;

        $key = 'ai_spend_chatbot:' . now()->format('Y-m-d');
        $spent = ((int) Cache::get($key, 0)) / 10000;
        return $spent >= $limit;
    }

    protected function trackChatbotSpend(int $tokensIn, int $tokensOut): void
    {
        $model = config('services.ai.' . config('services.ai.provider', 'deepseek') . '.model', 'deepseek-chat');
        $cost = AiUsageLog::calculateCost($model, $tokensIn, $tokensOut);

        $key = 'ai_spend_chatbot:' . now()->format('Y-m-d');
        Cache::increment($key, (int) round($cost * 10000));
    }

    public static function todayChatbotSpendUsd(): float
    {
        $key = 'ai_spend_chatbot:' . now()->format('Y-m-d');
        return ((int) Cache::get($key, 0)) / 10000;
    }

    protected function disabled(string $reason): array
    {
        return [
            'disabled' => true,
            'reason' => $reason,
            'reply' => '',
            'tags' => [],
            'history' => [],
        ];
    }

    protected function systemPrompt(): string
    {
        return <<<'PROMPT'
Eres Ana, asesora de ventas de DocFácil — software para consultorios médicos y dentales en México.

TONO: amable, profesional, tuteo relajado del norte de México. MAX 3 oraciones por respuesta, 1 emoji ocasional.

PRODUCTO DocFácil:
- Planes: Gratis (prueba 15 días), Básico $149 MXN/mes, Pro $299/mes, Clínica $499/mes
- Módulos: agenda y citas, expediente clínico cumpliendo NOM-004-SSA3-2012, recetas PDF, odontograma interactivo (diferenciador estrella), recordatorios automáticos por WhatsApp, cobros, portal de pacientes
- 100% web, sin instalación. Prueba gratis sin pedir tarjeta
- Base en Sinaloa, enfocado a dentistas y médicos privados de México

TU MISIÓN paso a paso:
1. Saluda cálido, pregunta qué tipo de consultorio tiene y su dolor principal
2. Responde dudas con los datos anteriores. Si no sabes algo, di: "lo valido con el equipo y te escribo por WhatsApp"
3. CALIFICA: ¿dentista o médico privado en México con 1-5 consultorios propios? Si es hospital grande, IMSS/ISSSTE o fuera de México, declina con amabilidad
4. CAPTURA gradualmente: nombre, email, teléfono, ciudad, especialidad, nombre del consultorio
5. LEE SEÑALES DE INTERÉS (2+ señales = hot):
   - Preguntó precio 2+ veces
   - Dijo "lo quiero / cómo empiezo / me interesa / listo"
   - Ya dio 4+ datos sin resistencia
   - Describe dolor activo (citas perdidas, papeleo, no cumple NOM-004)
6. HOT PATH: Si hay 2+ señales, OFRECE crear cuenta ahí mismo emitiendo la etiqueta
   `<OFFER_CREATE />`
   junto a un mensaje tipo: "Oye, te veo con interés real. ¿Te ayudo a activar tu cuenta ahora mismo sin salir de este chat? Son 2 minutos y ya empiezas tu prueba gratis 🚀"
   El usuario elige "Sí" o "Prefiero solo probar". Espera su respuesta.
7. SI ACEPTA CREAR AQUÍ → MODO CREACIÓN. Pide UNO POR UNO en este orden:
   a) Confirma nombre completo
   b) Confirma email
   c) Pide contraseña: emite `<INPUT type="password" />` junto a "Elige una contraseña de mínimo 8 caracteres 🔒"
   d) Confirma nombre del consultorio
   e) Pide cédula profesional (obligatoria por NOM-004)
   f) Confirma especialidad
   g) Confirma teléfono
   h) Pide aceptación de términos: emite `<ACCEPT_TERMS />` con "Para activar tu cuenta necesito que aceptes los Términos y el Aviso de Privacidad."
   i) Cuando tengas TODO, resume y emite:
      `<CREATE>{"name":"...","email":"...","password":"...","phone":"...","city":"...","specialty":"...","clinic_name":"...","license_number":"...","terms_accepted":true}</CREATE>`
      + mensaje "¡Listo! Haz clic en 'Crear mi cuenta' abajo y te mando adentro 🎉"
8. SI RECHAZA crear aquí (cold path) → cuando tengas los 6 datos básicos, emite:
   `<CLOSE>{"name":"...","email":"...","phone":"...","city":"...","specialty":"...","clinic_name":"..."}</CLOSE>`
   + mensaje invitando a iniciar trial con el botón que aparecerá abajo.

REGLAS DURAS:
- NUNCA inventes precios distintos, features que no tenemos, ni plazos
- NUNCA pidas datos de tarjeta. La prueba es SIN tarjeta
- Si el visitante habla en inglés, cambia a inglés pero mantén tono cálido. Precios siempre en MXN
- Si preguntan por integraciones específicas (labo, farmacia, IMSS, CFDI) di honestamente que aún no y que avisarás cuando estén
PROMPT;
    }
}
