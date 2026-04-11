<?php

namespace App\Services;

use App\Models\Patient;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PatientAISummaryService
{
    /**
     * Supported providers: anthropic, deepseek, openai
     * Configured via AI_PROVIDER env var.
     */
    public function summarize(Patient $patient): ?string
    {
        $cacheKey = $this->cacheKey($patient);

        return Cache::remember($cacheKey, now()->addHours(6), function () use ($patient) {
            return $this->generateSummary($patient);
        });
    }

    public function invalidate(Patient $patient): void
    {
        Cache::forget($this->cacheKey($patient));
    }

    protected function cacheKey(Patient $patient): string
    {
        $lastRecord = $patient->medicalRecords()->latest()->first();
        $lastRecordTs = $lastRecord?->updated_at?->timestamp ?? 0;
        return "patient_ai_summary:{$patient->id}:" . $patient->updated_at->timestamp . ":{$lastRecordTs}";
    }

    protected function generateSummary(Patient $patient): ?string
    {
        $provider = config('services.ai.provider', 'deepseek');
        $context = $this->buildPatientContext($patient);

        $systemPrompt = "Eres un asistente médico que resume historiales de pacientes en español para dentistas y médicos ocupados. "
            . "Genera un resumen narrativo BREVE (máximo 4 oraciones). "
            . "Enfócate en: edad, total de visitas, diagnósticos previos relevantes, alertas importantes (alergias, alto riesgo) y motivo común de visitas. "
            . "Usa un tono profesional pero humano. Si hay alergias o condiciones críticas, menciónalas al inicio. "
            . "NO inventes información. Si no hay historial, di 'Primera visita' y datos básicos.";

        $userPrompt = "DATOS DEL PACIENTE:\n{$context}\n\nRESUMEN:";

        try {
            return match ($provider) {
                'anthropic' => $this->callAnthropic($systemPrompt, $userPrompt),
                'openai' => $this->callOpenAiCompatible($systemPrompt, $userPrompt, 'openai'),
                default => $this->callOpenAiCompatible($systemPrompt, $userPrompt, 'deepseek'),
            };
        } catch (\Throwable $e) {
            Log::error('AI summary exception', ['error' => $e->getMessage(), 'provider' => $provider]);
            return null;
        }
    }

    protected function callAnthropic(string $system, string $user): ?string
    {
        $apiKey = config('services.ai.anthropic.key');
        if (!$apiKey) return null;

        $response = Http::timeout(15)
            ->withHeaders([
                'x-api-key' => $apiKey,
                'anthropic-version' => '2023-06-01',
                'content-type' => 'application/json',
            ])
            ->post('https://api.anthropic.com/v1/messages', [
                'model' => config('services.ai.anthropic.model'),
                'max_tokens' => 400,
                'system' => $system,
                'messages' => [['role' => 'user', 'content' => $user]],
            ]);

        if (!$response->successful()) {
            Log::warning('Anthropic API error', ['status' => $response->status(), 'body' => $response->body()]);
            return null;
        }

        return trim($response->json('content.0.text') ?? '') ?: null;
    }

    protected function callOpenAiCompatible(string $system, string $user, string $provider): ?string
    {
        $config = config("services.ai.{$provider}");
        $apiKey = $config['key'] ?? null;
        if (!$apiKey) return null;

        $response = Http::timeout(15)
            ->withToken($apiKey)
            ->post($config['base_url'] . '/chat/completions', [
                'model' => $config['model'],
                'max_tokens' => 400,
                'temperature' => 0.3,
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

    protected function buildPatientContext(Patient $patient): string
    {
        $patient->loadMissing(['medicalRecords.doctor.user', 'appointments.service', 'prescriptions.items']);

        $lines = [];
        $lines[] = "Nombre: {$patient->first_name} {$patient->last_name}";
        if ($patient->birth_date) {
            $lines[] = "Edad: {$patient->birth_date->age} años" . ($patient->gender ? " ({$patient->gender})" : '');
        }
        if ($patient->blood_type) {
            $lines[] = "Tipo sangre: {$patient->blood_type}";
        }
        if ($patient->allergies) {
            $lines[] = "⚠ Alergias: {$patient->allergies}";
        }
        if ($patient->medical_notes) {
            $lines[] = "Notas médicas: " . substr($patient->medical_notes, 0, 300);
        }

        $appts = $patient->appointments;
        $lines[] = "Total citas: {$appts->count()}";
        $lines[] = "Citas completadas: " . $appts->where('status', 'completed')->count();
        $lines[] = "Primera visita: " . ($appts->sortBy('starts_at')->first()?->starts_at?->format('d/m/Y') ?? 'N/A');
        $lines[] = "Última visita: " . ($appts->where('status', 'completed')->sortByDesc('starts_at')->first()?->starts_at?->format('d/m/Y') ?? 'Ninguna');

        $records = $patient->medicalRecords->sortByDesc('visit_date')->take(5);
        if ($records->count()) {
            $lines[] = "\nÚltimos expedientes (max 5):";
            foreach ($records as $r) {
                $lines[] = "- {$r->visit_date->format('d/m/Y')}: "
                    . ($r->chief_complaint ? "Motivo: {$r->chief_complaint}. " : '')
                    . ($r->diagnosis ? "Dx: {$r->diagnosis}. " : '')
                    . ($r->treatment ? "Tx: {$r->treatment}" : '');
            }
        }

        $prescriptions = $patient->prescriptions->sortByDesc('prescription_date')->take(3);
        if ($prescriptions->count()) {
            $lines[] = "\nRecetas recientes:";
            foreach ($prescriptions as $p) {
                $meds = $p->items->pluck('medication')->implode(', ');
                $lines[] = "- {$p->prescription_date->format('d/m/Y')}: {$meds}";
            }
        }

        return implode("\n", $lines);
    }
}
