<?php

namespace App\Filament\Doctor\Actions;

use App\Filament\Doctor\Resources\PaymentResource;
use App\Models\Patient;
use App\Models\Payment;
use App\Models\PaymentReceipt;
use Filament\Forms;
use Illuminate\Support\Facades\DB;

/**
 * Cómo paga hoy el paciente: lo comparten "Cobrar" de la ficha, "Cobrar" de
 * la cita y "Cobrar $300" (10-oct-2026).
 *
 * - "Paga todo" / "Una parte" / "Sin pago hoy", como en la caja de
 *   DaleControl: nadie tiene que restar.
 * - Pago combinado: una parte en efectivo y el resto con tarjeta o
 *   transferencia. Cada parte deja su recibo, así el corte cuadra con la
 *   terminal.
 */
class Cobro
{
    public const COMBINADO = 'combinado';

    /** Formas de pago del formulario: las de siempre más "Combinado". */
    public static function formas(): array
    {
        return PaymentResource::FORMAS_DE_PAGO + [self::COMBINADO => 'Combinado (efectivo + otra)'];
    }

    /**
     * Los campos de "¿cuánto paga hoy?" para un cobro nuevo. El total se lee
     * del campo `amount` del mismo formulario; si tiene saldo a favor, se
     * descuenta y "Paga todo" es lo que falta.
     *
     * @param  (\Closure(): float)|null  $aFavor
     */
    public static function camposDePago(bool $conSinPago = true, ?\Closure $aFavor = null): array
    {
        $total = fn (Forms\Get $get) => round(max(0, (float) $get('amount') - min((float) $get('amount'), $aFavor ? $aFavor() : 0)), 2);

        return [
            Forms\Components\ToggleButtons::make('como')
                ->label('¿Cuánto paga hoy?')
                ->options(array_filter([
                    'todo' => 'Paga todo',
                    'parte' => 'Una parte',
                    'nada' => $conSinPago ? 'Sin pago hoy' : null,
                ]))
                ->colors(['todo' => 'success', 'parte' => 'warning', 'nada' => 'gray'])
                ->default('todo')
                ->inline()
                ->live()
                ->required(),
            Forms\Components\TextInput::make('recibido')
                ->label('¿Cuánto deja hoy?')
                ->numeric()
                ->prefix('$')
                ->live(onBlur: true)
                ->minValue(0.01)
                ->maxValue(fn (Forms\Get $get) => $total($get))
                ->validationMessages(['max' => 'No puede dejar más del total.'])
                ->visible(fn (Forms\Get $get) => $get('como') === 'parte')
                ->required(fn (Forms\Get $get) => $get('como') === 'parte'),
            ...self::camposDeForma(fn (Forms\Get $get) => self::pagaHoy($get('como'), $total($get), (float) $get('recibido')),
                fn (Forms\Get $get) => $get('como') !== 'nada'),
            // El resumen, como un recibo: total, lo que pone su saldo a favor,
            // lo que paga hoy y lo que queda.
            Forms\Components\Placeholder::make('resumen')
                ->hiddenLabel()
                ->columnSpanFull()
                ->content(function (Forms\Get $get) use ($total) {
                    $t = $total($get);
                    $hoy = self::pagaHoy($get('como'), $t, (float) $get('recibido'));
                    $saldo = round((float) $get('amount') - $t, 2);

                    return new \Illuminate\Support\HtmlString(self::recibo([
                        ['Total', (float) $get('amount'), '#0f172a'],
                        ...($saldo > 0 ? [['Con su saldo a favor', -$saldo, '#047857']] : []),
                        ['Paga hoy', $hoy, '#0f766e'],
                        ['Queda debiendo', max(0, $t - $hoy), max(0, $t - $hoy) > 0 ? '#b45309' : '#64748b'],
                    ]));
                }),
        ];
    }

    /**
     * Forma de pago, con los campos del pago combinado.
     *
     * @param  \Closure(Forms\Get): float  $pagaHoy  lo que entra hoy, para validar el efectivo
     */
    public static function camposDeForma(\Closure $pagaHoy, ?\Closure $visible = null): array
    {
        $visible ??= fn () => true;

        return [
            Forms\Components\Select::make('payment_method')
                ->label('¿Cómo paga?')
                ->options(self::formas())
                ->default('cash')
                ->live()
                ->visible($visible)
                ->required($visible),
            Forms\Components\TextInput::make('efectivo')
                ->label('En efectivo')
                ->numeric()
                ->prefix('$')
                ->minValue(0.01)
                ->maxValue(fn (Forms\Get $get) => round($pagaHoy($get) - 0.01, 2))
                ->validationMessages(['max' => 'Si paga todo en efectivo, elija "Efectivo". El resto tiene que ir con la otra forma.'])
                ->helperText(fn (Forms\Get $get) => 'El resto, $' . number_format(max(0, $pagaHoy($get) - (float) $get('efectivo')), 2) . ', con la otra forma.')
                ->live(onBlur: true)
                ->visible(fn (Forms\Get $get) => $visible($get) && $get('payment_method') === self::COMBINADO)
                ->required(fn (Forms\Get $get) => $visible($get) && $get('payment_method') === self::COMBINADO),
            Forms\Components\Select::make('otra_forma')
                ->label('El resto con')
                ->options(collect(PaymentResource::FORMAS_DE_PAGO)->except('cash')->all())
                ->default('card')
                ->visible(fn (Forms\Get $get) => $visible($get) && $get('payment_method') === self::COMBINADO)
                ->required(fn (Forms\Get $get) => $visible($get) && $get('payment_method') === self::COMBINADO),
        ];
    }

    /**
     * Lo que toca cobrarle (11-oct-2026): su cita de hoy o, si no, la última
     * que se atendió en estos 30 días y no se ha cobrado. Si en la consulta se
     * anotaron procedimientos, esos y su total; si no, el servicio de la cita.
     *
     * @return array{appointment_id: int, service_id: ?int, concepto: ?string, amount: float, texto: string}|null
     */
    public static function sugerencia(Patient $paciente): ?array
    {
        $cita = \App\Models\Appointment::withoutGlobalScopes()->with(['service', 'procedures.service'])
            ->where('clinic_id', $paciente->clinic_id)->where('patient_id', $paciente->id)
            ->where(fn ($q) => $q->whereIn('status', ['completed', 'in_progress'])->orWhere(fn ($w) => $w->whereIn('status', \App\Models\Appointment::ESTADOS_QUE_OCUPAN)->whereDate('starts_at', today())))
            ->where('starts_at', '>=', now()->subDays(30))->where('starts_at', '<=', now()->endOfDay())
            ->whereDoesntHave('payments')
            ->latest('starts_at')->first();
        if (! $cita) {
            return null;
        }

        $cuando = $cita->starts_at->isToday() ? 'de hoy' : 'del ' . $cita->starts_at->locale('es')->isoFormat('D [de] MMMM');
        if ($cita->procedures->isNotEmpty()) {
            $concepto = $cita->procedures->map(fn ($p) => ($p->service?->name ?? 'Procedimiento')
                . ($p->tooth_number ? ' en ' . $p->tooth_number : '') . ((int) $p->quantity > 1 ? ' (' . (int) $p->quantity . ')' : ''))->implode(', ');
            $total = round((float) $cita->procedures->sum(fn ($p) => (float) $p->unit_price * max(1, (int) $p->quantity)), 2);
            $soloUno = $cita->procedures->count() === 1 && (int) $cita->procedures->first()->quantity <= 1;

            return ['appointment_id' => $cita->id, 'service_id' => $soloUno ? $cita->procedures->first()->service_id : null,
                'concepto' => $soloUno ? null : mb_substr($concepto, 0, 255), 'amount' => $total, 'texto' => "Lo de su cita {$cuando}: {$concepto}."];
        }
        if ($cita->service) {
            return ['appointment_id' => $cita->id, 'service_id' => $cita->service_id, 'concepto' => null,
                'amount' => round((float) $cita->service->price, 2), 'texto' => "Lo de su cita {$cuando}: {$cita->service->name}."];
        }

        return null;
    }

    /** @param list<array{0: string, 1: float, 2: string}> $renglones */
    public static function recibo(array $renglones): string
    {
        $celdas = collect($renglones)->map(fn ($r) => '<td style="padding:10px 12px;text-align:center;">'
            . '<div style="font-size:11px;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:.03em;">' . e($r[0]) . '</div>'
            . '<div style="font-size:20px;font-weight:800;color:' . $r[2] . ';margin-top:2px;">' . ($r[1] < 0 ? '−' : '') . '$' . number_format(abs($r[1]), 2) . '</div></td>')->implode('');

        return '<table style="width:100%;border-collapse:separate;border-spacing:0;background:#f8fafc;border:1px solid #e2e8f0;border-radius:12px;"><tr>' . $celdas . '</tr></table>';
    }

    public static function pagaHoy(?string $como, float $total, float $recibido): float
    {
        return match ($como) {
            'parte' => round(min($recibido, $total), 2),
            'nada' => 0.0,
            default => round($total, 2),
        };
    }

    /**
     * Lo que entra hoy, repartido por forma de pago: [[monto, forma], ...].
     *
     * @return list<array{0: float, 1: string}>
     */
    public static function partes(float $pagaHoy, array $data): array
    {
        if ($pagaHoy <= 0) {
            return [];
        }
        if (($data['payment_method'] ?? null) !== self::COMBINADO) {
            return [[$pagaHoy, $data['payment_method'] ?? 'cash']];
        }

        $efectivo = round((float) ($data['efectivo'] ?? 0), 2);
        if ($efectivo <= 0 || $efectivo >= $pagaHoy) {
            throw new \InvalidArgumentException('En un pago combinado, el efectivo tiene que ser menos de lo que paga.');
        }

        return [[$efectivo, 'cash'], [round($pagaHoy - $efectivo, 2), $data['otra_forma'] ?? 'card']];
    }

    /** La forma que se anota en el cobro: la del primer pago, o efectivo. */
    public static function formaPrincipal(array $data): string
    {
        $forma = $data['payment_method'] ?? 'cash';

        return $forma === self::COMBINADO ? 'cash' : $forma;
    }

    /**
     * Crea el cobro y registra lo que paga hoy, en un solo paso. Si el
     * paciente tiene saldo a favor (un anticipo), se descuenta primero y
     * "Paga todo" es solo lo que falta.
     */
    public static function nuevo(array $data, int $pacienteId, ?int $citaId = null): Payment
    {
        $total = round((float) $data['amount'], 2);
        $paciente = Patient::where('clinic_id', auth()->user()->clinic_id)->findOrFail($pacienteId);
        // La cita que se sugirió al abrir, solo si es de este paciente.
        $citaId ??= \App\Models\Appointment::where('clinic_id', $paciente->clinic_id)->where('patient_id', $paciente->id)
            ->whereKey($data['appointment_id'] ?? null)->value('id');
        $deSaldo = round(min($paciente->saldoAFavor(), $total), 2);
        $partes = self::partes(self::pagaHoy($data['como'] ?? 'todo', round($total - $deSaldo, 2), (float) ($data['recibido'] ?? 0)), $data);

        return DB::transaction(function () use ($data, $pacienteId, $citaId, $total, $deSaldo, $partes) {
            $cobro = Payment::create([
                'clinic_id' => auth()->user()->clinic_id,
                'patient_id' => $pacienteId,
                'appointment_id' => $citaId,
                'service_id' => $data['service_id'] ?? null,
                'notes' => filled($data['concepto'] ?? null) ? $data['concepto'] : null,
                'amount' => $total,
                'amount_paid' => 0,
                'status' => 'pending',
                'payment_method' => self::formaPrincipal($data),
                'payment_date' => today(),
                'due_date' => today(),
            ]);

            if ($deSaldo > 0) {
                $cobro->registrarAbono($deSaldo, PaymentReceipt::SALDO_A_FAVOR, 'Con su saldo a favor');
            }
            foreach ($partes as [$monto, $forma]) {
                $cobro->fresh()->registrarAbono($monto, $forma);
            }

            return $cobro->fresh();
        });
    }

    /**
     * Abona a varios adeudos, del más viejo al más nuevo: primero con su
     * saldo a favor, si tiene, y luego lo que paga hoy con cada forma.
     */
    public static function abonar(iterable $adeudos, float $pagaHoy, array $data): void
    {
        $adeudos = collect($adeudos);

        DB::transaction(function () use ($adeudos, $pagaHoy, $data) {
            $paciente = $adeudos->first()?->patient;
            $debe = round($adeudos->map->fresh()->sum(fn (Payment $c) => $c->remaining), 2);
            $deSaldo = $paciente ? round(min($paciente->saldoAFavor(), $debe), 2) : 0.0;
            if ($deSaldo > 0) {
                Payment::abonarEnOrden($adeudos->map->fresh(), $deSaldo, PaymentReceipt::SALDO_A_FAVOR);
            }

            foreach (self::partes(round($pagaHoy, 2), $data) as [$monto, $forma]) {
                Payment::abonarEnOrden($adeudos->map->fresh(), $monto, $forma);
            }
        });
    }

    /**
     * Un anticipo: el dinero entra hoy a la caja. Si debe algo, primero paga
     * eso; lo demás queda a su favor y se descuenta solo en el siguiente cobro.
     */
    public static function anticipo(array $data, int $pacienteId): float
    {
        $partes = self::partes(round((float) $data['amount'], 2), $data);

        return DB::transaction(function () use ($partes, $pacienteId) {
            $adeudos = Payment::where('clinic_id', auth()->user()->clinic_id)->where('patient_id', $pacienteId)
                ->withBalance()->yaToca()->get();
            $sobra = [];
            foreach ($partes as [$monto, $forma]) {
                $debe = round($adeudos->map->fresh()->sum(fn (Payment $c) => $c->remaining), 2);
                $aDeuda = round(min($monto, $debe), 2);
                if ($aDeuda > 0) {
                    Payment::abonarEnOrden($adeudos->map->fresh(), $aDeuda, $forma);
                }
                if ($monto - $aDeuda > 0.004) {
                    $sobra[] = [round($monto - $aDeuda, 2), $forma];
                }
            }

            $aFavor = round(array_sum(array_column($sobra, 0)), 2);
            if ($aFavor > 0) {
                $anticipo = Payment::create([
                    'clinic_id' => auth()->user()->clinic_id,
                    'patient_id' => $pacienteId,
                    'notes' => 'Anticipo',
                    'es_anticipo' => true,
                    'amount' => $aFavor,
                    'amount_paid' => 0,
                    'status' => 'pending',
                    'payment_method' => $sobra[0][1],
                    'payment_date' => today(),
                ]);
                foreach ($sobra as [$monto, $forma]) {
                    $anticipo->fresh()->registrarAbono($monto, $forma);
                }
            }

            return $aFavor;
        });
    }
}
