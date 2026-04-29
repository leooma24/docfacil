<?php

namespace App\Services;

use App\Models\Prospect;

/**
 * Calcula lead_score (0-100) para priorizar el outreach de Omar.
 *
 *   FIT (max 50)        — qué tan ICP es
 *   ENGAGEMENT (max 50) — qué tan caliente está
 *   - NEGATIVE          — penalizadores
 *   - DECAY             — sin actividad reciente
 *
 * Override states:
 *   - converted → 100
 *   - lost       → 0
 *   - unsubscribed → 0
 *
 * Buckets para UI:
 *   80-100 → 🔥 caliente (priorizar HOY)
 *   50-79  → 🌡️ tibio    (esta semana)
 *   30-49  → 🧊 frío     (cuando haya tiempo)
 *   0-29   → ❄️ congelado (skip o nurture pasivo)
 */
class LeadScoringService
{
    public const HOT_THRESHOLD = 80;
    public const WARM_THRESHOLD = 50;
    public const COLD_THRESHOLD = 30;

    /**
     * Especialidades core de DocFácil (dentistas) → score alto.
     */
    private const DENTAL_SPECIALTIES = [
        'Odontología General', 'Odontologia General', 'Odontología',
        'Ortodoncia', 'Odontopediatría', 'Odontopediatria',
        'Endodoncia', 'Periodoncia', 'Implantología', 'Implantologia',
        'Cirugía Maxilofacial', 'Cirugia Maxilofacial', 'Cirugía Oral',
        'Odontología Estética', 'Odontologia Estetica', 'Estética Dental',
        'Rehabilitación Oral', 'Rehabilitacion Oral', 'Prostodoncia',
        'Odontología Restauradora', 'Odontologia Integral',
        'Radiología Dental',
    ];

    /**
     * Anti-persona explícita (médicos no-dentales). Se atienden pero con score bajo.
     */
    private const ANTI_PERSONA_SPECIALTIES = [
        'Pediatría', 'Pediatria', 'Ginecología', 'Ginecologia',
        'Medicina General', 'Dermatología', 'Dermatologia',
        'Psiquiatría', 'Psiquiatria', 'Cardiología', 'Cardiologia',
    ];

    /**
     * Top ciudades con tracción real (>30 prospectos). Score boost.
     */
    private const HIGH_TRACTION_CITIES = [
        'Hermosillo', 'Culiacán', 'Ciudad Obregón', 'Mérida', 'Tijuana',
        'Ciudad Juárez', 'Santiago de Querétaro', 'Saltillo', 'Mazatlán',
        'Ciudad de México', 'Los Mochis', 'León', 'Monterrey', 'Torreón',
        'Cancún', 'Metepec', 'Cuernavaca',
    ];

    /**
     * Recalcula score, persiste, y dispara alertas si el lead cruzó a caliente.
     * Idempotente — no duplica alertas: solo manda si hot_alerted_at es null.
     * Si el lead enfría debajo de WARM_THRESHOLD, resetea hot_alerted_at para
     * permitir alerta futura cuando vuelva a calentar.
     *
     * Devuelve ['old' => int|null, 'new' => int, 'alerted' => bool].
     */
    public function updateAndNotify(Prospect $p): array
    {
        $oldScore = $p->lead_score;
        $newScore = $this->calculate($p);

        $update = ['lead_score' => $newScore];
        $alerted = false;

        // Cruce hacia caliente: dispara alerta una sola vez por evento de calentamiento.
        if ($newScore >= self::HOT_THRESHOLD && $p->hot_alerted_at === null) {
            $update['hot_alerted_at'] = now();
            $alerted = true;
        }

        // Si vuelve a enfriarse debajo del umbral tibio, resetea para permitir
        // que vuelva a alertar si recalienta más tarde.
        if ($newScore < self::WARM_THRESHOLD && $p->hot_alerted_at !== null) {
            $update['hot_alerted_at'] = null;
        }

        $p->updateQuietly($update);

        if ($alerted) {
            $this->dispatchAlerts($p->fresh(), $newScore);
        }

        return ['old' => $oldScore, 'new' => $newScore, 'alerted' => $alerted];
    }

    /**
     * Manda email + WhatsApp a Omar avisando que el lead se calentó.
     * Cualquier fallo se loggea pero no rompe la actualización del score.
     */
    protected function dispatchAlerts(Prospect $p, int $score): void
    {
        $adminEmail = collect(explode(',', (string) config('services.notifications.emails', '')))
            ->map(fn ($e) => trim($e))->filter()->first();
        $adminPhone = config('services.notifications.phone');
        $name = $p->cleanName() ?: 'Sin nombre';

        // Email — siempre se intenta (Gmail SMTP funciona)
        if (! empty($adminEmail)) {
            try {
                \Illuminate\Support\Facades\Mail::to($adminEmail)
                    ->send(new \App\Mail\LeadHeatedUpMail($p, $score));
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning('LeadHeatedUpMail failed', [
                    'prospect_id' => $p->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        // WhatsApp — solo si está configurado el número y la API funciona
        if (! empty($adminPhone) && ! empty(config('services.whatsapp.token'))) {
            try {
                $msg = "🔥 *Lead caliente · Score {$score}*\n\n" .
                    "*{$name}*" .
                    ($p->specialty ? " · {$p->specialty}" : '') .
                    ($p->city ? "\n📍 {$p->city}" : '') .
                    ($p->phone ? "\n📱 {$p->phone}" : '') .
                    "\n\n⚡ Contacta en las próximas 2 horas — speed-to-lead 21× más conversión." .
                    "\n\n" . url("/ventas/prospectos/{$p->id}/edit");

                app(\App\Services\WhatsAppService::class)->sendMessage($adminPhone, $msg);
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning('Lead heated up WhatsApp failed', [
                    'prospect_id' => $p->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    public function calculate(Prospect $p): int
    {
        // Overrides absolutos
        if ($p->unsubscribed_at !== null) return 0;
        if ($p->status === 'lost') return 0;
        if ($p->status === 'converted') return 100;

        $score = 0;

        // ── FIT (max 50) ──
        $score += $this->scoreSpecialty($p->specialty);    // 0-30
        $score += $this->scoreCity($p->city);              // 0-10
        $score += $this->scoreContactInfo($p);             // 0-10

        // ── ENGAGEMENT (max 50) ──
        $score += $this->scoreEmailClicks($p);             // 0-25
        $score += $this->scoreDemo($p);                    // 0-15
        $score += $this->scoreStatus($p->status);          // 0-20

        // ── NEGATIVE ──
        $score -= $this->penaltyAntiPersona($p);           // 0-15
        $score -= $this->penaltyObjections($p);            // 0-20
        $score -= $this->penaltyDecay($p);                 // 0-15

        return max(0, min(100, $score));
    }

    private function scoreSpecialty(?string $specialty): int
    {
        if (empty($specialty)) return 10; // sin info, neutral
        if (in_array($specialty, self::DENTAL_SPECIALTIES, true)) return 30;
        if (in_array($specialty, self::ANTI_PERSONA_SPECIALTIES, true)) return 5;
        // Especialidad desconocida pero no anti-persona explícita
        return 15;
    }

    private function scoreCity(?string $city): int
    {
        if (empty($city)) return 0;
        if (in_array($city, self::HIGH_TRACTION_CITIES, true)) return 10;
        return 5; // ciudad MX no top, todavía vale algo
    }

    private function scoreContactInfo(Prospect $p): int
    {
        $hasPhone = !empty($p->phone);
        $hasEmail = !empty($p->email);
        if ($hasPhone && $hasEmail) return 10;
        if ($hasPhone || $hasEmail) return 5;
        return 0;
    }

    private function scoreEmailClicks(Prospect $p): int
    {
        $clicks = $p->emailEvents()->where('event_type', 'click')->count();
        return match (true) {
            $clicks >= 3 => 25,
            $clicks === 2 => 20,
            $clicks === 1 => 12,
            default => 0,
        };
    }

    private function scoreDemo(Prospect $p): int
    {
        if ($p->demo_completed_at) return 15;
        if ($p->demo_scheduled_at) return 10;
        return 0;
    }

    private function scoreStatus(?string $status): int
    {
        return match ($status) {
            'trial' => 20,
            'interested' => 15,
            'contacted' => 5,
            default => 0, // 'new' o null
        };
    }

    private function penaltyAntiPersona(Prospect $p): int
    {
        // Si la specialty ya es anti-persona Y además hay objeción 'specific_govt' (IMSS),
        // doble red flag — penalizar fuerte.
        $isAntiSpecialty = $p->specialty && in_array($p->specialty, self::ANTI_PERSONA_SPECIALTIES, true);
        $hasGovtObjection = is_array($p->objections_faced) && in_array('specific_govt', $p->objections_faced, true);

        if ($isAntiSpecialty && $hasGovtObjection) return 15;
        if ($hasGovtObjection) return 10;
        return 0;
    }

    private function penaltyObjections(Prospect $p): int
    {
        if (!is_array($p->objections_faced) || empty($p->objections_faced)) return 0;

        $penalty = 0;
        foreach ($p->objections_faced as $obj) {
            $penalty += match ($obj) {
                'specific_old' => 10,
                'tech_has_system' => 5,
                'price_excel', 'tech_paper_works' => 3,
                default => 0,
            };
        }
        return min($penalty, 20);
    }

    private function penaltyDecay(Prospect $p): int
    {
        $lastTouch = $p->last_followup_at ?? $p->contacted_at ?? $p->created_at;
        if (!$lastTouch) return 0;

        $days = abs(now()->diffInDays($lastTouch));
        return match (true) {
            $days >= 60 => 15,
            $days >= 30 => 10,
            $days >= 14 => 5,
            default => 0,
        };
    }

    /**
     * Etiqueta visual para UI (con emoji).
     */
    public static function bucketLabel(?int $score): string
    {
        $score ??= 0;
        return match (true) {
            $score >= self::HOT_THRESHOLD => '🔥 Caliente',
            $score >= self::WARM_THRESHOLD => '🌡️ Tibio',
            $score >= self::COLD_THRESHOLD => '🧊 Frío',
            default => '❄️ Congelado',
        };
    }

    public static function bucketColor(?int $score): string
    {
        $score ??= 0;
        return match (true) {
            $score >= self::HOT_THRESHOLD => 'danger',  // rojo intenso (atiende YA)
            $score >= self::WARM_THRESHOLD => 'warning', // amarillo
            $score >= self::COLD_THRESHOLD => 'info',    // azul claro
            default => 'gray',
        };
    }
}
