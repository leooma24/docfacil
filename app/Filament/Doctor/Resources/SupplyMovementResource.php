<?php

namespace App\Filament\Doctor\Resources;

use App\Filament\Doctor\Resources\SupplyMovementResource\Pages;
use App\Models\SupplyMovement;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

/**
 * El kardex: todo lo que entró, salió o se perdió, en orden.
 *
 * Es de solo lectura a propósito. Un movimiento es un hecho histórico: editarlo
 * o borrarlo rompería la única razón por la que el inventario se puede creer.
 * Si alguien capturó mal, se corrige con otro movimiento — igual que en
 * contabilidad, y por la misma razón.
 *
 * Se captura desde el catálogo de Insumos, que es donde el doctor tiene el
 * insumo enfrente y ve su stock.
 */
class SupplyMovementResource extends Resource
{
    protected static ?string $model = SupplyMovement::class;

    protected static ?string $slug = 'movimientos-insumos';

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?string $navigationLabel = 'Movimientos';

    protected static ?string $modelLabel = 'Movimiento';

    protected static ?string $pluralModelLabel = 'Movimientos';

    protected static ?int $navigationSort = 9;

    protected static bool $shouldRegisterNavigation = true;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('clinic_id', auth()->user()->clinic_id)
            ->with(['supply', 'user'])
            ->latest('occurred_at');
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('occurred_at')
                    ->label('Cuándo')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                Tables\Columns\TextColumn::make('supply.name')
                    ->label('Insumo')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('type')
                    ->label('Tipo')
                    ->badge()
                    ->formatStateUsing(fn (SupplyMovement $record) => $record->typeLabel())
                    ->color(fn (string $state) => match ($state) {
                        'in' => 'success',
                        'waste' => 'warning',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('quantity')
                    ->label('Cantidad')
                    ->formatStateUsing(function (SupplyMovement $record) {
                        $signo = $record->addsToStock() ? '+' : '−';

                        return $signo . number_format((float) $record->quantity, 0) . ' ' . ($record->supply?->unit ?? '');
                    })
                    ->color(fn (SupplyMovement $record) => $record->addsToStock() ? 'success' : 'danger')
                    ->weight('bold')
                    ->sortable(),
                Tables\Columns\TextColumn::make('waste_reason')
                    ->label('Motivo')
                    ->badge()
                    ->color('warning')
                    ->formatStateUsing(fn (SupplyMovement $record) => $record->wasteReasonLabel())
                    ->placeholder('—')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('unit_cost')
                    ->label('Costo unitario')
                    ->money('MXN', locale: 'es_MX')
                    ->placeholder('—')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('reason')
                    ->label('Nota')
                    ->wrap()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Capturó')
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('supply_id')
                    ->label('Insumo')
                    ->relationship('supply', 'name'),
                Tables\Filters\SelectFilter::make('type')
                    ->label('Tipo')
                    ->options(SupplyMovement::TYPES),
                Tables\Filters\SelectFilter::make('waste_reason')
                    ->label('Motivo de merma')
                    ->options(SupplyMovement::WASTE_REASONS),
                Tables\Filters\Filter::make('occurred_at')
                    ->label('Fecha')
                    ->form([
                        \Filament\Forms\Components\DatePicker::make('desde')->label('Desde'),
                        \Filament\Forms\Components\DatePicker::make('hasta')->label('Hasta'),
                    ])
                    ->query(function (Builder $query, array $data) {
                        return $query
                            ->when($data['desde'] ?? null, fn (Builder $q, $fecha) => $q->whereDate('occurred_at', '>=', $fecha))
                            ->when($data['hasta'] ?? null, fn (Builder $q, $fecha) => $q->whereDate('occurred_at', '<=', $fecha));
                    }),
            ])
            ->actions([])
            ->bulkActions([])
            ->defaultSort('occurred_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSupplyMovements::route('/'),
        ];
    }
}
