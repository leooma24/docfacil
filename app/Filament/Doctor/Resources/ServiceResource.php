<?php

namespace App\Filament\Doctor\Resources;

use App\Filament\Doctor\Resources\ServiceResource\Pages;
use App\Models\Service;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ServiceResource extends Resource
{
    protected static ?string $slug = 'servicios';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('clinic_id', auth()->user()->clinic_id);
    }

    protected static ?string $model = Service::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?string $navigationLabel = 'Servicios';

    protected static ?string $modelLabel = 'Servicio';

    protected static ?string $pluralModelLabel = 'Servicios';

    protected static ?string $recordTitleAttribute = 'name';

    public static function getGloballySearchableAttributes(): array
    {
        return ['name', 'category'];
    }

    public static function getGlobalSearchResultDetails(\Illuminate\Database\Eloquent\Model $record): array
    {
        return [
            'Precio' => '$' . number_format($record->price, 0),
            'Categoría' => $record->category ?? '-',
        ];
    }

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Información del Servicio')
                    ->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nombre')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('category')
                            ->label('Categoría')
                            ->maxLength(255)
                            ->datalist([
                                'General', 'Preventivo', 'Restauración',
                                'Endodoncia', 'Cirugía', 'Prótesis',
                                'Estética', 'Diagnóstico', 'Ortodoncia',
                            ]),
                        Forms\Components\TextInput::make('price')
                            ->label('Precio')
                            ->numeric()
                            ->prefix('$')
                            ->required(),
                        // Sin esto, "Curetaje (por cuadrante)" a $800 se cobraba
                        // una vez aunque fueran dos cuadrantes: el precio no
                        // decía de qué era.
                        Forms\Components\Select::make('unit')
                            ->label('El precio es')
                            ->options(\App\Support\WorkUnit::billingLabels())
                            ->default(\App\Support\WorkUnit::VISIT)
                            ->required()
                            ->native(false)
                            ->live()
                            ->helperText(fn (callable $get) => match ($get('unit')) {
                                \App\Support\WorkUnit::QUADRANT => 'Por cuadrante: un curetaje de 2 cuadrantes se cobra dos veces.',
                                \App\Support\WorkUnit::TOOTH => 'Por diente: tres resinas se cobran tres veces.',
                                default => 'Por visita, sin importar cuántos dientes se trabajen.',
                            }),
                        Forms\Components\TextInput::make('duration_minutes')
                            ->label('Duración (minutos)')
                            ->numeric()
                            ->suffix('min')
                            ->required()
                            ->default(30),
                        Forms\Components\Select::make('recall_months')
                            ->label('Recall / seguimiento')
                            ->placeholder('Sin recall')
                            ->helperText('Los pacientes que reciben este servicio serán recordados para regresar. Típico: 6 meses para limpieza, 12 para revisión.')
                            ->options([
                                3 => 'Cada 3 meses (ortodoncia, whitening)',
                                6 => 'Cada 6 meses (limpieza, revisión)',
                                12 => 'Cada 12 meses (revisión anual)',
                                24 => 'Cada 24 meses',
                            ]),
                        Forms\Components\Textarea::make('description')
                            ->label('Descripción')
                            ->columnSpanFull()
                            ->rows(2),
                        Forms\Components\Toggle::make('is_active')
                            ->label('Activo')
                            ->default(true),
                    ]),

                // La receta. Sin ella no hay nada que descontar al cerrar la
                // consulta; con ella, el doctor confirma y el kardex se mueve.
                Forms\Components\Section::make('Receta de insumos')
                    ->description('Qué gasta este servicio. Es de donde sale el descuento de inventario. Se puede dejar vacío y llenarlo después: la primera vez que uses el servicio en una consulta, te lo vamos a preguntar.')
                    ->schema([
                        Forms\Components\Repeater::make('recipe')
                            ->relationship('recipe')
                            ->hiddenLabel()
                            ->addActionLabel('Agregar insumo')
                            ->defaultItems(0)
                            ->columns(2)
                            ->schema([
                                Forms\Components\Select::make('supply_id')
                                    ->label('Insumo')
                                    ->options(fn () => \App\Models\Supply::where('clinic_id', auth()->user()->clinic_id)
                                        ->active()
                                        ->orderBy('name')
                                        ->pluck('name', 'id')
                                        ->all())
                                    ->searchable()
                                    ->required()
                                    ->columnSpanFull(),
                                Forms\Components\TextInput::make('quantity')
                                    ->label('Cantidad por unidad')
                                    ->numeric()
                                    ->default(1)
                                    ->minValue(0.001)
                                    ->required(),
                                Forms\Components\Select::make('scope')
                                    ->label('Se gasta')
                                    ->options(\App\Support\WorkUnit::LABELS)
                                    ->placeholder('Como el servicio')
                                    ->native(false)
                                    ->helperText(function (callable $get) {
                                        $base = 'Vacío = como se cobre el servicio.';
                                        $insumo = \App\Models\Supply::find($get('supply_id'));
                                        $sugerido = \App\Support\WorkUnit::scopeSuggestedByCategory($insumo?->category);

                                        return $sugerido
                                            ? $base . ' Por su categoría (' . $insumo->category . ') suele ir ' . strtolower(\App\Support\WorkUnit::label($sugerido)) . '.'
                                            : $base;
                                    }),
                                Forms\Components\TextInput::make('waste_factor')
                                    ->label('Merma')
                                    ->numeric()
                                    ->default(1)
                                    ->helperText('1.15 = se pierde 15%. Sin esto el inventario queda corto.'),
                                Forms\Components\Toggle::make('is_optional')
                                    ->label('No siempre se usa')
                                    ->helperText('Aparecerá sin palomear en la propuesta.'),
                            ])
                            // El índice único de la tabla lo impide igual, pero
                            // aquí el doctor recibe un mensaje en vez de un error.
                            ->rule(function () {
                                return function (string $attribute, $value, $fail) {
                                    $insumos = collect($value ?? [])->pluck('supply_id')->filter();

                                    if ($insumos->count() !== $insumos->unique()->count()) {
                                        $fail('Hay un insumo repetido. Súbele la cantidad al que ya está, en vez de agregarlo dos veces.');
                                    }
                                };
                            }),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Servicio')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('category')
                    ->label('Categoría')
                    ->badge()
                    ->searchable(),
                Tables\Columns\TextColumn::make('price')
                    ->label('Precio')
                    ->money('MXN', locale: 'es_MX')
                    ->description(fn (Service $record) => $record->isBilledPerUnit()
                        ? 'por ' . str_replace('Por ', '', strtolower($record->unitLabel()))
                        : null)
                    ->sortable(),
                // El servicio que ya existía quedó como "por visita" porque el
                // sistema no sabía de unidades. Esta columna señala los que su
                // propio nombre delata, para que el doctor los confirme.
                Tables\Columns\TextColumn::make('unit')
                    ->label('Se cobra')
                    ->badge()
                    ->formatStateUsing(fn (string $state, Service $record) => $record->needsUnitReview()
                        ? '⚠ Revisar'
                        : str_replace('Por ', '', $record->unitLabel()))
                    ->color(fn (Service $record) => $record->needsUnitReview() ? 'warning' : 'gray')
                    ->tooltip(fn (Service $record) => $record->needsUnitReview()
                        ? 'El nombre dice "por '
                            . ($record->suggestedUnit() === \App\Support\WorkUnit::QUADRANT ? 'cuadrante' : 'diente')
                            . '" pero el precio está como por visita. Un curetaje de dos cuadrantes se está cobrando una vez.'
                        : null),
                Tables\Columns\TextColumn::make('duration_minutes')
                    ->label('Duración')
                    ->suffix(' min')
                    ->sortable(),
                Tables\Columns\TextColumn::make('recipe_count')
                    ->label('Receta')
                    ->counts('recipe')
                    ->badge()
                    ->formatStateUsing(function ($state, Service $record) {
                        $porRevisar = $record->recipeLinesNeedingReview();

                        if ($state === 0) {
                            return 'Sin receta';
                        }

                        return $porRevisar > 0
                            ? "⚠ {$state} · {$porRevisar} por revisar"
                            : (string) $state;
                    })
                    ->color(fn ($state, Service $record) => match (true) {
                        $state === 0 => 'gray',
                        $record->recipeLinesNeedingReview() > 0 => 'warning',
                        default => 'success',
                    })
                    ->tooltip(fn (Service $record) => $record->recipeLinesNeedingReview() > 0
                        ? 'Algún insumo se está gastando con el alcance del servicio, pero su categoría pide otro. La anestesia de un curetaje va por zona contigua, no por cuadrante.'
                        : null),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Activo')
                    ->boolean(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Activo'),
                // Los que su nombre delata pero siguen cobrándose por visita.
                Tables\Filters\Filter::make('unit_needs_review')
                    ->label('Unidad por revisar')
                    ->query(function (Builder $query) {
                        // Las mismas pistas que usa la sugerencia en PHP: si
                        // fueran dos listas, este filtro enseñaría servicios
                        // que la sugerencia no reconoce.
                        $pistas = collect(\App\Support\WorkUnit::NAME_HINTS)->flatten()->all();

                        return $query
                            ->where('unit', \App\Support\WorkUnit::VISIT)
                            ->where(function (Builder $grupo) use ($pistas) {
                                foreach ($pistas as $pista) {
                                    $grupo->orWhere('name', 'like', "%{$pista}%");
                                }
                            });
                    }),
                // Las recetas que gastarían un insumo con el alcance
                // equivocado. Es el caso de la anestesia dentro del curetaje.
                //
                // La cuenta se hace en PHP y no en SQL a propósito: la regla ya
                // vive en ServiceSupply::needsScopeReview(), y repetirla
                // en SQL sería la segunda fuente de verdad que este proyecto
                // trata de evitar. Son decenas de servicios por consultorio.
                Tables\Filters\Filter::make('recipe_needs_review')
                    ->label('Receta por revisar')
                    ->query(fn (Builder $query) => $query->whereIn(
                        'services.id',
                        \App\Models\Service::where('clinic_id', auth()->user()->clinic_id)
                            ->with(['recipe.supply', 'recipe.service'])
                            ->get()
                            ->filter(fn (\App\Models\Service $servicio) => $servicio->recipeLinesNeedingReview() > 0)
                            ->pluck('id'),
                    )),
            ])
            ->actions([
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
            'index' => Pages\ListServices::route('/'),
            'create' => Pages\CreateService::route('/create'),
            'edit' => Pages\EditService::route('/{record}/edit'),
        ];
    }
}
