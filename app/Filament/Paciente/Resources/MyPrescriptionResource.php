<?php

namespace App\Filament\Paciente\Resources;

use App\Filament\Paciente\Resources\MyPrescriptionResource\Pages;
use App\Models\Prescription;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class MyPrescriptionResource extends Resource
{
    protected static ?string $model = Prescription::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-duplicate';

    protected static ?string $navigationLabel = 'Mis Recetas';

    protected static ?string $modelLabel = 'Receta';

    protected static ?string $pluralModelLabel = 'Mis Recetas';

    protected static ?string $slug = 'mis-recetas';

    public static function getEloquentQuery(): Builder
    {
        $patient = auth()->user()->patient;

        return parent::getEloquentQuery()
            ->where('patient_id', $patient?->id);
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('prescription_date')
                    ->label('Fecha')
                    ->date('d/m/Y')
                    ->sortable(),
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
                Tables\Actions\Action::make('download_pdf')
                    ->label('Descargar PDF')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('success')
                    ->action(function (Prescription $record) {
                        $record->load(['patient', 'doctor.user', 'doctor.clinic', 'items']);

                        $pdf = Pdf::loadView('pdf.prescription', [
                            'prescription' => $record,
                        ]);

                        return response()->streamDownload(
                            fn () => print($pdf->output()),
                            "receta-{$record->id}.pdf"
                        );
                    }),
            ])
            ->defaultSort('prescription_date', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMyPrescriptions::route('/'),
        ];
    }
}
