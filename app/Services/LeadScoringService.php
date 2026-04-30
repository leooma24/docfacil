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
     * Idempotente — solo marca hot_alerted_at si al menos UN canal de alerta
     * (email o WhatsApp) salió exitoso. Si todo falló, deja hot_alerted_at
     * null para que el próximo recalc lo reintente.
     *
     * Devuelve ['old' => int|null, 'new' => int, 'alerted' => bool].
     */
    public function updateAndNotify(Prospect $p): array
    {
        $oldScore = $p->lead_score;
        $newScore = $this->calculate($p);

        $update = ['lead_score' => $newScore];
        $crossedHot = ($newScore >= self::HOT_THRESHOLD && $p->hot_alerted_at === null);

        // Si enfría debajo del umbral tibio, resetea para permitir re-alerta futura.
        if ($newScore < self::WARM_THRESHOLD && $p->hot_alerted_at !== null) {
            $update['hot_alerted_at'] = null;
        }

        $p->updateQuietly($update);

        $alertSucceeded = false;
        if ($crossedHot) {
            $alertSucceeded = $this->dispatchAlerts($p->fresh(), $newScore);
            // Solo marcamos como alertado si al menos un canal salió. Si todos
            // fallaron, queda null y el próximo recalc lo reintenta.
            if ($alertSucceeded) {
                $p->updateQuietly(['hot_alerted_at' => now()]);
            }
        }

        return ['old' => $oldScore, 'new' => $newScore, 'alerted' => $alertSucceeded];
    }

    /**
     * Manda email + WhatsApp a Omar. Devuelve true si AL MENOS UN canal salió
     * exitoso (para que updateAndNotify decida si marcar hot_alerted_at).
     */
    protected function dispatchAlerts(Prospect $p, int $score): bool
    {
        $adminEmail = collect(explode(',', (string) config('services.notifications.emails', '')))
            ->map(fn ($e) => trim($e))->filter()->first();
        $adminPhone = config('services.notifications.phone');
        $name = $p->cleanName() ?: 'Sin nombre';
        $emailOk = false;
        $whatsappOk = false;

        // Email — siempre se intenta (Gmail SMTP funciona)
        if (! empty($adminEmail)) {
            try {
                \Illuminate\Support\Facades\Mail::to($adminEmail)
                    ->send(new \App\Mail\LeadHeatedUpMail($p, $score));
                $emailOk = true;
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::error('LeadHeatedUpMail failed', [
                    'prospect_id' => $p->id,
                    'score' => $score,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        // WhatsApp — sendMessage devuelve bool: false en token expirado o API 4xx/5xx
        if (! empty($adminPhone) && ! empty(config('services.whatsapp.token'))) {
            try {
                $msg = "🔥 *Lead caliente · Score {$score}*\n\n" .
                    "*{$name}*" .
                    ($p->specialty ? " · {$p->specialty}" : '') .
                    ($p->city ? "\n📍 {$p->city}" : '') .
                    ($p->phone ? "\n📱 {$p->phone}" : '') .
                    "\n\n⚡ Contacta en las próximas 2 horas — speed-to-lead 21× más conversión." .
                    "\n\n" . url("/ventas/prospectos/{$p->id}/edit");

                $whatsappOk = (bool) app(\App\Services\WhatsAppService::class)->sendMessage($adminPhone, $msg);
                if (! $whatsappOk) {
                    \Illuminate\Support\Facades\Log::warning('Lead heated up WhatsApp returned false', [
                        'prospect_id' => $p->id,
                        'score' => $score,
                    ]);
                }
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::error('Lead heated up WhatsApp threw', [
                    'prospect_id' => $p->id,
                    'score' => $score,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        // Si los dos canales fallan, log explícito — Omar no se va a enterar
        // del lead caliente. Crítico para speed-to-lead.
        if (! $emailOk && ! $whatsappOk) {
            \Illuminate\Support\Facades\Log::error('Lead heated up — ALL CHANNELS FAILED', [
                'prospect_id' => $p->id,
                'score' => $score,
                'will_retry_next_cron' => true,
            ]);
        }

        return $emailOk || $whatsappOk;
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
        // Decay aplica solo si ya HUBO un toque (followup o contacted).
        // Un lead importado pero nunca contactado no se "enfría" — se queda
        // neutral hasta que arranque el outreach. Esto evita penalizar
        // imports masivos donde created_at es viejo pero el outreach apenas
        // empieza.
        $lastTouch = $p->last_followup_at ?? $p->contacted_at;
        if (! $lastTouch) {
            return 0;
        }

        $days = abs((int) now()->diffInDays($lastTouch));
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
