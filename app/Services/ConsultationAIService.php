<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ConsultationAIService
{
    /**
     * Parse a raw dictation transcript from the doctor and extract structured data:
     * chief_complaint, diagnosis, treatment, medical_notes, medications[]
     */
    public function structureDictation(string $transcript): ?array
    {
        if (empty(trim($transcript))) {
            return null;
        }

        $system = "Eres un asistente médico que extrae información estructurada de dictados de consulta en español. "
            . "Recibes un texto dictado por un doctor y extraes la información en JSON con esta estructura EXACTA:\n"
            . '{"chief_complaint":"texto","diagnosis":"texto","treatment":"texto","medical_notes":"texto","medications":[{"medication":"nombre","dosage":"dosis","frequency":"frecuencia","duration":"duración","instructions":"indicaciones"}]}' . "\n\n"
            . "Reglas:\n"
            . "- Si algún campo no se menciona, devuélvelo como string vacío ''\n"
            . "- 'chief_complaint' es el motivo de consulta (por qué viene el paciente)\n"
            . "- 'diagnosis' es el diagnóstico clínico\n"
            . "- 'treatment' es el tratamiento APLICADO en esta visita (no medicamentos)\n"
            . "- 'medications' es un array de medicamentos RECETADOS (puede estar vacío [])\n"
            . "- 'medical_notes' son observaciones adicionales\n"
            . "- Usa terminología médica estándar\n"
            . "- NO inventes información que no esté en el dictado\n"
            . "- RESPONDE SOLO CON EL JSON, sin texto extra ni markdown";

        $user = "DICTADO:\n\"{$transcript}\"\n\nJSON:";

        try {
            $result = $this->callAi($system, $user);
            if (!$result) return null;

            // Try to extract JSON even if the model wrapped it
            $json = $this->extractJson($result);
            if (!$json) return null;

            $data = json_decode($json, true);
            if (!is_array($data)) return null;

            return [
                'chief_complaint' => (string) ($data['chief_complaint'] ?? ''),
                'diagnosis' => (string) ($data['diagnosis'] ?? ''),
                'treatment' => (string) ($data['treatment'] ?? ''),
                'medical_notes' => (string) ($data['medical_notes'] ?? ''),
                'medications' => array_map(function ($m) {
                    return [
                        'medication' => (string) ($m['medication'] ?? ''),
                        'dosage' => (string) ($m['dosage'] ?? ''),
                        'frequency' => (string) ($m['frequency'] ?? ''),
                        'duration' => (string) ($m['duration'] ?? ''),
                        'instructions' => (string) ($m['instructions'] ?? ''),
                    ];
                }, $data['medications'] ?? []),
            ];
        } catch (\Throwable $e) {
            Log::error('Consultation AI exception', ['error' => $e->getMessage()]);
            return null;
        }
    }

    protected function extractJson(string $text): ?string
    {
        // Try plain JSON
        if (str_starts_with(trim($text), '{')) {
            return trim($text);
        }
        // Extract from markdown code block
        if (preg_match('/```(?:json)?\s*(\{.*?\})\s*```/s', $text, $m)) {
            return $m[1];
        }
        // Extract first {...}
        if (preg_match('/\{.*\}/s', $text, $m)) {
            return $m[0];
        }
        return null;
    }

    protected function callAi(string $system, string $user): ?string
    {
        $provider = config('services.ai.provider', 'deepseek');
        $config = config("services.ai.{$provider}");
        $apiKey = $config['key'] ?? null;
        if (!$apiKey) return null;

        if ($provider === 'anthropic') {
            $response = Http::timeout(30)
                ->withHeaders([
                    'x-api-key' => $apiKey,
                    'anthropic-version' => '2023-06-01',
                    'content-type' => 'application/json',
                ])
                ->post('https://api.anthropic.com/v1/messages', [
                    'model' => $config['model'],
                    'max_tokens' => 1000,
                    'system' => $system,
                    'messages' => [['role' => 'user', 'content' => $user]],
                ]);
            return $response->successful() ? trim($response->json('content.0.text') ?? '') : null;
        }

        // OpenAI / DeepSeek compatible
        $response = Http::timeout(30)
            ->withToken($apiKey)
            ->post($config['base_url'] . '/chat/completions', [
                'model' => $config['model'],
                'max_tokens' => 1000,
                'temperature' => 0.2,
                'messages' => [
                    ['role' => 'system', 'content' => $system],
                    ['role' => 'user', 'content' => $user],
                ],
            ]);

        if (!$response->successful()) {
            Log::warning("{$provider} API error", ['status' => $response->status(), 'body' => $response->body()]);
            return null;
        }

        return trim($response->json('choices.0.message.content') ?? '') ?: null;
    }
}
