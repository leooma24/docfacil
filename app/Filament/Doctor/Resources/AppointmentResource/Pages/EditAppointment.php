<?php

namespace App\Filament\Doctor\Resources\AppointmentResource\Pages;

use App\Filament\Doctor\Concerns\HasFormHero;
use App\Filament\Doctor\Resources\AppointmentResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAppointment extends EditRecord
{
    use HasFormHero;

    protected static string $resource = AppointmentResource::class;

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
        $name = $patient ? trim($patient->first_name . ' ' . $patient->last_name) : 'Cita';
        $when = $this->record->starts_at?->format('d/m/Y H:i') ?? '';

        return [
            'title'    => 'Editar cita',
            'icon'     => '📅',
            'kicker'   => '✏️ ' . $name,
            'subtitle' => $when ? "Reagenda, cambia el servicio o actualiza notas. Cita programada para {$when}." : 'Reagenda, cambia el servicio o actualiza notas de la cita.',
            'gradient' => '#3b82f6 0%, #0891b2 40%, #0ea5e9 100%',
            'accent'   => '#3b82f6',
        ];
    }
}
