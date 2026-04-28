<?php

namespace App\Filament\Doctor\Resources\AppointmentResource\Pages;

use App\Filament\Doctor\Concerns\HasFormHero;
use App\Filament\Doctor\Resources\AppointmentResource;
use Filament\Resources\Pages\CreateRecord;

class CreateAppointment extends CreateRecord
{
    use HasFormHero;

    protected static string $resource = AppointmentResource::class;

    protected static string $view = 'filament.doctor.resources.create-with-hero';

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['clinic_id'] = auth()->user()->clinic_id;

        return $data;
    }

    /**
     * Pre-fill form: patient_id viene de ?patient= (cuando se entra desde el
     * profile de un paciente con click en "Agendar"), doctor_id default al
     * doctor logueado para no tener que seleccionar uno mismo cada vez.
     */
    protected function fillForm(): void
    {
        $defaults = [];

        if ($patientId = request()->query('patient')) {
            $defaults['patient_id'] = (int) $patientId;
        }

        if ($doctorId = auth()->user()?->doctor?->id) {
            $defaults['doctor_id'] = $doctorId;
        }

        $this->form->fill($defaults);
    }

    protected function getFormHeroConfig(): array
    {
        return [
            'title'    => 'Nueva cita',
            'icon'     => '📅',
            'kicker'   => '➕ Agendar cita',
            'subtitle' => 'Agenda una nueva cita. El paciente recibirá recordatorio WhatsApp 24h y 2h antes.',
            'gradient' => '#3b82f6 0%, #0891b2 40%, #0ea5e9 100%',
            'accent'   => '#3b82f6',
        ];
    }
}
