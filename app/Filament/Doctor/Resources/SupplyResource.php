<?php

namespace App\Filament\Doctor\Resources;

use App\Filament\Doctor\Resources\SupplyResource\Pages;
use App\Models\Supply;
use App\Models\SupplyMovement;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

/**
 * El catálogo de insumos del consultorio, con su stock a la vista.
 *
 * El stock no se captura aquí: se capturan movimientos. El número que aparece
 * en la columna es la suma del kardex, y por eso no hay ningún campo para
 * "ajustar el stock a mano" — un ajuste es un movimiento más, con su motivo.
 */
class SupplyResource extends Resource
{
    protected static ?string $model = Supply::class;

    protected static ?string $slug = 'insumos';

    protected static ?string $navigationIcon = 'heroicon-o-cube';

    protected static ?string $navigationLabel = 'Insumos';

    protected static ?string $modelLabel = 'Insumo';

    protected static ?string $pluralModelLabel = 'Insumos';

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?int $navigationSort = 8;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('clinic_id', auth()->user()->clinic_id)
            // El stock de cada fila en la misma consulta: sin esto la tabla
            // dispara una consulta por insumo.
            ->withStock();
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['name', 'sku', 'category'];
    }

    public static function getGlobalSearchResultDetails(\Illuminate\Database\Eloquent\Model $record): array
    {
        return [
            'Stock' => number_format($record->stockOnHand(), 0) . ' ' . $record->unit,
            'Categoría' => $record->category ?? '-',
        ];
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Qué es')
                    ->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nombre')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Guantes de nitrilo'),
                        Forms\Components\TextInput::make('sku')
                            ->label('Código / SKU')
                            ->maxLength(255)
                            ->helperText('Opcional. El que uses con tu proveedor.'),
                        Forms\Components\TextInput::make('category')
                            ->label('Categoría')
                            ->maxLength(255)
                            ->datalist([
                                'Anestesia', 'Restaurador', 'Endodoncia', 'Protección',
                                'Desechable', 'Instrumental', 'Ortodoncia', 'Estéril',
                                'Radiología', 'Blanqueamiento', 'Periodoncia',
                            ]),
                        Forms\Components\TextInput::make('preferred_supplier')
                            ->label('Proveedor')
                            ->maxLength(255),
                    ]),

                Forms\Components\Section::make('Cómo se compra y cómo se gasta')
                    ->description('Si compras por caja y gastas por pieza, aquí va el puente. Sin esto el inventario miente desde el primer día.')
                    ->columns(3)
                    ->schema([
                        Forms\Components\TextInput::make('unit')
                            ->label('Unidad en que se gasta')
                            ->required()
                            ->default('pieza')
                            ->maxLength(20)
                            ->datalist(['pieza', 'ml', 'gramo', 'jeringa', 'cartucho', 'rollo', 'par', 'caja']),
                        Forms\Components\TextInput::make('purchase_unit')
                            ->label('Unidad en que se compra')
                            ->maxLength(20)
                            ->placeholder('caja')
                            ->datalist(['caja', 'paquete', 'frasco', 'bote']),
                        Forms\Components\TextInput::make('units_per_purchase')
                            ->label('¿Cuántas trae?')
                            ->numeric()
                            ->default(1)
                            ->minValue(0.001)
                            ->helperText('Una caja de 50 guantes: 50.'),
                    ]),

                Forms\Components\Section::make('Punto de reorden y costo')
                    ->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make('min_stock')
                            ->label('Avisar cuando queden')
                            ->numeric()
                            ->default(0)
                            ->helperText('En unidad de gasto. En cero no avisa — un insumo sin punto de reorden no debe llenar la pantalla de alertas.'),
                        Forms\Components\TextInput::make('cost_per_unit')
                            ->label('Costo por unidad')
                            ->numeric()
                            ->prefix('$')
                            ->default(0)
                            ->helperText('Lo que te cuesta una unidad de gasto.'),
                        Forms\Components\Toggle::make('is_active')
                            ->label('Activo')
                            ->default(true),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Insumo')
                    ->searchable()
                    ->sortable()
                    ->description(fn (Supply $record) => $record->category),
                Tables\Columns\TextColumn::make('stock_on_hand')
                    ->label('Stock')
                    ->badge()
                    ->color(fn (Supply $record) => $record->belowMinimum() ? 'danger' : 'success')
                    ->formatStateUsing(fn ($state, Supply $record) => number_format((float) $state, 0) . ' ' . $record->unit)
                    ->sortable(),
                Tables\Columns\TextColumn::make('min_stock')
                    ->label('Mínimo')
                    ->formatStateUsing(fn ($state, Supply $record) => (float) $state > 0
                        ? number_format((float) $state, 0) . ' ' . $record->unit
                        : '—')
                    ->toggleable()
                    ->sortable(),
                // La caducidad más próxima entre los lotes que todavía tienen
                // existencia. Se calcula por fila: son decenas de insumos por
                // consultorio, no miles.
                Tables\Columns\TextColumn::make('next_expiry')
                    ->label('Caduca')
                    ->state(fn (Supply $record) => $record->nextExpiry()?->format('d/m/Y'))
                    ->badge()
                    ->color(fn (Supply $record) => $record->lotsExpiringSoon(30)->isNotEmpty() ? 'warning' : 'gray')
                    ->placeholder('—')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('cost_per_unit')
                    ->label('Costo')
                    ->money('MXN', locale: 'es_MX')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('purchase_unit')
                    ->label('Se compra')
                    ->formatStateUsing(fn ($state, Supply $record) => $state
                        ? "1 {$state} = " . rtrim(rtrim(number_format((float) $record->units_per_purchase, 3), '0'), '.') . " {$record->unit}"
                        : '—')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('preferred_supplier')
                    ->label('Proveedor')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Activo')
                    ->boolean(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')->label('Activo'),
                // Los que tienen algún lote por caducar en los próximos 30 días.
                Tables\Filters\Filter::make('expiring_soon')
                    ->label('Por caducar (30 días)')
                    ->query(fn (Builder $query) => $query->whereIn(
                        'supplies.id',
                        Supply::where('clinic_id', auth()->user()->clinic_id)
                            ->get()
                            ->filter(fn (Supply $insumo) => $insumo->lotsExpiringSoon(30)->isNotEmpty())
                            ->pluck('id'),
                    )),
                Tables\Filters\SelectFilter::make('category')
                    ->label('Categoría')
                    ->options(fn () => Supply::where('clinic_id', auth()->user()->clinic_id)
                        ->whereNotNull('category')
                        ->distinct()
                        ->pluck('category', 'category')
                        ->all()),
            ])
            ->actions([
                Tables\Actions\Action::make('movement')
                    ->label('Movimiento')
                    ->icon('heroicon-o-arrows-right-left')
                    ->modalHeading(fn (Supply $record) => 'Movimiento de ' . $record->name)
                    ->modalSubmitActionLabel('Registrar')
                    ->form([
                        Forms\Components\Select::make('type')
                            ->label('Tipo')
                            ->options(SupplyMovement::TYPES)
                            ->default('in')
                            ->required()
                            ->live(),
                        Forms\Components\TextInput::make('quantity')
                            ->label('Cantidad')
                            ->numeric()
                            ->required()
                            ->minValue(0.001)
                            ->helperText(fn (Supply $record, callable $get) => (float) $record->units_per_purchase > 1
                                && $record->purchase_unit
                                    ? "En {$record->unit}s. Si compraste por {$record->purchase_unit}, multiplica: 1 {$record->purchase_unit} = "
                                        . rtrim(rtrim(number_format((float) $record->units_per_purchase, 3), '0'), '.') . " {$record->unit}s."
                                    : "En {$record->unit}s."),
                        Forms\Components\TextInput::make('unit_cost')
                            ->label('Costo por unidad')
                            ->numeric()
                            ->prefix('$')
                            ->visible(fn (callable $get) => $get('type') === 'in'),
                        // El lote es opcional: los guantes no caducan y
                        // capturarlos sería trabajo sin provecho.
                        Forms\Components\TextInput::make('lot_number')
                            ->label('Lote')
                            ->maxLength(255)
                            ->visible(fn (callable $get) => $get('type') === 'in'),
                        Forms\Components\DatePicker::make('expires_on')
                            ->label('Caduca el')
                            ->native(false)
                            ->visible(fn (callable $get) => $get('type') === 'in')
                            ->helperText('Opcional. Sin fecha, este insumo no avisa de caducidades.'),
                        // El motivo es una categoría, no una nota: el contador
                        // suma por causa para deducir la merma.
                        Forms\Components\Select::make('waste_reason')
                            ->label('Por qué se perdió')
                            ->options(SupplyMovement::WASTE_REASONS)
                            ->native(false)
                            ->visible(fn (callable $get) => $get('type') === 'waste')
                            ->required(fn (callable $get) => $get('type') === 'waste')
                            ->helperText('Sin motivo no se puede deducir. Se guarda el costo del insumo para el reporte.'),
                        Forms\Components\TextInput::make('reason')
                            ->label('Nota')
                            ->maxLength(255)
                            ->placeholder('Compra a proveedor, uso en consulta, frasco caducado…'),
                    ])
                    ->action(function (Supply $record, array $data) {
                        // Si la entrada trae lote o caducidad, se abre el lote
                        // y el movimiento queda ligado a él. De ahí sale el
                        // reparto FEFO que calcula qué queda en cada uno.
                        $lote = null;

                        if ($data['type'] === 'in'
                            && (filled($data['lot_number'] ?? null) || filled($data['expires_on'] ?? null))) {
                            $lote = $record->lots()->create([
                                'clinic_id' => $record->clinic_id,
                                'lot_number' => $data['lot_number'] ?? null,
                                'expires_on' => $data['expires_on'] ?? null,
                                'quantity' => (float) $data['quantity'],
                                'unit_cost' => $data['unit_cost'] ?? null,
                                'supplier' => $record->preferred_supplier,
                            ]);
                        }

                        $record->register($data['type'], (float) $data['quantity'], [
                            'unit_cost' => $data['unit_cost'] ?? null,
                            'reason' => $data['reason'] ?? null,
                            'waste_reason' => $data['waste_reason'] ?? null,
                            'supply_lot_id' => $lote?->id,
                        ]);

                        Notification::make()
                            ->title('Movimiento registrado')
                            ->body($record->name . ': ' . number_format($record->fresh()->currentStock(), 0) . ' ' . $record->unit . ' en existencia.')
                            ->success()
                            ->send();
                    }),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('name');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSupplies::route('/'),
            'create' => Pages\CreateSupply::route('/create'),
            'edit' => Pages\EditSupply::route('/{record}/edit'),
        ];
    }
}
