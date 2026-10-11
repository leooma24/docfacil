<?php

namespace App\Filament\Doctor\Actions;

use App\Filament\Doctor\Resources\PaymentResource;
use App\Models\Payment;
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
     * del campo `amount` del mismo formulario.
     */
    public static function camposDePago(bool $conSinPago = true): array
    {
        $total = fn (Forms\Get $get) => round((float) $get('amount'), 2);

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
            Forms\Components\Placeholder::make('resumen')
                ->label('')
                ->content(function (Forms\Get $get) use ($total) {
                    $t = $total($get);
                    $hoy = self::pagaHoy($get('como'), $t, (float) $get('recibido'));

                    return 'Total $' . number_format($t, 2) . ' · paga hoy $' . number_format($hoy, 2) . ' · queda $' . number_format(max(0, $t - $hoy), 2);
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
     * Crea el cobro y registra lo que paga hoy, en un solo paso.
     */
    public static function nuevo(array $data, int $pacienteId, ?int $citaId = null): Payment
    {
        $total = round((float) $data['amount'], 2);
        $partes = self::partes(self::pagaHoy($data['como'] ?? 'todo', $total, (float) ($data['recibido'] ?? 0)), $data);

        return DB::transaction(function () use ($data, $pacienteId, $citaId, $total, $partes) {
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

            foreach ($partes as [$monto, $forma]) {
                $cobro->registrarAbono($monto, $forma);
            }

            return $cobro->fresh();
        });
    }

    /** Abona a varios adeudos, del más viejo al más nuevo, con cada forma de pago. */
    public static function abonar(iterable $adeudos, float $pagaHoy, array $data): void
    {
        DB::transaction(function () use ($adeudos, $pagaHoy, $data) {
            foreach (self::partes(round($pagaHoy, 2), $data) as [$monto, $forma]) {
                Payment::abonarEnOrden(collect($adeudos)->map->fresh(), $monto, $forma);
            }
        });
    }
}
