<?php

namespace App\Models;

use App\Models\Concerns\BelongsToClinic;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class Payment extends Model
{
    use BelongsToClinic;

    protected $fillable = [
        'clinic_id', 'patient_id', 'appointment_id', 'service_id', 'payment_plan_id', 'installment_number',
        'amount', 'amount_paid', 'payment_method', 'status', 'notes',
        'payment_date', 'due_date',
        // La factura la hace el contador (no hay CFDI): solo se anota.
        'factura_solicitada', 'factura_enviada_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'amount_paid' => 'decimal:2',
            'payment_date' => 'date',
            'due_date' => 'date',
            'factura_solicitada' => 'boolean',
            'factura_enviada_at' => 'datetime',
        ];
    }

    public function clinic(): BelongsTo
    {
        return $this->belongsTo(Clinic::class);
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    /** El plan de pagos al que pertenece, si es enganche o mensualidad. */
    public function paymentPlan(): BelongsTo
    {
        return $this->belongsTo(PaymentPlan::class);
    }

    /** Los abonos, cada uno con su fecha y forma de pago. */
    public function receipts(): HasMany
    {
        return $this->hasMany(PaymentReceipt::class);
    }

    /**
     * Lo pagado siempre es la suma de los recibos. Se crea el cobro ya
     * pagado (la consulta), se marca pagado a mano o se le sube lo abonado
     * desde el formulario: en todos los casos queda el recibo de lo que
     * entró. Al crearlo, con la fecha del cobro; después, con la de hoy.
     */
    protected static function booted(): void
    {
        static::created(fn (Payment $cobro) => $cobro->cuadrarRecibos($cobro->payment_date ?? now()));
        static::updated(function (Payment $cobro) {
            if ($cobro->wasChanged(['amount_paid', 'status', 'amount'])) {
                $cobro->cuadrarRecibos(now());
                $cobro->paymentPlan?->actualizarEstado();
            }
        });
    }

    private function cuadrarRecibos(\DateTimeInterface $fecha): void
    {
        $pagado = $this->status === 'paid' ? (float) $this->amount : (float) $this->amount_paid;
        $diferencia = round($pagado - (float) $this->receipts()->sum('amount'), 2);

        if (abs($diferencia) < 0.01) {
            return;
        }

        $this->receipts()->create([
            'clinic_id' => $this->clinic_id,
            'amount' => $diferencia,
            'payment_method' => $this->payment_method,
            'paid_at' => $fecha,
            'notes' => $diferencia < 0 ? ($this->status === 'refunded' ? 'Devolución' : 'Ajuste: se corrigió lo pagado') : null,
        ]);
    }

    /**
     * Registra un abono de hoy y actualiza saldo y estado.
     */
    public function registrarAbono(float $monto, ?string $formaDePago = null, ?string $notas = null): PaymentReceipt
    {
        $monto = round($monto, 2);

        if ($monto <= 0 || $monto > round($this->remaining, 2)) {
            throw new \InvalidArgumentException('El abono debe ser mayor a cero y no más de lo que se debe ($' . number_format($this->remaining, 2) . ').');
        }

        return DB::transaction(function () use ($monto, $formaDePago, $notas) {
            $recibo = $this->receipts()->create([
                'clinic_id' => $this->clinic_id,
                'amount' => $monto,
                'payment_method' => $formaDePago ?? $this->payment_method,
                'paid_at' => now(),
                'notes' => $notas,
            ]);

            $pagado = round((float) $this->amount_paid + $monto, 2);
            $this->update([
                'amount_paid' => min($pagado, (float) $this->amount),
                'status' => $pagado >= (float) $this->amount ? 'paid' : 'partial',
            ]);

            return $recibo;
        });
    }

    /**
     * Un pago que cubre varios adeudos: se abona del más viejo al más nuevo.
     *
     * El paciente que debe dos mensualidades y trae $1,500 no paga "la
     * primera" y "la segunda" por separado: da el dinero y ya. Aquí se
     * reparte solo, con la misma fecha y forma de pago.
     */
    public static function abonarEnOrden(iterable $cobros, float $monto, ?string $formaDePago = null): void
    {
        $cobros = collect($cobros)
            ->filter(fn (Payment $c) => $c->remaining > 0)
            ->sortBy(fn (Payment $c) => ($c->due_date ?? $c->payment_date)?->timestamp ?? 0)
            ->values();

        $monto = round($monto, 2);
        $debe = round($cobros->sum(fn (Payment $c) => $c->remaining), 2);

        if ($monto <= 0 || $monto > $debe) {
            throw new \InvalidArgumentException('El pago debe ser mayor a cero y no más de lo que se debe ($' . number_format($debe, 2) . ').');
        }

        DB::transaction(function () use ($cobros, $monto, $formaDePago) {
            $queda = $monto;
            foreach ($cobros as $cobro) {
                if ($queda <= 0) {
                    break;
                }
                $parte = round(min($queda, $cobro->remaining), 2);
                $cobro->registrarAbono($parte, $formaDePago);
                $queda = round($queda - $parte, 2);
            }
        });
    }

    /**
     * Saldo pendiente = amount - amount_paid. Nunca negativo.
     */
    protected function remaining(): Attribute
    {
        return Attribute::make(
            get: fn () => max(0, (float) $this->amount - (float) $this->amount_paid)
        );
    }

    /**
     * Vencido = tiene saldo pendiente Y due_date < hoy.
     */
    protected function isOverdue(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->remaining > 0
                && $this->due_date !== null
                && $this->due_date->isPast()
        );
    }

    /**
     * Scope de cobros con saldo pendiente (pending o partial).
     */
    public function scopeWithBalance(Builder $query): Builder
    {
        return $query->whereIn('status', ['pending', 'partial']);
    }

    /**
     * Lo que ya se debe o ya se pagó: deja fuera las mensualidades de un plan
     * que todavía no vencen, para que 20 mensualidades de ortodoncia no
     * saturen cobros, saldos y perfiles desde el primer día.
     */
    public function scopeYaToca(Builder $query): Builder
    {
        return $query->where(fn ($q) => $q->whereNull('payment_plan_id')
            ->orWhereDate('due_date', '<=', today())
            ->orWhere('amount_paid', '>', 0)
            ->orWhere('status', 'paid'));
    }

    /**
     * Scope de cobros vencidos (saldo pendiente + due_date pasada).
     */
    public function scopeOverdue(Builder $query): Builder
    {
        return $query->withBalance()
            ->whereNotNull('due_date')
            ->whereDate('due_date', '<', now()->toDateString());
    }

    /**
     * Lo que de verdad entró a la caja en un periodo.
     *
     * Antes cada widget hacía su propia cuenta: where('status','paid')
     * ->sum('amount'). Eso deja fuera los abonos — un paciente que dio
     * $2,000 de un tratamiento de $5,000 contaba como cero, y el doctor veía
     * menos ingreso del que había recibido.
     *
     * Aquí cuenta cada abono el día que entró (ver PaymentReceipt). Todo lo que muestre ingresos usa esto, para que el escritorio
     * y el corte del mes no digan números distintos.
     */
    public static function cobradoEntre(
        int $clinicId,
        \DateTimeInterface $desde,
        \DateTimeInterface $hasta,
    ): float {
        // Cuenta los recibos por su fecha: el abono de hoy entra hoy aunque
        // el cobro sea del mes pasado.
        return (float) PaymentReceipt::withoutGlobalScopes()
            ->where('clinic_id', $clinicId)
            // Hasta el final del día: SQLite guarda la fecha con hora, y un
            // periodo de un solo día (el "cobrado hoy") salía en cero.
            ->whereBetween('paid_at', [$desde->format('Y-m-d') . ' 00:00:00', $hasta->format('Y-m-d') . ' 23:59:59'])
            ->sum('amount');
    }

    /**
     * Lo que sigue pendiente de cobrar de ese periodo.
     */
    public static function porCobrarEntre(
        int $clinicId,
        \DateTimeInterface $desde,
        \DateTimeInterface $hasta,
    ): float {
        return (float) static::withoutGlobalScopes()
            ->where('clinic_id', $clinicId)
            ->whereIn('status', ['pending', 'partial'])
            // Sin mensualidades que no vencen: con "Este año" el periodo
            // sumaba más que el total que le deben (auditoría del 12-oct-2026).
            ->yaToca()
            // Hasta el final del día, igual que en cobradoEntre().
            ->whereBetween('payment_date', [$desde->format('Y-m-d'), $hasta->format('Y-m-d') . ' 23:59:59'])
            ->selectRaw('SUM(amount - amount_paid) as saldo')
            ->value('saldo');
    }

    /**
     * Todo lo que le deben al consultorio hoy, sea del mes que sea.
     *
     * Antes el escritorio sumaba el monto de los cobros 'pending' y se
     * saltaba los abonos: un tratamiento de $2,500 con $1,000 dados no salía
     * en "Por cobrar", y el doctor veía $0 con pacientes debiéndole. Igual
     * que con cobradoEntre(), todo lo que muestre saldos usa esto.
     */
    public static function saldoPorCobrar(int $clinicId): float
    {
        return (float) static::withoutGlobalScopes()
            ->where('clinic_id', $clinicId)
            ->withBalance()
            // Las mensualidades de un plan que todavía no vencen no se deben
            // hoy: 20 mensualidades de ortodoncia no son $16,000 por cobrar.
            ->yaToca()
            ->selectRaw('SUM(amount - amount_paid) as saldo')
            ->value('saldo');
    }
}
