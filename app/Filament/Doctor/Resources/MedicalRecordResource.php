<?php

namespace App\Filament\Doctor\Resources;

use App\Filament\Doctor\Resources\MedicalRecordResource\Pages;
use App\Models\Doctor;
use App\Models\MedicalRecord;
use App\Models\Patient;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class MedicalRecordResource extends Resource
{
    protected static ?string $slug = 'expediente-clinico';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('clinic_id', auth()->user()->clinic_id);
    }

    protected static ?string $model = MedicalRecord::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationLabel = 'Expediente Clínico';

    protected static ?string $modelLabel = 'Consulta';

    protected static ?string $pluralModelLabel = 'Expediente Clínico';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Datos de la Consulta')
                    ->columns(2)
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
                        Forms\Components\DatePicker::make('visit_date')
                            ->label('Fecha de consulta')
                            ->default(now())
                            ->required()
                            ->native(false)
                            ->displayFormat('d/m/Y'),
                        Forms\Components\Select::make('appointment_id')
                            ->label('Cita asociada')
                            ->relationship('appointment')
                            ->getOptionLabelFromRecordUsing(fn ($record) => $record->starts_at->format('d/m/Y H:i') . ' - ' . $record->patient->full_name)
                            ->searchable()
                            ->preload(),
                    ]),
                Forms\Components\Section::make('Información Clínica')
                    ->schema([
                        Forms\Components\Textarea::make('chief_complaint')
                            ->label('Motivo de consulta')
                            ->rows(2),
                        Forms\Components\Textarea::make('diagnosis')
                            ->label('Diagnóstico')
                            ->rows(3),
                        Forms\Components\Textarea::make('treatment')
                            ->label('Tratamiento')
                            ->rows(3),
                        Forms\Components\Textarea::make('notes')
                            ->label('Notas adicionales')
                            ->rows(2),
                    ]),
                Forms\Components\Section::make('Signos Vitales')
                    ->columns(4)
                    ->collapsible()
                    ->schema([
                        Forms\Components\TextInput::make('vital_signs.blood_pressure')
                            ->label('Presión arterial')
                            ->placeholder('120/80'),
                        Forms\Components\TextInput::make('vital_signs.heart_rate')
                            ->label('Frec. cardíaca')
                            ->numeric()
                            ->suffix('bpm'),
                        Forms\Components\TextInput::make('vital_signs.temperature')
                            ->label('Temperatura')
                            ->numeric()
                            ->suffix('°C'),
                        Forms\Components\TextInput::make('vital_signs.weight')
                            ->label('Peso')
                            ->numeric()
                            ->suffix('kg'),
                    ]),
                Forms\Components\Section::make('Archivos')
                    ->collapsible()
                    ->schema([
                        Forms\Components\FileUpload::make('attachments')
                            ->label('Radiografías / Fotos')
                            ->multiple()
                            ->image()
                            ->directory('medical-records')
                            ->maxFiles(10)
                            ->maxSize(5120)
                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                            ->reorderable(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('visit_date')
                    ->label('Fecha')
                    ->date('d/m/Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('patient.first_name')
                    ->label('Paciente')
                    ->formatStateUsing(fn ($record) => "{$record->patient->first_name} {$record->patient->last_name}")
                    ->searchable(),
                Tables\Columns\TextColumn::make('doctor.user.name')
                    ->label('Doctor'),
                Tables\Columns\TextColumn::make('chief_complaint')
                    ->label('Motivo')
                    ->limit(40),
                Tables\Columns\TextColumn::make('diagnosis')
                    ->label('Diagnóstico')
                    ->limit(40),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('patient_id')
                    ->label('Paciente')
                    ->relationship('patient', 'first_name')
                    ->searchable()
                    ->preload(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->defaultSort('visit_date', 'desc');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMedicalRecords::route('/'),
            'create' => Pages\CreateMedicalRecord::route('/create'),
            'edit' => Pages\EditMedicalRecord::route('/{record}/edit'),
        ];
    }
}
