<?php

namespace App\Filament\Doctor\Resources;

use App\Filament\Doctor\Resources\ExpenseResource\Pages;
use App\Models\Expense;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

/**
 * Los gastos del consultorio.
 *
 * DocFácil solo sabía lo que entra. El doctor que quería saber cómo le fue
 * de verdad sacaba sus cobros de aquí y les restaba a mano lo que llevaba en
 * otra hoja — y por eso la hoja nunca se muere.
 */
class ExpenseResource extends Resource
{
    protected static ?string $model = Expense::class;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';

    protected static ?string $navigationLabel = 'Gastos';

    protected static ?string $modelLabel = 'Gasto';

    protected static ?string $pluralModelLabel = 'Gastos';

    protected static ?string $slug = 'gastos';

    protected static ?string $navigationGroup = 'Consultorio';

    protected static ?int $navigationSort = 45;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('clinic_id', auth()->user()->clinic_id);
    }

    public static function shouldRegisterNavigation(): bool
    {
        return (bool) auth()->user()?->clinic?->hasFeature('expenses');
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('El gasto')
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('concept')
                        ->label('¿En qué fue?')
                        ->placeholder('Resinas y anestesia')
                        ->required()
                        ->maxLength(255)
                        ->columnSpanFull(),

                    Forms\Components\Select::make('category')
                        ->label('Categoría')
                        ->options(Expense::CATEGORIAS)
                        ->required()
                        ->native(false)
                        ->searchable()
                        ->default('materiales'),

                    Forms\Components\TextInput::make('amount')
                        ->label('Monto')
                        ->numeric()
                        ->prefix('$')
                        ->suffix('MXN')
                        ->required()
                        ->minValue(0.01)
                        ->step(0.01),

                    Forms\Components\DatePicker::make('expense_date')
                        ->label('Fecha')
                        ->required()
                        ->native(false)
                        ->displayFormat('d/m/Y')
                        ->maxDate(now())
                        ->default(now()),

                    Forms\Components\Select::make('payment_method')
                        ->label('¿Cómo lo pagaste?')
                        ->options(Expense::FORMAS_DE_PAGO)
                        ->native(false)
                        ->placeholder('Sin especificar'),
                ]),

            Forms\Components\Section::make('Lo demás (opcional)')
                ->description('Nada de esto es obligatorio. Llénalo solo si te sirve después.')
                ->collapsed()
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('supplier')
                        ->label('Proveedor')
                        ->placeholder('Depósito Dental del Norte')
                        ->maxLength(255),

                    Forms\Components\Toggle::make('is_recurring')
                        ->label('Se repite cada mes')
                        ->helperText('La renta, la nómina, el internet. DocFácil lo vuelve a capturar solo el mismo día del próximo mes.')
                        ->inline(false),

                    Forms\Components\FileUpload::make('receipt_path')
                        ->label('Comprobante')
                        ->helperText('Foto del ticket o la factura en PDF. Se guarda privado, nadie más lo ve.')
                        // Disco privado: son datos del negocio, no van al
                        // directorio publico.
                        ->disk('local')
                        ->directory('comprobantes-gastos')
                        ->visibility('private')
                        ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp', 'application/pdf'])
                        ->maxSize(5120)
                        ->columnSpanFull(),

                    Forms\Components\Textarea::make('notes')
                        ->label('Nota')
                        ->rows(2)
                        ->maxLength(1000)
                        ->columnSpanFull(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('expense_date', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('expense_date')
                    ->label('Fecha')
                    ->date('d/m/Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('concept')
                    ->label('Concepto')
                    ->searchable()
                    ->wrap()
                    ->description(fn (Expense $record) => $record->supplier),

                Tables\Columns\TextColumn::make('category')
                    ->label('Categoría')
                    ->badge()
                    ->color('gray')
                    ->formatStateUsing(fn (string $state) => Expense::CATEGORIAS[$state] ?? $state)
                    ->sortable(),

                Tables\Columns\TextColumn::make('amount')
                    ->label('Monto')
                    ->money('MXN', locale: 'es_MX')
                    ->alignEnd()
                    ->sortable()
                    ->summarize(Tables\Columns\Summarizers\Sum::make()->money('MXN', locale: 'es_MX')->label('Total')),

                Tables\Columns\IconColumn::make('is_recurring')
                    ->label('Cada mes')
                    ->boolean()
                    ->trueIcon('heroicon-o-arrow-path')
                    ->falseIcon('')
                    ->trueColor('info'),

                Tables\Columns\IconColumn::make('receipt_path')
                    ->label('Comprobante')
                    ->boolean()
                    ->trueIcon('heroicon-o-paper-clip')
                    ->falseIcon('')
                    ->trueColor('gray'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('category')
                    ->label('Categoría')
                    ->options(Expense::CATEGORIAS)
                    ->multiple(),

                Tables\Filters\Filter::make('periodo')
                    ->form([
                        Forms\Components\DatePicker::make('desde')->label('Desde')->native(false)->displayFormat('d/m/Y'),
                        Forms\Components\DatePicker::make('hasta')->label('Hasta')->native(false)->displayFormat('d/m/Y'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['desde'] ?? null, fn ($q, $fecha) => $q->whereDate('expense_date', '>=', $fecha))
                            ->when($data['hasta'] ?? null, fn ($q, $fecha) => $q->whereDate('expense_date', '<=', $fecha));
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicadores = [];

                        if ($data['desde'] ?? null) {
                            $indicadores[] = 'Desde ' . \Carbon\Carbon::parse($data['desde'])->format('d/m/Y');
                        }
                        if ($data['hasta'] ?? null) {
                            $indicadores[] = 'Hasta ' . \Carbon\Carbon::parse($data['hasta'])->format('d/m/Y');
                        }

                        return $indicadores;
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                // La renta de este mes es igual a la del pasado. Copiarla es
                // mas rapido que volver a capturarla.
                Tables\Actions\Action::make('repetir')
                    ->label('Repetir')
                    ->icon('heroicon-o-document-duplicate')
                    ->color('gray')
                    ->tooltip('Crea otro gasto igual con la fecha de hoy')
                    ->action(function (Expense $record) {
                        $copia = $record->replicate(['receipt_path', 'last_generated_on']);
                        $copia->expense_date = now();
                        $copia->is_recurring = false;
                        $copia->created_by = auth()->id();
                        $copia->save();

                        \Filament\Notifications\Notification::make()
                            ->title('Gasto copiado')
                            ->body('Se creó otro igual con la fecha de hoy. Ajústalo si el monto cambió.')
                            ->success()
                            ->send();
                    }),
                // Un gasto con proveedor casi siempre es una compra de
                // insumos. Volver a capturarla en el catálogo es trabajo
                // duplicado — y el kardex guarda la referencia, así que el
                // mismo gasto no puede entrar dos veces al inventario.
                Tables\Actions\Action::make('supply_entry')
                    ->label('Entrada de insumos')
                    ->icon('heroicon-o-cube')
                    ->color('gray')
                    ->tooltip('Suma al inventario lo que compraste en este gasto')
                    ->modalHeading(fn (Expense $record) => 'Entrada de insumos — ' . $record->concept)
                    ->modalDescription('Solo se puede registrar una vez por insumo desde el mismo gasto.')
                    ->modalSubmitActionLabel('Registrar entrada')
                    ->visible(fn () => \App\Models\Supply::where('clinic_id', auth()->user()->clinic_id)->active()->exists())
                    ->form(fn (Expense $record) => [
                        Forms\Components\Select::make('supply_id')
                            ->label('Insumo')
                            ->options(fn () => \App\Models\Supply::where('clinic_id', auth()->user()->clinic_id)
                                ->active()
                                ->orderBy('name')
                                ->pluck('name', 'id')
                                ->all())
                            ->searchable()
                            ->required()
                            ->live(),
                        Forms\Components\TextInput::make('quantity')
                            ->label('Cantidad')
                            ->numeric()
                            ->required()
                            ->minValue(0.001)
                            ->live(onBlur: true)
                            ->helperText(function (callable $get) {
                                $insumo = \App\Models\Supply::find($get('supply_id'));

                                if (! $insumo) {
                                    return 'Elige primero el insumo.';
                                }

                                $factor = (float) $insumo->units_per_purchase;

                                return $factor > 1 && $insumo->purchase_unit
                                    ? "En {$insumo->unit}s. 1 {$insumo->purchase_unit} = "
                                        . rtrim(rtrim(number_format($factor, 3), '0'), '.') . " {$insumo->unit}s."
                                    : "En {$insumo->unit}s.";
                            }),
                        Forms\Components\Placeholder::make('unit_cost')
                            ->label('Costo por unidad')
                            ->content(function (callable $get) use ($record) {
                                $insumo = \App\Models\Supply::find($get('supply_id'));
                                $cantidad = (float) ($get('quantity') ?? 0);

                                if (! $insumo || $cantidad <= 0) {
                                    return '—';
                                }

                                return '$' . number_format($insumo->entryCost((float) $record->amount, $cantidad), 2)
                                    . " por {$insumo->unit}, del total del gasto.";
                            }),
                    ])
                    ->action(function (Expense $record, array $data) {
                        $insumo = \App\Models\Supply::where('clinic_id', auth()->user()->clinic_id)
                            ->findOrFail($data['supply_id']);

                        $cantidad = (float) $data['quantity'];

                        $yaEntro = \App\Models\SupplyMovement::where('clinic_id', auth()->user()->clinic_id)
                            ->where('reference_type', Expense::class)
                            ->where('reference_id', $record->id)
                            ->where('supply_id', $insumo->id)
                            ->where('type', 'in')
                            ->exists();

                        if ($yaEntro) {
                            \Filament\Notifications\Notification::make()
                                ->title('Ya estaba registrado')
                                ->body("{$insumo->name} ya entró al inventario desde este gasto.")
                                ->warning()
                                ->send();

                            return;
                        }

                        $insumo->register('in', $cantidad, [
                            'unit_cost' => $insumo->entryCost((float) $record->amount, $cantidad),
                            'reason' => 'Compra: ' . $record->concept . ($record->supplier ? " ({$record->supplier})" : ''),
                            'reference_type' => Expense::class,
                            'reference_id' => $record->id,
                        ]);

                        \Filament\Notifications\Notification::make()
                            ->title('Entrada registrada')
                            ->body($insumo->name . ': ' . number_format($insumo->fresh()->currentStock(), 0) . " {$insumo->unit} en existencia.")
                            ->success()
                            ->send();
                    }),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading('Todavía no has capturado gastos')
            ->emptyStateDescription('Anota lo que sale — renta, materiales, laboratorio, sueldos. Con eso el corte del mes te dice cuánto te quedó de verdad.')
            ->emptyStateIcon('heroicon-o-banknotes');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListExpenses::route('/'),
            'create' => Pages\CreateExpense::route('/create'),
            'edit' => Pages\EditExpense::route('/{record}/edit'),
        ];
    }
}
