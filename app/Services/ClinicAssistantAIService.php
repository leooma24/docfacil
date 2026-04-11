<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\MedicalRecord;
use App\Models\Patient;
use App\Models\Payment;
use App\Models\Prescription;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ClinicAssistantAIService
{
    /**
     * Answer a question about the clinic using live data as context.
     * The AI can query: appointments, patients, payments, prescriptions, records.
     */
    public function ask(int $clinicId, string $question, array $history = []): ?string
    {
        if (!\App\Services\AI::enabled() || \App\Services\AI::dailyLimitReached()) return null;

        $context = $this->buildContext($clinicId);

        $system = "Eres el asistente IA de DocFácil, una app para consultorios médicos/dentales en México. "
            . "Respondes preguntas del doctor sobre SU CONSULTORIO usando los datos en tiempo real que se te proporcionan. "
            . "IMPORTANTES:\n"
            . "- Responde en español, de forma BREVE y conversacional (máx 4 oraciones)\n"
            . "- Usa los datos REALES del consultorio, nunca inventes números\n"
            . "- Si te preguntan por algo que no puedes saber con los datos dados, dilo\n"
            . "- Puedes ser amigable y motivador\n"
            . "- Si te piden algo accionable (ej. 'genera un mensaje'), responde con el texto listo para copiar\n"
            . "- Formato limpio, sin markdown excesivo\n\n"
            . "DATOS ACTUALES DEL CONSULTORIO:\n" . $context;

        $messages = [['role' => 'system', 'content' => $system]];
        foreach ($history as $msg) {
            if (!empty($msg['role']) && !empty($msg['content'])) {
                $messages[] = ['role' => $msg['role'], 'content' => $msg['content']];
            }
        }
        $messages[] = ['role' => 'user', 'content' => $question];

        try {
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
                        'max_tokens' => 600,
                        'system' => $system,
                        'messages' => array_slice($messages, 1), // anthropic doesn't use system in messages
                    ]);
                return $response->successful() ? trim($response->json('content.0.text') ?? '') : null;
            }

            $response = Http::timeout(30)
                ->withToken($apiKey)
                ->post($config['base_url'] . '/chat/completions', [
                    'model' => $config['model'],
                    'max_tokens' => 600,
                    'temperature' => 0.4,
                    'messages' => $messages,
                ]);

            if (!$response->successful()) {
                Log::warning("{$provider} assistant API error", ['body' => $response->body()]);
                return null;
            }

            return trim($response->json('choices.0.message.content') ?? '') ?: null;
        } catch (\Throwable $e) {
            Log::error('Assistant AI exception', ['error' => $e->getMessage()]);
            return null;
        }
    }

    protected function buildContext(int $clinicId): string
    {
        $now = now();
        $lines = [];
        $lines[] = "Fecha actual: " . $now->translatedFormat('l d \d\e F Y H:i');

        // Today
        $todayAppts = Appointment::where('clinic_id', $clinicId)
            ->whereDate('starts_at', $now->toDateString())
            ->with(['patient', 'service'])
            ->orderBy('starts_at')
            ->get();
        $lines[] = "\n-- CITAS DE HOY (" . $todayAppts->count() . ") --";
        foreach ($todayAppts as $a) {
            $lines[] = "- {$a->starts_at->format('H:i')}: {$a->patient->first_name} {$a->patient->last_name} ({$a->status}) - " . ($a->service->name ?? 'Sin servicio');
        }

        // Tomorrow
        $tomorrow = $now->copy()->addDay();
        $tomorrowAppts = Appointment::where('clinic_id', $clinicId)
            ->whereDate('starts_at', $tomorrow->toDateString())
            ->with(['patient', 'service'])
            ->orderBy('starts_at')
            ->get();
        $lines[] = "\n-- CITAS MAÑANA (" . $tomorrowAppts->count() . ") --";
        foreach ($tomorrowAppts as $a) {
            $lines[] = "- {$a->starts_at->format('H:i')}: {$a->patient->first_name} {$a->patient->last_name}";
        }

        // Week stats
        $weekStart = $now->copy()->startOfWeek();
        $weekAppts = Appointment::where('clinic_id', $clinicId)
            ->whereBetween('starts_at', [$weekStart, $now])
            ->count();
        $weekRevenue = Payment::where('clinic_id', $clinicId)
            ->where('status', 'paid')
            ->whereBetween('payment_date', [$weekStart, $now])
            ->sum('amount');
        $lines[] = "\n-- ESTA SEMANA --";
        $lines[] = "Citas: {$weekAppts}";
        $lines[] = "Ingresos: $" . number_format($weekRevenue, 2);

        // Month stats
        $monthStart = $now->copy()->startOfMonth();
        $monthAppts = Appointment::where('clinic_id', $clinicId)
            ->whereBetween('starts_at', [$monthStart, $now])
            ->count();
        $monthRevenue = Payment::where('clinic_id', $clinicId)
            ->where('status', 'paid')
            ->whereBetween('payment_date', [$monthStart, $now])
            ->sum('amount');
        $pending = Payment::where('clinic_id', $clinicId)
            ->whereIn('status', ['pending', 'partial'])
            ->sum('amount');
        $lines[] = "\n-- ESTE MES --";
        $lines[] = "Citas: {$monthAppts}";
        $lines[] = "Ingresos cobrados: $" . number_format($monthRevenue, 2);
        $lines[] = "Cobros pendientes: $" . number_format($pending, 2);

        // Top services
        $topServices = Payment::where('clinic_id', $clinicId)
            ->where('status', 'paid')
            ->whereBetween('payment_date', [$monthStart, $now])
            ->whereNotNull('service_id')
            ->with('service:id,name')
            ->get()
            ->groupBy('service.name')
            ->map(fn ($g) => (float) $g->sum('amount'))
            ->sortDesc()
            ->take(5);
        if ($topServices->count()) {
            $lines[] = "\n-- TOP SERVICIOS DEL MES --";
            foreach ($topServices as $name => $amt) {
                $lines[] = "- {$name}: $" . number_format($amt, 2);
            }
        }

        // Totals
        $totalPatients = Patient::where('clinic_id', $clinicId)->count();
        $lines[] = "\n-- GENERALES --";
        $lines[] = "Total de pacientes: {$totalPatients}";

        return implode("\n", $lines);
    }
}
