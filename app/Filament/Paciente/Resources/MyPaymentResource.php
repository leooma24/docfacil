<?php

namespace App\Filament\Paciente\Resources;

use App\Filament\Paciente\Resources\MyPaymentResource\Pages;
use App\Models\Payment;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class MyPaymentResource extends Resource
{
    protected static ?string $model = Payment::class;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';

    protected static ?string $navigationLabel = 'Mis Pagos';

    protected static ?string $modelLabel = 'Pago';

    protected static ?string $pluralModelLabel = 'Mis Pagos';

    protected static ?string $slug = 'mis-pagos';

    public static function getEloquentQuery(): Builder
    {
        $patient = auth()->user()->patient;

        return parent::getEloquentQuery()
            ->where('patient_id', $patient?->id);
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('payment_date')
                    ->label('Fecha')
                    ->date('d/m/Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('service.name')
                    ->label('Servicio')
                    ->placeholder('Sin servicio'),
                Tables\Columns\TextColumn::make('amount')
                    ->label('Monto')
                    ->money('MXN')
                    ->sortable(),
                Tables\Columns\BadgeColumn::make('status')
                    ->label('Estado')
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        'paid' => 'Pagado',
                        'pending' => 'Pendiente',
                        'partial' => 'Parcial',
                        'refunded' => 'Reembolsado',
                    })
                    ->colors([
                        'success' => 'paid',
                        'warning' => fn ($state) => in_array($state, ['pending', 'partial']),
                        'danger' => 'refunded',
                    ]),
                Tables\Columns\BadgeColumn::make('payment_method')
                    ->label('Método')
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        'cash' => 'Efectivo',
                        'card' => 'Tarjeta',
                        'transfer' => 'Transferencia',
                        'other' => 'Otro',
                    }),
            ])
            ->defaultSort('payment_date', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMyPayments::route('/'),
        ];
    }
}
