<?php

namespace App\Filament\Sales\Resources;

use App\Filament\Sales\Resources\CommissionResource\Pages;
use App\Models\Commission;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class CommissionResource extends Resource
{
    protected static ?string $model = Commission::class;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';

    protected static ?string $navigationLabel = 'Mis Comisiones';

    protected static ?string $modelLabel = 'Comisión';

    protected static ?string $pluralModelLabel = 'Comisiones';

    protected static ?int $navigationSort = 2;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('user_id', auth()->id());
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit($record): bool
    {
        return false;
    }

    public static function canDelete($record): bool
    {
        return false;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('earned_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('clinic.name')->label('Clínica')->searchable(),
                Tables\Columns\BadgeColumn::make('tier')
                    ->label('Pago')
                    ->formatStateUsing(fn ($state) => $state === 'first' ? '1ra mitad' : '2da mitad')
                    ->colors(['primary' => 'first', 'info' => 'second']),
                Tables\Columns\TextColumn::make('plan_at_sale')
                    ->label('Plan')
                    ->formatStateUsing(fn ($state) => ucfirst($state)),
                Tables\Columns\TextColumn::make('amount')
                    ->label('Monto')
                    ->money('MXN')
                    ->weight('bold')
                    ->sortable(),
                Tables\Columns\BadgeColumn::make('status')
                    ->label('Estado')
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'paid',
                        'danger' => 'clawed_back',
                        'gray' => 'cancelled',
                    ])
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        'pending' => 'Pendiente',
                        'paid' => 'Pagada',
                        'clawed_back' => 'Clawback',
                        'cancelled' => 'Cancelada',
                    }),
                Tables\Columns\TextColumn::make('earned_at')->label('Fecha ganada')->date('d/m/Y')->sortable(),
                Tables\Columns\TextColumn::make('paid_at')->label('Fecha pagada')->date('d/m/Y')->placeholder('—'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Estado')
                    ->options([
                        'pending' => 'Pendiente',
                        'paid' => 'Pagada',
                        'clawed_back' => 'Clawback',
                        'cancelled' => 'Cancelada',
                    ]),
                Tables\Filters\SelectFilter::make('tier')
                    ->label('Pago')
                    ->options([
                        'first' => '1ra mitad',
                        'second' => '2da mitad',
                    ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCommissions::route('/'),
        ];
    }
}
