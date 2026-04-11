<?php

namespace App\Services;

use App\Models\Patient;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PatientMessageAIService
{
    public const TYPES = [
        'reminder' => 'Recordatorio de cita',
        'followup' => 'Seguimiento post-consulta',
        'birthday' => 'Felicitación de cumpleaños',
        'promotion' => 'Oferta de regreso',
        'payment' => 'Recordatorio de pago pendiente',
        'welcome' => 'Bienvenida',
        'checkup' => 'Invitación a revisión',
    ];

    public function generate(Patient $patient, string $type, ?string $extra = null): ?string
    {
        if (!\App\Services\AI::enabled() || \App\Services\AI::dailyLimitReached()) return null;

        $typeLabel = self::TYPES[$type] ?? 'Mensaje general';
        $context = $this->buildContext($patient);

        $system = "Eres un asistente que redacta mensajes cortos de WhatsApp para pacientes de clínicas médicas/dentales en México. "
            . "Los mensajes deben ser: BREVES (máx 3-4 oraciones), amables, profesionales, con 1-2 emojis máximo. "
            . "Usa el nombre del paciente. Usa información real del historial cuando sea relevante. "
            . "Firma con el nombre de la clínica. "
            . "NO inventes datos que no tengas. "
            . "Responde SOLO con el mensaje listo para copiar, sin explicaciones ni formato markdown.";

        $user = "TIPO DE MENSAJE: {$typeLabel}\n\n"
            . "DATOS DEL PACIENTE:\n{$context}\n\n"
            . ($extra ? "CONTEXTO EXTRA: {$extra}\n\n" : '')
            . "Genera el mensaje:";

        try {
            $provider = config('services.ai.provider', 'deepseek');
            $config = config("services.ai.{$provider}");
            $apiKey = $config['key'] ?? null;
            if (!$apiKey) return null;

            if ($provider === 'anthropic') {
                $response = Http::timeout(20)
                    ->withHeaders([
                        'x-api-key' => $apiKey,
                        'anthropic-version' => '2023-06-01',
                        'content-type' => 'application/json',
                    ])
                    ->post('https://api.anthropic.com/v1/messages', [
                        'model' => $config['model'],
                        'max_tokens' => 300,
                        'system' => $system,
                        'messages' => [['role' => 'user', 'content' => $user]],
                    ]);
                return $response->successful() ? trim($response->json('content.0.text') ?? '') : null;
            }

            $response = Http::timeout(20)
                ->withToken($apiKey)
                ->post($config['base_url'] . '/chat/completions', [
                    'model' => $config['model'],
                    'max_tokens' => 300,
                    'temperature' => 0.7,
                    'messages' => [
                        ['role' => 'system', 'content' => $system],
                        ['role' => 'user', 'content' => $user],
                    ],
                ]);

            return $response->successful() ? trim($response->json('choices.0.message.content') ?? '') : null;
        } catch (\Throwable $e) {
            Log::error('Patient message AI exception', ['error' => $e->getMessage()]);
            return null;
        }
    }

    protected function buildContext(Patient $patient): string
    {
        $patient->loadMissing(['clinic', 'appointments.service', 'medicalRecords']);

        $lines = [];
        $lines[] = "Nombre: {$patient->first_name} {$patient->last_name}";
        if ($patient->birth_date) $lines[] = "Edad: {$patient->birth_date->age} años";

        $lastAppt = $patient->appointments->where('status', 'completed')->sortByDesc('starts_at')->first();
        if ($lastAppt) {
            $lines[] = "Última visita: " . $lastAppt->starts_at->format('d/m/Y');
            if ($lastAppt->service) $lines[] = "Último servicio: {$lastAppt->service->name}";
        }

        $nextAppt = $patient->appointments->whereIn('status', ['scheduled', 'confirmed'])->where('starts_at', '>=', now())->sortBy('starts_at')->first();
        if ($nextAppt) {
            $lines[] = "Próxima cita: " . $nextAppt->starts_at->translatedFormat('l d \d\e F, H:i');
        }

        $lastDx = $patient->medicalRecords->sortByDesc('visit_date')->first();
        if ($lastDx && $lastDx->diagnosis) {
            $lines[] = "Último diagnóstico: {$lastDx->diagnosis}";
        }

        $clinicName = $patient->clinic->name ?? 'la clínica';
        $lines[] = "Clínica: {$clinicName}";

        return implode("\n", $lines);
    }
}
