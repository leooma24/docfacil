<?php

namespace App\Filament\Doctor\Resources\PaymentPlanResource\RelationManagers;

use App\Filament\Doctor\Resources\PaymentResource;
use App\Models\Payment;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

/** El enganche y las mensualidades, cada una con su fecha y su estado. */
class MensualidadesRelationManager extends RelationManager
{
    protected static string $relationship = 'payments';

    protected static ?string $title = 'Mensualidades';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('installment_number')->label('#')
                    ->formatStateUsing(fn (int $state) => $state === 0 ? 'Enganche' : (string) $state),
                Tables\Columns\TextColumn::make('due_date')->label('Vence')->date('d/m/Y'),
                Tables\Columns\TextColumn::make('amount')->label('Monto')->money('MXN'),
                Tables\Columns\TextColumn::make('amount_paid')->label('Pagado')->money('MXN'),
                Tables\Columns\TextColumn::make('estado')->label('Estado')->badge()
                    ->state(fn (Payment $p) => match (true) {
                        $p->status === 'paid' => 'Pagada',
                        $p->due_date?->isBefore(today()) => 'Vencida',
                        $p->due_date?->isToday() => 'Vence hoy',
                        $p->status === 'partial' => 'Con abono',
                        default => 'Por pagar',
                    })
                    ->color(fn (string $state) => match ($state) {
                        'Pagada' => 'success',
                        'Vencida' => 'danger',
                        'Vence hoy', 'Con abono' => 'warning',
                        default => 'gray',
                    }),
            ])
            ->actions([
                Tables\Actions\Action::make('pagar')
                    ->label('Registrar pago')
                    ->icon('heroicon-o-banknotes')
                    ->color('success')
                    ->visible(fn (Payment $p) => $p->remaining > 0)
                    ->form(fn (Payment $p) => [
                        Forms\Components\TextInput::make('monto')->label('Monto')->numeric()->prefix('$')
                            ->default($p->remaining)->required()->minValue(0.01)->maxValue((float) $p->remaining),
                        Forms\Components\Select::make('payment_method')->label('Forma de pago')
                            ->options(PaymentResource::FORMAS_DE_PAGO)->default('cash')->required(),
                    ])
                    ->action(function (Payment $p, array $data) {
                        $p->registrarAbono((float) $data['monto'], $data['payment_method']);
                        Notification::make()->title('Pago registrado')->success()->send();
                    }),
            ])
            ->paginated(false);
    }

    public function isReadOnly(): bool
    {
        return false;
    }
}
