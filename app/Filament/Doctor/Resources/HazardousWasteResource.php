<?php

namespace App\Filament\Doctor\Resources;

use App\Filament\Doctor\Resources\HazardousWasteResource\Pages;
use App\Models\HazardousWaste;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

/**
 * El registro de residuos que la norma obliga a manejar aparte.
 *
 * Del lado del inventario esto no existe: no genera ningún movimiento ni baja
 * ningún stock, porque el material ya salió cuando se mezcló. Lo que deja es la
 * trazabilidad —qué residuo, de qué consulta, en qué contenedor y con qué
 * número salió—, que es lo que se enseña en una revisión.
 */
class HazardousWasteResource extends Resource
{
    protected static ?string $model = HazardousWaste::class;

    protected static ?string $slug = 'residuos';

    protected static ?string $navigationIcon = 'heroicon-o-exclamation-triangle';

    protected static ?string $navigationLabel = 'Residuos';

    protected static ?string $modelLabel = 'Residuo';

    protected static ?string $pluralModelLabel = 'Residuos';

    protected static ?int $navigationSort = 10;

    /**
     * El inventario va en Pro. Se cierran las dos puertas: la navegacion y el
     * acceso por URL, porque esconder el menu no cierra nada.
     */
    public static function shouldRegisterNavigation(): bool
    {
        return (bool) auth()->user()?->clinic?->hasFeature('inventory');
    }

    public static function canAccess(): bool
    {
        return (bool) auth()->user()?->clinic?->hasFeature('inventory');
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('clinic_id', auth()->user()->clinic_id);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Qué residuo')
                    ->description('Este registro NO descuenta inventario: ese material ya salió cuando se mezcló. Es para poder demostrar qué se hizo con él.')
                    ->columns(2)
                    ->schema([
                        Forms\Components\Select::make('material')
                            ->label('Material')
                            ->options(HazardousWaste::MATERIALS)
                            ->required()
                            ->native(false),
                        Forms\Components\DatePicker::make('disposed_on')
                            ->label('Fecha')
                            ->required()
                            ->default(now())
                            ->native(false),
                        Forms\Components\TextInput::make('quantity')
                            ->label('Cantidad')
                            ->numeric()
                            ->required()
                            ->minValue(0.001),
                        Forms\Components\TextInput::make('unit')
                            ->label('Unidad')
                            ->required()
                            ->default('gramo')
                            ->datalist(['gramo', 'mililitro', 'pieza', 'cápsula']),
                    ]),

                Forms\Components\Section::make('Trazabilidad')
                    ->description('Lo que se enseña en una revisión: dónde se guardó y con qué registro salió.')
                    ->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make('container')
                            ->label('Contenedor')
                            ->maxLength(255)
                            ->placeholder('Frasco de amalgama, contenedor RPBI…'),
                        Forms\Components\TextInput::make('manifest_number')
                            ->label('Número de registro o manifiesto')
                            ->maxLength(255)
                            ->helperText('El que trae el recolector autorizado. Se puede anotar después.'),
                        Forms\Components\Textarea::make('notes')
                            ->label('Notas')
                            ->rows(2)
                            ->columnSpanFull(),
                    ]),

                Forms\Components\Section::make('De dónde salió')
                    ->columns(2)
                    ->collapsed()
                    ->schema([
                        Forms\Components\Select::make('appointment_id')
                            ->label('Consulta')
                            ->options(fn () => \App\Models\Appointment::where('clinic_id', auth()->user()->clinic_id)
                                ->with('patient')
                                ->latest('starts_at')
                                ->limit(50)
                                ->get()
                                ->mapWithKeys(fn ($cita) => [
                                    $cita->id => ($cita->starts_at?->format('d/m/Y') ?? '—')
                                        . ' · ' . ($cita->patient?->first_name ?? '') . ' ' . ($cita->patient?->last_name ?? ''),
                                ])
                                ->all())
                            ->searchable(),
                        Forms\Components\Select::make('supply_id')
                            ->label('Insumo')
                            ->options(fn () => \App\Models\Supply::where('clinic_id', auth()->user()->clinic_id)
                                ->orderBy('name')
                                ->pluck('name', 'id')
                                ->all())
                            ->searchable(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('disposed_on')
                    ->label('Fecha')
                    ->date('d/m/Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('material')
                    ->label('Material')
                    ->badge()
                    ->formatStateUsing(fn (HazardousWaste $record) => $record->materialLabel())
                    ->sortable(),
                Tables\Columns\TextColumn::make('quantity')
                    ->label('Cantidad')
                    ->formatStateUsing(fn (HazardousWaste $record) => rtrim(rtrim(number_format((float) $record->quantity, 3), '0'), '.') . ' ' . $record->unit)
                    ->sortable(),
                Tables\Columns\TextColumn::make('container')
                    ->label('Contenedor')
                    ->placeholder('—')
                    ->toggleable(),
                // Lo que falta para que el registro sirva en una revisión.
                Tables\Columns\TextColumn::make('manifest_number')
                    ->label('Registro')
                    ->badge()
                    ->formatStateUsing(fn (HazardousWaste $record) => $record->isMissingManifest() ? '⚠ Falta' : $record->manifest_number)
                    ->color(fn (HazardousWaste $record) => $record->isMissingManifest() ? 'warning' : 'success')
                    ->tooltip(fn (HazardousWaste $record) => $record->isMissingManifest()
                        ? 'Anota el número del manifiesto del recolector autorizado. Sin él, el registro no demuestra nada.'
                        : null),
                Tables\Columns\TextColumn::make('appointment.patient.first_name')
                    ->label('Paciente')
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('material')
                    ->label('Material')
                    ->options(HazardousWaste::MATERIALS),
                Tables\Filters\Filter::make('missing_manifest')
                    ->label('Sin número de registro')
                    ->query(fn (Builder $query) => $query->where(fn (Builder $q) => $q->whereNull('manifest_number')->orWhere('manifest_number', ''))),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->defaultSort('disposed_on', 'desc');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListHazardousWastes::route('/'),
            'create' => Pages\CreateHazardousWaste::route('/create'),
            'edit' => Pages\EditHazardousWaste::route('/{record}/edit'),
        ];
    }
}
