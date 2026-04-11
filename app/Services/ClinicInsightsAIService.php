<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\MedicalRecord;
use App\Models\Patient;
use App\Models\Payment;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ClinicInsightsAIService
{
    public function getInsights(int $clinicId): ?array
    {
        if (!\App\Services\AI::enabled() || \App\Services\AI::dailyLimitReached()) return null;

        // Cache 6 hours (stats don't change that fast)
        return Cache::remember("clinic_insights:{$clinicId}:" . now()->format('Y-m-d-H'), now()->addHours(6), function () use ($clinicId) {
            return $this->generateInsights($clinicId);
        });
    }

    public function invalidate(int $clinicId): void
    {
        // Clear all hour-scoped keys for today
        Cache::forget("clinic_insights:{$clinicId}:" . now()->format('Y-m-d-H'));
    }

    protected function generateInsights(int $clinicId): ?array
    {
        $data = $this->collectStats($clinicId);

        $system = "Eres un asesor de negocios para clínicas médicas/dentales en México. "
            . "Recibes estadísticas del consultorio y generas 3-5 insights accionables en español. "
            . "Cada insight debe ser BREVE (1-2 oraciones) y enfocado en ACCIÓN concreta. "
            . "Usa un tono motivador y profesional. Menciona números reales. "
            . "Prioriza insights que aumenten ingresos, retención de pacientes, o eficiencia. "
            . "Responde en JSON con esta estructura:\n"
            . '{"summary":"resumen general breve","insights":[{"type":"success|warning|opportunity","icon":"emoji","title":"título corto","message":"mensaje accionable"}]}' . "\n"
            . "Tipos permitidos: success (algo positivo), warning (requiere atención), opportunity (oportunidad de crecer). "
            . "RESPONDE SOLO CON EL JSON, sin markdown.";

        $user = "ESTADÍSTICAS DEL CONSULTORIO:\n" . json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n\nJSON:";

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
                        'max_tokens' => 1500,
                        'system' => $system,
                        'messages' => [['role' => 'user', 'content' => $user]],
                    ]);
                $text = $response->successful() ? trim($response->json('content.0.text') ?? '') : null;
            } else {
                $response = Http::timeout(30)
                    ->withToken($apiKey)
                    ->post($config['base_url'] . '/chat/completions', [
                        'model' => $config['model'],
                        'max_tokens' => 1500,
                        'temperature' => 0.5,
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
                'insights' => array_map(function ($i) {
                    return [
                        'type' => in_array($i['type'] ?? 'opportunity', ['success', 'warning', 'opportunity']) ? $i['type'] : 'opportunity',
                        'icon' => (string) ($i['icon'] ?? '💡'),
                        'title' => (string) ($i['title'] ?? ''),
                        'message' => (string) ($i['message'] ?? ''),
                    ];
                }, $parsed['insights'] ?? []),
                'generated_at' => now()->toIso8601String(),
                'stats' => $data,
            ];
        } catch (\Throwable $e) {
            Log::error('Clinic insights AI exception', ['error' => $e->getMessage()]);
            return null;
        }
    }

    protected function collectStats(int $clinicId): array
    {
        $now = now();
        $monthStart = $now->copy()->startOfMonth();
        $lastMonthStart = $now->copy()->subMonth()->startOfMonth();
        $lastMonthEnd = $now->copy()->subMonth()->endOfMonth();

        $monthAppts = Appointment::where('clinic_id', $clinicId)
            ->whereBetween('starts_at', [$monthStart, $now])
            ->count();
        $lastMonthAppts = Appointment::where('clinic_id', $clinicId)
            ->whereBetween('starts_at', [$lastMonthStart, $lastMonthEnd])
            ->count();

        $monthRevenue = Payment::where('clinic_id', $clinicId)
            ->where('status', 'paid')
            ->whereBetween('payment_date', [$monthStart, $now])
            ->sum('amount');
        $lastMonthRevenue = Payment::where('clinic_id', $clinicId)
            ->where('status', 'paid')
            ->whereBetween('payment_date', [$lastMonthStart, $lastMonthEnd])
            ->sum('amount');

        $pendingRevenue = Payment::where('clinic_id', $clinicId)
            ->whereIn('status', ['pending', 'partial'])
            ->sum('amount');

        $topDiagnoses = MedicalRecord::where('clinic_id', $clinicId)
            ->whereBetween('visit_date', [$monthStart, $now])
            ->whereNotNull('diagnosis')
            ->select('diagnosis')
            ->get()
            ->pluck('diagnosis')
            ->map(fn ($d) => mb_substr(trim($d), 0, 50))
            ->countBy()
            ->sortDesc()
            ->take(5)
            ->toArray();

        $topServices = Payment::where('clinic_id', $clinicId)
            ->where('status', 'paid')
            ->whereBetween('payment_date', [$monthStart, $now])
            ->whereNotNull('service_id')
            ->with('service:id,name')
            ->get()
            ->groupBy('service.name')
            ->map(fn ($group) => (float) $group->sum('amount'))
            ->sortDesc()
            ->take(5)
            ->toArray();

        $totalPatients = Patient::where('clinic_id', $clinicId)->count();
        $newPatientsThisMonth = Patient::where('clinic_id', $clinicId)
            ->whereBetween('created_at', [$monthStart, $now])
            ->count();

        $inactivePatients = Patient::where('clinic_id', $clinicId)
            ->whereDoesntHave('appointments', function ($q) {
                $q->where('starts_at', '>=', now()->subMonths(6));
            })
            ->count();

        $noShows = Appointment::where('clinic_id', $clinicId)
            ->whereBetween('starts_at', [$monthStart, $now])
            ->where('status', 'no_show')
            ->count();

        $cancelled = Appointment::where('clinic_id', $clinicId)
            ->whereBetween('starts_at', [$monthStart, $now])
            ->where('status', 'cancelled')
            ->count();

        return [
            'mes_actual' => $now->translatedFormat('F Y'),
            'citas_mes_actual' => $monthAppts,
            'citas_mes_pasado' => $lastMonthAppts,
            'ingresos_mes_actual_mxn' => (float) $monthRevenue,
            'ingresos_mes_pasado_mxn' => (float) $lastMonthRevenue,
            'cobros_pendientes_mxn' => (float) $pendingRevenue,
            'total_pacientes' => $totalPatients,
            'pacientes_nuevos_este_mes' => $newPatientsThisMonth,
            'pacientes_inactivos_6m' => $inactivePatients,
            'top_diagnosticos_mes' => $topDiagnoses,
            'top_servicios_mes_ingresos' => $topServices,
            'citas_no_asistio' => $noShows,
            'citas_canceladas' => $cancelled,
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
