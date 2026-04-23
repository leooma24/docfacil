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
                    ->money('MXN')
                    ->sortable(),
                Tables\Columns\TextColumn::make('duration_minutes')
                    ->label('Duración')
                    ->suffix(' min')
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Activo')
                    ->boolean(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Activo'),
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
