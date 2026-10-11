<?php

namespace App\Filament\Doctor\Actions;

use App\Models\Payment;
use Closure;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Notifications\Notification;
use Illuminate\Support\Collection;

/**
 * Cobrar lo que se debe: un clic para abrir, otro para guardar.
 *
 * El monto ya viene puesto con todo lo que se debe; si el paciente trae
 * menos, se cambia y queda como abono. Lo usan los adeudos vencidos del
 * escritorio, "Lo que sigue" del perfil y el cobro abierto, para que en los
 * tres lados se cobre igual.
 */
class CobrarAbono
{
    /**
     * @param  Closure(array): Collection<int, Payment>  $cobros  los adeudos a cobrar, según los argumentos de la acción
     */
    public static function make(string $nombre, Closure $cobros): Action
    {
        $debe = fn (array $arguments) => round($cobros($arguments)->sum(fn (Payment $c) => $c->remaining), 2);
        // Su saldo a favor (anticipo) se descuenta solo: aquí va lo que falta.
        $aFavor = fn (array $arguments) => round(min((float) ($cobros($arguments)->first()?->patient?->saldoAFavor() ?? 0), $debe($arguments)), 2);
        $falta = fn (array $arguments) => round($debe($arguments) - $aFavor($arguments), 2);

        return Action::make($nombre)
            ->label('Cobrar')
            ->icon('heroicon-o-banknotes')
            ->color('success')
            ->modalHeading('Registrar pago')
            ->modalIcon('heroicon-o-banknotes')
            ->modalIconColor('success')
            ->modalDescription(fn (array $arguments) => 'Debe $' . number_format($debe($arguments), 2)
                . ($aFavor($arguments) > 0 ? ' · $' . number_format($aFavor($arguments), 2) . ' se pagan con su saldo a favor' : '') . '.')
            ->modalWidth('lg')
            ->modalSubmitActionLabel('Guardar pago')
            ->fillForm(fn (array $arguments) => [
                'monto' => $falta($arguments),
                'payment_method' => 'cash',
            ])
            ->form(fn (array $arguments) => [
                Forms\Components\TextInput::make('monto')
                    ->label('Monto que paga')
                    ->helperText(fn () => 'Debe $' . number_format($debe($arguments), 2)
                        . ($aFavor($arguments) > 0 ? ', y $' . number_format($aFavor($arguments), 2) . ' se pagan con su saldo a favor' : '')
                        . '. Si trae menos, cámbielo y queda como abono.')
                    ->numeric()
                    ->prefix('$')
                    ->required()
                    ->live(onBlur: true)
                    ->minValue(fn () => $aFavor($arguments) > 0 ? 0 : 0.01)
                    ->maxValue($falta($arguments)),
                // Con "Combinado": una parte en efectivo y el resto con
                // tarjeta o transferencia, cada una con su recibo.
                ...Cobro::camposDeForma(fn (Forms\Get $get) => round((float) $get('monto'), 2)),
            ])
            ->action(function (array $data, array $arguments) use ($cobros) {
                $adeudos = $cobros($arguments);
                Cobro::abonar($adeudos, (float) $data['monto'], $data);

                $queda = round($adeudos->map->fresh()->sum(fn (Payment $c) => $c->remaining), 2);
                Notification::make()
                    ->title('Pago registrado')
                    ->body($queda > 0 ? 'Todavía debe $' . number_format($queda, 2) . '.' : 'Ya no debe nada.')
                    ->success()
                    ->send();
            });
    }
}
