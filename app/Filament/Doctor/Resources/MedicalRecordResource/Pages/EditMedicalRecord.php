<?php

namespace App\Filament\Doctor\Resources\MedicalRecordResource\Pages;

use App\Filament\Doctor\Concerns\HasFormHero;
use App\Filament\Doctor\Resources\MedicalRecordResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMedicalRecord extends EditRecord
{
    use HasFormHero;

    protected static string $resource = MedicalRecordResource::class;

    protected static string $view = 'filament.doctor.resources.edit-with-hero';

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function getFormHeroConfig(): array
    {
        $patient = $this->record->patient ?? null;
        $name = $patient ? trim($patient->first_name . ' ' . $patient->last_name) : 'Consulta';
        $date = $this->record->visit_date?->format('d/m/Y') ?? '';

        return [
            'title'    => 'Editar consulta',
            'icon'     => '📋',
            'kicker'   => '✏️ ' . $name . ($date ? ' · ' . $date : ''),
            'subtitle' => 'Actualiza diagnóstico, tratamiento y notas clínicas.',
            'gradient' => '#ef4444 0%, #f97316 40%, #f59e0b 100%',
            'accent'   => '#ef4444',
        ];
    }
}
