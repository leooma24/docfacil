<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ConsentFormAIService
{
    /**
     * Given a procedure name, generate a full consent form template:
     * title, content (HTML), risks, alternatives.
     */
    public function generateTemplate(string $procedureName, ?string $specialty = null): ?array
    {
        if (!\App\Services\AI::enabled() || \App\Services\AI::dailyLimitReached()) return null;

        $key = trim($procedureName);
        if (strlen($key) < 4) return null;

        $cacheKey = 'ai_consent:' . md5(strtolower($key . '|' . $specialty));

        return Cache::remember($cacheKey, now()->addDays(30), function () use ($key, $specialty) {
            return $this->generate($key, $specialty);
        });
    }

    protected function generate(string $procedure, ?string $specialty): ?array
    {
        $system = "Eres un asistente médico experto en redacción de consentimientos informados en español (México). "
            . "Recibes el nombre de un procedimiento y generas un consentimiento informado COMPLETO y profesional. "
            . "El lenguaje debe ser claro para el paciente pero con terminología médica correcta. "
            . "Responde en JSON con esta estructura EXACTA:\n"
            . '{"title":"Consentimiento Informado - [procedimiento]","content":"HTML con <p>, <ul>, <strong>","risks":"texto plano con riesgos separados por puntos","alternatives":"texto plano con alternativas"}' . "\n\n"
            . "Reglas para cada campo:\n"
            . "- title: 'Consentimiento Informado - [nombre procedimiento]'\n"
            . "- content: HTML con explicación del procedimiento (qué es, cómo se hace, duración, cuidados). Usa <p>, <ul><li>, <strong>. Mínimo 3 párrafos. Incluye al inicio 'Yo, [NOMBRE_PACIENTE], autorizo el siguiente procedimiento:' y al final la declaración de comprensión\n"
            . "- risks: 3-5 riesgos reales del procedimiento separados por puntos\n"
            . "- alternatives: 2-3 alternativas de tratamiento separadas por puntos\n"
            . "- RESPONDE SOLO CON EL JSON, sin markdown ni texto extra";

        $specialtyHint = $specialty ? " (especialidad: {$specialty})" : '';
        $user = "PROCEDIMIENTO: {$procedure}{$specialtyHint}\n\nJSON:";

        try {
            $result = $this->callAi($system, $user);
            if (!$result) return null;

            $json = $this->extractJson($result);
            if (!$json) return null;

            $data = json_decode($json, true);
            if (!is_array($data)) return null;

            return [
                'title' => (string) ($data['title'] ?? "Consentimiento Informado - {$procedure}"),
                'content' => (string) ($data['content'] ?? ''),
                'risks' => (string) ($data['risks'] ?? ''),
                'alternatives' => (string) ($data['alternatives'] ?? ''),
                'procedure_name' => $procedure,
            ];
        } catch (\Throwable $e) {
            Log::error('Consent AI exception', ['error' => $e->getMessage()]);
            return null;
        }
    }

    protected function extractJson(string $text): ?string
    {
        if (str_starts_with(trim($text), '{')) return trim($text);
        if (preg_match('/```(?:json)?\s*(\{.*?\})\s*```/s', $text, $m)) return $m[1];
        if (preg_match('/\{.*\}/s', $text, $m)) return $m[0];
        return null;
    }

    protected function callAi(string $system, string $user): ?string
    {
        $provider = config('services.ai.provider', 'deepseek');
        $config = config("services.ai.{$provider}");
        $apiKey = $config['key'] ?? null;
        if (!$apiKey) return null;

        if ($provider === 'anthropic') {
            $response = Http::timeout(45)
                ->withHeaders([
                    'x-api-key' => $apiKey,
                    'anthropic-version' => '2023-06-01',
                    'content-type' => 'application/json',
                ])
                ->post('https://api.anthropic.com/v1/messages', [
                    'model' => $config['model'],
                    'max_tokens' => 2500,
                    'system' => $system,
                    'messages' => [['role' => 'user', 'content' => $user]],
                ]);
            return $response->successful() ? trim($response->json('content.0.text') ?? '') : null;
        }

        $response = Http::timeout(45)
            ->withToken($apiKey)
            ->post($config['base_url'] . '/chat/completions', [
                'model' => $config['model'],
                'max_tokens' => 2500,
                'temperature' => 0.3,
                'messages' => [
                    ['role' => 'system', 'content' => $system],
                    ['role' => 'user', 'content' => $user],
                ],
            ]);

        if (!$response->successful()) {
            Log::warning("{$provider} consent API error", ['status' => $response->status(), 'body' => $response->body()]);
            return null;
        }

        return trim($response->json('choices.0.message.content') ?? '') ?: null;
    }
}
