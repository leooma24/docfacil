<?php

namespace App\Filament\Doctor\Actions;

use App\Filament\Doctor\Resources\PaymentResource;
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

        return Action::make($nombre)
            ->label('Cobrar')
            ->icon('heroicon-o-banknotes')
            ->color('success')
            ->modalHeading('Registrar pago')
            ->modalSubmitActionLabel('Guardar pago')
            ->fillForm(fn (array $arguments) => [
                'monto' => $debe($arguments),
                'payment_method' => 'cash',
            ])
            ->form(fn (array $arguments) => [
                Forms\Components\TextInput::make('monto')
                    ->label('Monto que paga')
                    ->helperText('Debe $' . number_format($debe($arguments), 2) . '. Si trae menos, cámbielo y queda como abono.')
                    ->numeric()
                    ->prefix('$')
                    ->required()
                    ->minValue(0.01)
                    ->maxValue($debe($arguments)),
                Forms\Components\Select::make('payment_method')
                    ->label('Forma de pago')
                    ->options(PaymentResource::FORMAS_DE_PAGO)
                    ->required(),
            ])
            ->action(function (array $data, array $arguments) use ($cobros) {
                $adeudos = $cobros($arguments);
                Payment::abonarEnOrden($adeudos, (float) $data['monto'], $data['payment_method'] ?? null);

                $queda = round($adeudos->map->fresh()->sum(fn (Payment $c) => $c->remaining), 2);
                Notification::make()
                    ->title('Pago registrado')
                    ->body($queda > 0 ? 'Todavía debe $' . number_format($queda, 2) . '.' : 'Ya no debe nada.')
                    ->success()
                    ->send();
            });
    }
}
