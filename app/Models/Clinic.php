<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Clinic extends Model
{
    protected $fillable = [
        'name', 'slug', 'phone', 'email', 'address', 'country',
        'city', 'state', 'zip_code', 'logo', 'plan',
        'trial_ends_at', 'is_active',
        'is_beta', 'beta_tier', 'is_founder', 'founder_price',
        'beta_starts_at', 'beta_ends_at', 'beta_notes',
        'show_as_case_study', 'case_study_logo', 'case_study_testimonial',
        'onboarding_status',
        'sold_at',
        'first_payment_received_at', 'second_payment_received_at', 'cancelled_at',
        'is_demo', 'demo_expires_at',
        // Stripe / Cashier + ciclo de facturación
        'stripe_id', 'pm_type', 'pm_last_four',
        'billing_cycle', 'payment_method',
        'plan_started_at', 'plan_ends_at', 'auto_renew',
    ];

    // Nota: sold_by_user_id NO está en $fillable a propósito.
    // Solo puede asignarse via forceFill() en Register.php cuando viene ?vnd=
    // o mediante el admin con un código dedicado. Esto evita que un form admin
    // cualquiera reasigne comisiones de un vendedor a otro.

    protected function casts(): array
    {
        return [
            'trial_ends_at' => 'datetime',
            'beta_starts_at' => 'datetime',
            'beta_ends_at' => 'datetime',
            'is_active' => 'boolean',
            'is_beta' => 'boolean',
            'is_founder' => 'boolean',
            'show_as_case_study' => 'boolean',
            'founder_price' => 'decimal:2',
            'sold_at' => 'datetime',
            'first_payment_received_at' => 'datetime',
            'second_payment_received_at' => 'datetime',
            'cancelled_at' => 'datetime',
            'is_demo' => 'boolean',
            'demo_expires_at' => 'datetime',
            'plan_started_at' => 'datetime',
            'plan_ends_at' => 'datetime',
            'auto_renew' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Clinic $clinic) {
            if (empty($clinic->slug)) {
                $clinic->slug = Str::slug($clinic->name);
            }
        });
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function doctors(): HasMany
    {
        return $this->hasMany(Doctor::class);
    }

    public function patients(): HasMany
    {
        return $this->hasMany(Patient::class);
    }

    public function services(): HasMany
    {
        return $this->hasMany(Service::class);
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    public function soldBy(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'sold_by_user_id');
    }

    public function commissions(): HasMany
    {
        return $this->hasMany(Commission::class);
    }

    public function speiPayments(): HasMany
    {
        return $this->hasMany(SpeiPayment::class);
    }

    /**
     * Activa un plan por la duración del ciclo especificado.
     * Usado tanto por Stripe (al recibir payment_succeeded) como por SPEI (al aprobar pago).
     */
    public function activatePlan(string $plan, string $billingCycle, string $paymentMethod): void
    {
        $days = $billingCycle === 'annual' ? 365 : 30;
        $now = now();

        // Si extiende un plan actual no expirado, se suma desde plan_ends_at, no desde hoy.
        $from = ($this->plan_ends_at && $this->plan_ends_at->isFuture())
            ? $this->plan_ends_at
            : $now;

        $this->update([
            'plan' => $plan,
            'billing_cycle' => $billingCycle,
            'payment_method' => $paymentMethod,
            'plan_started_at' => $this->plan_started_at ?? $now,
            'plan_ends_at' => $from->copy()->addDays($days),
            'is_active' => true,
        ]);
    }

    public function planIsPaid(): bool
    {
        return in_array($this->plan, ['basico', 'profesional', 'clinica'], true);
    }

    public function planIsActive(): bool
    {
        if (!$this->planIsPaid()) {
            return false;
        }
        return $this->plan_ends_at && $this->plan_ends_at->isFuture();
    }

    /**
     * Nombre del plan tal cual se muestra al usuario.
     */
    public function planDisplayName(): string
    {
        return self::displayNameForPlan($this->plan);
    }

    public static function displayNameForPlan(?string $plan): string
    {
        return match ($plan) {
            'free' => 'Free',
            'basico' => 'Básico',
            'profesional' => 'Pro',
            'clinica' => 'Clínica',
            default => ucfirst((string) $plan),
        };
    }

    /**
     * Source of truth para qué features cubre cada plan. Debe coincidir con la
     * promesa de la landing — si alguien paga Pro debe tener lo que le prometimos.
     *
     * Si agregas un feature aquí, agrégalo también a la landing y al brochure.
     */
    public static function featuresForPlan(string $plan): array
    {
        $basico = [
            'pdf_prescriptions',       // Recetas PDF con cédula y logo
            'whatsapp_reminders',      // Recordatorios auto + manual 1-clic
            'whatsapp_payment',        // Cobro por WhatsApp
            'qr_checkin',              // Check-in con QR
            'basic_dashboard',
        ];
        // Nota: recall_automation y treatment_plans son ADD-ONS de pago
        // ($49 y $129/mes), gestionados via ClinicAddon. Ya no estan en
        // featuresForPlan — Clinic::hasFeature() consulta addons activos
        // adicionalmente al plan base.
        $profesional = array_merge($basico, [
            'odontogram',              // Odontograma interactivo (solo aplica a dentistas)
            'consent_forms',           // Consentimientos + firma digital
            'multi_doctor',            // Hasta 3 doctores
            'advanced_reports',        // Reportes avanzados
            'smart_alerts',            // Alertas inteligentes
            'priority_support',
            'waitlist',                // Lista de espera + notificacion auto al cancelar
            'public_booking',          // Portal publico /clinica/{slug}/agendar
        ]);
        $clinica = array_merge($profesional, [
            'unlimited_doctors',
            'per_doctor_reports',      // Reportes + produccion individual por doctor
            'dedicated_onboarding',
        ]);

        return match ($plan) {
            'free' => [],
            'basico' => $basico,
            'profesional' => $profesional,
            'clinica' => $clinica,
            default => [],
        };
    }

    /**
     * ¿Este consultorio tiene acceso al feature X según su plan?
     * Uso: $clinic->hasFeature('odontogram')
     *
     * Si el plan ya venció (trial/beta expirado), los features pagados dejan de
     * funcionar aunque figuren en featuresForPlan(). Así evitamos que un user
     * siga usando Pro después de que su trial expiró y no pagó.
     */
    public function hasFeature(string $feature): bool
    {
        // Feature de plan pagado pero el plan ya venció → bloquear.
        if ($this->planIsPaid() && !$this->planIsActive()) {
            return false;
        }

        // Trial/beta expirado: bloquea features pagados.
        if ($this->plan === 'free' && $this->trial_ends_at && $this->trial_ends_at->isPast()) {
            return false;
        }
        if ($this->is_beta && $this->beta_ends_at && $this->beta_ends_at->isPast()) {
            return false;
        }

        // 1) Feature incluido en el plan base
        if (in_array($feature, self::featuresForPlan($this->plan), true)) {
            return true;
        }

        // 2) Feature disponible via add-on activo
        $addonsConfig = config('addons', []);
        foreach ($addonsConfig as $addon) {
            if (($addon['feature_flag'] ?? null) !== $feature) continue;
            $hasActive = $this->addons()
                ->where('addon_slug', $addon['slug'])
                ->active()
                ->exists();
            if ($hasActive) return true;
        }

        return false;
    }

    public function addons(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\ClinicAddon::class);
    }

    /**
     * Scope para filtrar clínicas que tienen un feature pagado activo.
     * Lo usamos en comandos programados (reminders WhatsApp, portal paciente, etc.)
     * para no disparar features a clínicas que no las tienen contratadas.
     *
     * Nota: esto es una aproximación SQL — replica la lógica de hasFeature()
     * pero a nivel query. Si agregas un check en hasFeature(), agrégalo también aquí.
     */
    public function scopeWithActiveFeature($query, string $feature)
    {
        $plansWithFeature = collect(['free', 'basico', 'profesional', 'clinica'])
            ->filter(fn ($p) => in_array($feature, self::featuresForPlan($p), true))
            ->values()
            ->all();

        if (empty($plansWithFeature)) {
            return $query->whereRaw('1=0'); // ninguna clínica califica
        }

        return $query
            ->where('is_active', true)
            ->whereIn('plan', $plansWithFeature)
            ->where(function ($q) {
                $q->where(function ($q2) {
                    // Plan pagado todavía activo
                    $q2->whereIn('plan', ['basico', 'profesional', 'clinica'])
                       ->where('plan_ends_at', '>', now());
                })->orWhere(function ($q2) {
                    // Beta vigente (cualquier plan, incluso 'free' con is_beta=true)
                    $q2->where('is_beta', true)->where('beta_ends_at', '>', now());
                });
            });
    }
}
