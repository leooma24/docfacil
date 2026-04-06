<?php

namespace App\Filament\Doctor\Resources;

use App\Filament\Doctor\Resources\PrescriptionResource\Pages;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Prescription;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class PrescriptionResource extends Resource
{
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('clinic_id', auth()->user()->clinic_id);
    }

    protected static ?string $model = Prescription::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-duplicate';

    protected static ?string $navigationLabel = 'Recetas';

    protected static ?string $modelLabel = 'Receta';

    protected static ?string $pluralModelLabel = 'Recetas';

    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Datos de la Receta')
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
                        Forms\Components\DatePicker::make('prescription_date')
                            ->label('Fecha')
                            ->default(now())
                            ->required()
                            ->native(false)
                            ->displayFormat('d/m/Y'),
                        Forms\Components\Select::make('medical_record_id')
                            ->label('Consulta asociada')
                            ->relationship('medicalRecord')
                            ->getOptionLabelFromRecordUsing(fn ($record) => $record->visit_date->format('d/m/Y') . ' - ' . ($record->diagnosis ?? 'Sin diagnóstico'))
                            ->searchable()
                            ->preload(),
                        Forms\Components\Textarea::make('diagnosis')
                            ->label('Diagnóstico')
                            ->rows(2)
                            ->columnSpanFull(),
                    ]),
                Forms\Components\Section::make('Medicamentos')
                    ->schema([
                        Forms\Components\Repeater::make('items')
                            ->relationship()
                            ->label('')
                            ->schema([
                                Forms\Components\TextInput::make('medication')
                                    ->label('Medicamento')
                                    ->required()
                                    ->columnSpan(2),
                                Forms\Components\TextInput::make('dosage')
                                    ->label('Dosis')
                                    ->placeholder('500mg'),
                                Forms\Components\TextInput::make('frequency')
                                    ->label('Frecuencia')
                                    ->placeholder('Cada 8 horas'),
                                Forms\Components\TextInput::make('duration')
                                    ->label('Duración')
                                    ->placeholder('7 días'),
                                Forms\Components\TextInput::make('instructions')
                                    ->label('Indicaciones')
                                    ->placeholder('Tomar con alimentos'),
                            ])
                            ->columns(6)
                            ->defaultItems(1)
                            ->addActionLabel('Agregar medicamento')
                            ->reorderable()
                            ->collapsible(),
                    ]),
                Forms\Components\Textarea::make('notes')
                    ->label('Notas / Indicaciones generales')
                    ->rows(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('prescription_date')
                    ->label('Fecha')
                    ->date('d/m/Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('patient.first_name')
                    ->label('Paciente')
                    ->formatStateUsing(fn ($record) => "{$record->patient->first_name} {$record->patient->last_name}")
                    ->searchable(),
                Tables\Columns\TextColumn::make('doctor.user.name')
                    ->label('Doctor'),
                Tables\Columns\TextColumn::make('diagnosis')
                    ->label('Diagnóstico')
                    ->limit(40),
                Tables\Columns\TextColumn::make('items_count')
                    ->label('Medicamentos')
                    ->counts('items')
                    ->suffix(' med.'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('download_pdf')
                    ->label('PDF')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('success')
                    ->action(function (Prescription $record) {
                        $record->load(['patient', 'doctor.user', 'doctor.clinic', 'items']);

                        $pdf = Pdf::loadView('pdf.prescription', [
                            'prescription' => $record,
                        ]);

                        return response()->streamDownload(
                            fn () => print($pdf->output()),
                            "receta-{$record->id}-{$record->patient->last_name}.pdf"
                        );
                    }),
            ])
            ->defaultSort('prescription_date', 'desc');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPrescriptions::route('/'),
            'create' => Pages\CreatePrescription::route('/create'),
            'edit' => Pages\EditPrescription::route('/{record}/edit'),
        ];
    }
}
