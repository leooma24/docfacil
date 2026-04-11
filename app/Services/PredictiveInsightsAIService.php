<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\Patient;
use App\Models\Payment;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PredictiveInsightsAIService
{
    public function getPredictions(int $clinicId): ?array
    {
        return Cache::remember("predictive_insights:{$clinicId}:" . now()->format('Y-m-d'), now()->addHours(12), function () use ($clinicId) {
            return $this->generate($clinicId);
        });
    }

    public function invalidate(int $clinicId): void
    {
        Cache::forget("predictive_insights:{$clinicId}:" . now()->format('Y-m-d'));
    }

    protected function generate(int $clinicId): ?array
    {
        $data = $this->collectHistoricalStats($clinicId);

        $system = "Eres un consultor de negocios senior para clínicas médicas/dentales en México. "
            . "Analizas datos históricos y generas PREDICCIONES específicas y RECOMENDACIONES accionables. "
            . "Todo en español. Responde en JSON con esta estructura EXACTA:\n"
            . '{"predictions":[{"icon":"emoji","type":"revenue|workload|retention|pricing","title":"título corto","prediction":"qué va a pasar","action":"qué hacer","impact":"impacto estimado en MXN o pacientes"}],"summary":"resumen ejecutivo de 2 oraciones"}' . "\n\n"
            . "Reglas:\n"
            . "- Genera 4-6 predicciones concretas\n"
            . "- Usa números reales de los datos proporcionados\n"
            . "- Cada predicción debe tener una ACCIÓN clara\n"
            . "- Estima IMPACTO monetario o de pacientes cuando sea posible\n"
            . "- Enfócate en: ingresos futuros, retención de pacientes, optimización de agenda, precios, pacientes en riesgo\n"
            . "- No inventes datos, usa lo proporcionado\n"
            . "- RESPONDE SOLO CON EL JSON, sin markdown";

        $user = "DATOS HISTÓRICOS DEL CONSULTORIO:\n" . json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n\nJSON:";

        try {
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
                        'max_tokens' => 2000,
                        'system' => $system,
                        'messages' => [['role' => 'user', 'content' => $user]],
                    ]);
                $text = $response->successful() ? trim($response->json('content.0.text') ?? '') : null;
            } else {
                $response = Http::timeout(45)
                    ->withToken($apiKey)
                    ->post($config['base_url'] . '/chat/completions', [
                        'model' => $config['model'],
                        'max_tokens' => 2000,
                        'temperature' => 0.4,
                        'messages' => [
                            ['role' => 'system', 'content' => $system],
                            ['role' => 'user', 'content' => $user],
                        ],
                    ]);
                if (!$response->successful()) return null;
                $text = trim($response->json('choices.0.message.content') ?? '');
            }

            if (!$text) return null;
            $json = $this->extractJson($text);
            if (!$json) return null;

            $parsed = json_decode($json, true);
            if (!is_array($parsed)) return null;

            return [
                'summary' => (string) ($parsed['summary'] ?? ''),
                'predictions' => array_map(function ($p) {
                    return [
                        'icon' => (string) ($p['icon'] ?? '💡'),
                        'type' => (string) ($p['type'] ?? 'revenue'),
                        'title' => (string) ($p['title'] ?? ''),
                        'prediction' => (string) ($p['prediction'] ?? ''),
                        'action' => (string) ($p['action'] ?? ''),
                        'impact' => (string) ($p['impact'] ?? ''),
                    ];
                }, $parsed['predictions'] ?? []),
                'generated_at' => now()->toIso8601String(),
                'stats' => $data,
            ];
        } catch (\Throwable $e) {
            Log::error('Predictive AI exception', ['error' => $e->getMessage()]);
            return null;
        }
    }

    protected function collectHistoricalStats(int $clinicId): array
    {
        $now = now();

        // Last 6 months revenue
        $revenueByMonth = [];
        for ($i = 5; $i >= 0; $i--) {
            $monthStart = $now->copy()->subMonths($i)->startOfMonth();
            $monthEnd = $now->copy()->subMonths($i)->endOfMonth();
            $revenue = (float) Payment::where('clinic_id', $clinicId)
                ->where('status', 'paid')
                ->whereBetween('payment_date', [$monthStart, $monthEnd])
                ->sum('amount');
            $revenueByMonth[$monthStart->translatedFormat('F Y')] = $revenue;
        }

        // Last 6 months appointments
        $appointmentsByMonth = [];
        for ($i = 5; $i >= 0; $i--) {
            $monthStart = $now->copy()->subMonths($i)->startOfMonth();
            $monthEnd = $now->copy()->subMonths($i)->endOfMonth();
            $count = Appointment::where('clinic_id', $clinicId)
                ->whereBetween('starts_at', [$monthStart, $monthEnd])
                ->count();
            $appointmentsByMonth[$monthStart->translatedFormat('F Y')] = $count;
        }

        // Cancellations/no-shows ratio
        $totalLastMonth = Appointment::where('clinic_id', $clinicId)
            ->whereBetween('starts_at', [$now->copy()->subMonth(), $now])
            ->count();
        $cancelledLastMonth = Appointment::where('clinic_id', $clinicId)
            ->whereBetween('starts_at', [$now->copy()->subMonth(), $now])
            ->whereIn('status', ['cancelled', 'no_show'])
            ->count();

        // Top services by revenue (last 3 months)
        $topServices = Payment::where('clinic_id', $clinicId)
            ->where('status', 'paid')
            ->where('payment_date', '>=', $now->copy()->subMonths(3))
            ->whereNotNull('service_id')
            ->with('service:id,name')
            ->get()
            ->groupBy('service.name')
            ->map(fn ($g) => ['count' => $g->count(), 'revenue' => (float) $g->sum('amount')])
            ->sortByDesc('revenue')
            ->take(5)
            ->toArray();

        // Patient retention
        $totalPatients = Patient::where('clinic_id', $clinicId)->count();
        $activePatients6m = Patient::where('clinic_id', $clinicId)
            ->whereHas('appointments', function ($q) use ($now) {
                $q->where('starts_at', '>=', $now->copy()->subMonths(6));
            })
            ->count();
        $inactivePatients = $totalPatients - $activePatients6m;

        // Pending revenue
        $pending = (float) Payment::where('clinic_id', $clinicId)
            ->whereIn('status', ['pending', 'partial'])
            ->sum('amount');

        // Top patients by revenue
        $topPatients = Payment::where('clinic_id', $clinicId)
            ->where('status', 'paid')
            ->where('payment_date', '>=', $now->copy()->subMonths(6))
            ->with('patient:id,first_name,last_name')
            ->get()
            ->groupBy('patient_id')
            ->map(fn ($g) => ['name' => optional($g->first()->patient)->first_name . ' ' . optional($g->first()->patient)->last_name, 'revenue' => (float) $g->sum('amount'), 'visits' => $g->count()])
            ->sortByDesc('revenue')
            ->take(10)
            ->values()
            ->toArray();

        return [
            'ingresos_por_mes_mxn' => $revenueByMonth,
            'citas_por_mes' => $appointmentsByMonth,
            'total_pacientes' => $totalPatients,
            'pacientes_activos_6m' => $activePatients6m,
            'pacientes_inactivos_6m' => $inactivePatients,
            'tasa_cancelacion_mes_pasado_pct' => $totalLastMonth > 0 ? round(($cancelledLastMonth / $totalLastMonth) * 100, 1) : 0,
            'cobros_pendientes_mxn' => $pending,
            'top_servicios_3m' => $topServices,
            'top_10_pacientes_6m' => $topPatients,
        ];
    }

    protected function extractJson(string $text): ?string
    {
        if (str_starts_with(trim($text), '{')) return trim($text);
        if (preg_match('/```(?:json)?\s*(\{.*?\})\s*```/s', $text, $m)) return $m[1];
        if (preg_match('/\{.*\}/s', $text, $m)) return $m[0];
        return null;
    }
}
