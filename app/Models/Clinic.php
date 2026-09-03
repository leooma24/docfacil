<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

class Clinic extends Model
{
    protected $fillable = [
        'name', 'slug', 'phone', 'email', 'address', 'country',
        'working_hours',
        'minutos_entre_citas',
        'city', 'state', 'zip_code', 'logo', 'google_review_url', 'plan',
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
            'working_hours' => 'array',
            'minutos_entre_citas' => 'integer',
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

    public function consultationSettings(): HasOne
    {
        return $this->hasOne(ClinicConsultationSettings::class);
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

        // Referral cascading: si esta clinica fue referida, el referente
        // gana 1 mes gratis por este pago (hasta cap de 12 meses/año).
        // Silencioso si no hay referral asociado o si ya alcanzo el cap.
        try {
            \App\Models\Referral::grantCascadeReward($this);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Referral cascade failed on activatePlan', [
                'clinic_id' => $this->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function planIsPaid(): bool
    {
        return in_array($this->plan, ['basico', 'profesional', 'clinica'], true);
    }

    /**
     * ¿El consultorio sigue dentro de su prueba gratis de 15 días?
     *
     * Solo aplica al plan free: quien ya paga no está "en prueba".
     */
    public function enPruebaVigente(): bool
    {
        return $this->plan === 'free'
            && $this->trial_ends_at !== null
            && $this->trial_ends_at->isFuture();
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
    // ─────────────────────────────────────────────────────────────
    //  Horario de atención
    //
    //  Antes no existía: un paciente podía pedir cita el domingo a las 3 de
    //  la mañana desde el portal público y el sistema la aceptaba. El
    //  07:00–21:00 del calendario solo pinta la cuadrícula, no impide nada.
    // ─────────────────────────────────────────────────────────────

    /** Días de la semana, en el orden en que los espera un mexicano. */
    public const DIAS = [
        'lunes' => 'Lunes',
        'martes' => 'Martes',
        'miercoles' => 'Miércoles',
        'jueves' => 'Jueves',
        'viernes' => 'Viernes',
        'sabado' => 'Sábado',
        'domingo' => 'Domingo',
    ];

    /**
     * Horario típico de un consultorio, para que un consultorio nuevo no
     * arranque sin nada y acepte citas a cualquier hora.
     */
    public static function horarioPorDefecto(): array
    {
        $entreSemana = ['abre' => '09:00', 'cierra' => '19:00'];

        return [
            'lunes' => $entreSemana,
            'martes' => $entreSemana,
            'miercoles' => $entreSemana,
            'jueves' => $entreSemana,
            'viernes' => $entreSemana,
            'sabado' => ['abre' => '09:00', 'cierra' => '14:00'],
            'domingo' => null,   // cerrado
        ];
    }

    /** El horario configurado, o el típico si el consultorio no lo ha tocado. */
    public function horario(): array
    {
        return array_merge(self::horarioPorDefecto(), $this->working_hours ?? []);
    }

    /**
     * Minutos de aire entre un paciente y el siguiente.
     *
     * Limpiar el sillón, esterilizar y guardar toma tiempo, y ese tiempo no
     * es la cita: si no se aparta, el doctor arranca tarde y se le cae el
     * resto del día.
     *
     * Se topa a 120 para que un dedo pesado no deje la agenda sin huecos.
     */
    public function minutosEntreCitas(): int
    {
        return max(0, min(120, (int) ($this->minutos_entre_citas ?? 0)));
    }


    /** Cómo se llama aquí el día de una fecha. */
    private static function nombreDelDia(\DateTimeInterface $momento): string
    {
        return array_keys(self::DIAS)[(int) $momento->format('N') - 1];
    }

    public function closures(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\ClinicClosure::class);
    }

    /**
     * ¿Ese día el consultorio está cerrado por vacaciones, feriado o similar?
     *
     * Sin esto, el portal público seguía aceptando citas para toda la semana
     * que el doctor se iba de vacaciones.
     */
    public function cierraEse(\DateTimeInterface $momento): ?\App\Models\ClinicClosure
    {
        $dia = $momento->format('Y-m-d');

        return $this->closures()
            ->whereDate('starts_on', '<=', $dia)
            ->whereDate('ends_on', '>=', $dia)
            ->first();
    }

    /**
     * ¿El consultorio atiende en ese momento?
     *
     * La hora de cierre es el límite: si cierran a las 19:00, una cita puede
     * empezar a las 18:45 pero no a las 19:00.
     */
    public function atiendeEn(\DateTimeInterface $momento): bool
    {
        // Vacaciones y feriados le ganan al horario normal.
        if ($this->cierraEse($momento)) {
            return false;
        }

        $dia = $this->horario()[self::nombreDelDia($momento)] ?? null;

        if (! $dia || empty($dia['abre']) || empty($dia['cierra'])) {
            return false;
        }

        $hora = $momento->format('H:i');

        if ($hora < $dia['abre'] || $hora >= $dia['cierra']) {
            return false;
        }

        // Muchos consultorios cierran a comer. Sin esto, el sistema aceptaba
        // citas a media hora de comida como si nada.
        if (self::hayDescanso($dia) && $hora >= $dia['descanso_de'] && $hora < $dia['descanso_a']) {
            return false;
        }

        return true;
    }

    /** ¿Ese día tienen hora de comida configurada? */
    private static function hayDescanso(array $dia): bool
    {
        return ! empty($dia['descanso_de']) && ! empty($dia['descanso_a']);
    }

    /**
     * ¿Cabe completa una cita entre esas horas?
     *
     * Que empiece dentro del horario no basta: una limpieza de una hora a
     * las 18:30, cerrando a las 19:00, deja al paciente a media consulta.
     */
    public function cabeLaCita(\DateTimeInterface $inicio, \DateTimeInterface $fin): bool
    {
        if (! $this->atiendeEn($inicio)) {
            return false;
        }

        $dia = $this->horario()[self::nombreDelDia($inicio)];

        // Una cita que cruza la medianoche nunca cabe en un día de trabajo.
        if ($fin->format('Y-m-d') !== $inicio->format('Y-m-d')) {
            return false;
        }

        if ($fin->format('H:i') > $dia['cierra']) {
            return false;
        }

        // Tampoco puede cruzar la hora de comida: empezar a las 13:30 una
        // consulta de una hora, comiendo de 14:00 a 16:00, deja al paciente
        // esperando en el sillón.
        if (self::hayDescanso($dia)
            && $inicio->format('H:i') < $dia['descanso_de']
            && $fin->format('H:i') > $dia['descanso_de']) {
            return false;
        }

        return true;
    }

    /**
     * Cómo se dicen los días en plural.
     *
     * De lunes a viernes no cambian ("los lunes"), pero sábado y domingo sí
     * ("los sábados"). Sin esto salía "Los Domingo el consultorio no abre".
     */
    private const DIAS_EN_PLURAL = [
        'lunes' => 'lunes',
        'martes' => 'martes',
        'miercoles' => 'miércoles',
        'jueves' => 'jueves',
        'viernes' => 'viernes',
        'sabado' => 'sábados',
        'domingo' => 'domingos',
    ];

    /** Frase para decirle al paciente cuándo sí puede venir. */
    public function horarioDelDia(\DateTimeInterface $momento): string
    {
        if ($cierre = $this->cierraEse($momento)) {
            $motivo = $cierre->reason ? " ({$cierre->reason})" : '';

            return "Ese día el consultorio está cerrado{$motivo}. Elige otra fecha y con gusto te atendemos.";
        }

        $clave = self::nombreDelDia($momento);
        $dia = $this->horario()[$clave] ?? null;
        $nombre = self::DIAS_EN_PLURAL[$clave];

        if (! $dia) {
            return "Los {$nombre} el consultorio no abre. Elige otro día y con gusto te atendemos.";
        }

        if (self::hayDescanso($dia)) {
            return "Los {$nombre} atendemos de {$dia['abre']} a {$dia['descanso_de']} "
                . "y de {$dia['descanso_a']} a {$dia['cierra']}.";
        }

        return "Los {$nombre} atendemos de {$dia['abre']} a {$dia['cierra']}.";
    }

    public static function featuresForPlan(string $plan): array
    {
        $basico = [
            'pdf_prescriptions',       // Recetas PDF con cédula y logo
            'whatsapp_reminders',      // Recordatorios auto + manual 1-clic
            'whatsapp_payment',        // Cobro por WhatsApp
            'qr_checkin',              // Check-in con QR
            'basic_dashboard',
            'odontogram',              // Odontograma FDI interactivo — diferenciador
                                       // dental clave; va en Basico para que el
                                       // dentista solo (90% del ICP) lo tenga.
            'patient_portal',          // Portal del paciente. Va en Basico: lo
                                       // tienen todos los planes de pago, no
                                       // Free.
            'expenses',                // Gastos y corte del mes. Va en Basico
                                       // porque es justo lo que hace que el
                                       // doctor deje su hoja de calculo.
        ];
        // Nota: treatment_plans sigue siendo ADD-ON de pago ($129/mes),
        // gestionado via ClinicAddon. Clinic::hasFeature() consulta addons
        // activos adicionalmente al plan base.
        //
        // recall_automation SI viene en Pro. Estaba escondido como add-on de
        // $49 que nadie compro, siendo que es lo que mas dinero le genera a un
        // dentista: el paciente que debia volver a los 6 meses y no volvio ya
        // es suyo, nada mas hay que hablarle. Sigue en config/addons.php para
        // que un Basico lo pueda comprar suelto.
        $profesional = array_merge($basico, [
            'consent_forms',           // Consentimientos + firma digital
            'multi_doctor',            // Hasta 3 doctores
            'advanced_reports',        // Reportes avanzados
            'smart_alerts',            // Alertas inteligentes
            'priority_support',
            'waitlist',                // Lista de espera + notificacion auto al cancelar
            'public_booking',          // Portal publico /clinica/{slug}/agendar
            'recall_automation',       // A quien ya le toca volver. Lo que mas
                                       // ingreso le genera a un consultorio dental.
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
    /**
     * ¿Este feature viene en el plan, sin contar add-ons comprados?
     *
     * Sirve para no venderle a alguien algo que ya trae incluido: el
     * marketplace de add-ons enseñaba "Recall automático $49/mes" a un Pro
     * que ya lo tiene.
     */
    public function planIncluyeFeature(string $feature): bool
    {
        if ($this->enPruebaVigente()) {
            return in_array($feature, self::featuresForPlan('profesional'), true);
        }

        if ($this->planIsPaid() && ! $this->planIsActive()) {
            return false;
        }

        return in_array($feature, self::featuresForPlan($this->plan), true);
    }

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

        // Durante la prueba de 15 dias el consultorio nuevo trae Pro completo,
        // para que alcance a ver todo lo que el producto hace antes de decidir.
        // Al vencer se queda con lo que pague (Pro o Basico) o cae a Free, que
        // no incluye features pagados.
        //
        // Ojo: esto NO cambia featuresForPlan('free'), que es lo que la landing
        // y la pagina de planes muestran como "Free". Es solo el permiso real
        // mientras la prueba sigue viva.
        if ($this->enPruebaVigente()) {
            return in_array($feature, self::featuresForPlan('profesional'), true);
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

    /**
     * Cómo va el programa Fundador.
     *
     * El número sale de contar las clínicas marcadas como fundadoras, no de
     * un texto que alguien tenga que acordarse de actualizar. Por eso no
     * puede mentir, y por eso no se reinicia como lo hacía el reloj de
     * "oferta de lanzamiento" que terminaba cada fin de mes y volvía a
     * empezar.
     *
     * Se cachea 5 minutos: la landing recibe tráfico de buscadores y este
     * conteo no cambia de un minuto a otro.
     *
     * @return array{total:int, tomados:int, quedan:int, hay:bool}
     */
    public static function lugaresDeFundador(): array
    {
        $total = max(0, (int) config('founders.seats', 10));

        // Si la base no contesta, la portada NO se cae: se enseña el programa
        // como si no hubiera nadie dentro. Una página de marketing que da 500
        // por un conteo es mucho peor que un número momentáneamente bajo.
        try {
            $tomados = \Illuminate\Support\Facades\Cache::remember(
                'fundadores.tomados',
                now()->addMinutes(5),
                fn () => static::withoutGlobalScopes()->where('is_founder', true)->count(),
            );
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('No se pudo contar los lugares de fundador', [
                'error' => $e->getMessage(),
            ]);

            $tomados = 0;
        }

        $tomados = min((int) $tomados, $total);

        return [
            'total' => $total,
            'tomados' => $tomados,
            'quedan' => $total - $tomados,
            'hay' => ($total - $tomados) > 0,
        ];
    }
}
