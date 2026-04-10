<?php

namespace App\Filament\Doctor\Resources;

use App\Filament\Doctor\Resources\OdontogramResource\Pages;
use App\Models\Doctor;
use App\Models\Odontogram;
use App\Models\Patient;
use App\Services\SpecialtyService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class OdontogramResource extends Resource
{
    protected static ?string $slug = 'odontogramas';

    public static function shouldRegisterNavigation(): bool
    {
        return SpecialtyService::currentDoctorCanSee('odontogram');
    }

    public static function canAccess(): bool
    {
        return SpecialtyService::currentDoctorCanSee('odontogram');
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('clinic_id', auth()->user()->clinic_id);
    }

    protected static ?string $model = Odontogram::class;

    protected static ?string $navigationIcon = 'heroicon-o-cursor-arrow-ripple';

    protected static ?string $navigationLabel = 'Odontograma';

    protected static ?string $modelLabel = 'Odontograma';

    protected static ?string $pluralModelLabel = 'Odontogramas';

    protected static ?int $navigationSort = 6;

    protected static ?string $navigationGroup = 'Dental';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Datos del Odontograma')
                    ->columns(3)
                    ->schema([
                        Forms\Components\Select::make('patient_id')
                            ->label('Paciente')
                            ->relationship('patient')
                            ->getOptionLabelFromRecordUsing(fn (Patient $record) => "{$record->first_name} {$record->last_name}")
                            ->searchable(['first_name', 'last_name'])
                            ->preload()
                            ->required(),
                        Forms\Components\Select::make('doctor_id')
                            ->label('Doctor')
                            ->relationship('doctor')
                            ->getOptionLabelFromRecordUsing(fn (Doctor $record) => $record->user?->name ?? '')
                            ->preload()
                            ->required(),
                        Forms\Components\DatePicker::make('evaluation_date')
                            ->label('Fecha evaluación')
                            ->default(now())
                            ->required()
                            ->native(false)
                            ->displayFormat('d/m/Y'),
                    ]),
                Forms\Components\Textarea::make('notes')
                    ->label('Observaciones generales')
                    ->rows(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('evaluation_date')
                    ->label('Fecha')
                    ->date('d/m/Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('patient.first_name')
                    ->label('Paciente')
                    ->formatStateUsing(fn ($record) => "{$record->patient->first_name} {$record->patient->last_name}")
                    ->searchable(),
                Tables\Columns\TextColumn::make('doctor.user.name')
                    ->label('Doctor'),
                Tables\Columns\TextColumn::make('teeth_count')
                    ->label('Dientes registrados')
                    ->counts('teeth'),
                Tables\Columns\TextColumn::make('notes')
                    ->label('Observaciones')
                    ->limit(40),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->defaultSort('evaluation_date', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOdontograms::route('/'),
            'create' => Pages\CreateOdontogram::route('/create'),
            'edit' => Pages\EditOdontogram::route('/{record}/edit'),
        ];
    }
}
