<?php

namespace App\Filament\Doctor\Widgets;

use App\Models\Payment;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class PendingPayments extends BaseWidget
{
    protected static ?int $sort = 5;

    protected int|string|array $columnSpan = 1;

    protected static ?string $heading = 'Cobros pendientes';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Payment::query()
                    ->where('clinic_id', auth()->user()->clinic_id)
                    ->whereIn('status', ['pending', 'partial'])
                    ->with(['patient', 'service'])
                    ->orderBy('payment_date', 'desc')
                    ->limit(10)
            )
            ->columns([
                Tables\Columns\TextColumn::make('patient.first_name')
                    ->label('Paciente')
                    ->formatStateUsing(fn ($record) => "{$record->patient->first_name} {$record->patient->last_name}"),
                Tables\Columns\TextColumn::make('service.name')
                    ->label('Servicio')
                    ->limit(20),
                Tables\Columns\TextColumn::make('amount')
                    ->label('Monto')
                    ->money('MXN')
                    ->weight('bold'),
                Tables\Columns\BadgeColumn::make('status')
                    ->label('')
                    ->formatStateUsing(fn ($state) => $state === 'pending' ? 'Pendiente' : 'Parcial')
                    ->colors(['warning' => fn () => true]),
            ])
            ->actions([
                Tables\Actions\Action::make('mark_paid')
                    ->label('Cobrar')
                    ->icon('heroicon-o-banknotes')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(fn (Payment $record) => $record->update(['status' => 'paid'])),
            ])
            ->emptyStateHeading('Sin pendientes')
            ->emptyStateDescription('Todos los cobros al día')
            ->paginated(false);
    }
}
