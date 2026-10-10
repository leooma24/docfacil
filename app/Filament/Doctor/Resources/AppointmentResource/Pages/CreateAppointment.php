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

    /** Al tope de citas del mes se le dice con palabras, no con una pantalla de error. */
    protected function beforeCreate(): void
    {
        $clinica = auth()->user()->clinic;
        $inicio = $this->data['starts_at'] ?? null;

        if ($clinica && ! $clinica->puedeAgendar($inicio ? \Illuminate\Support\Carbon::parse($inicio) : null)) {
            $clinica->avisarTopeDeCitas();
            $this->halt();
        }
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['clinic_id'] = auth()->user()->clinic_id;

        return $data;
    }

    // Defaults de patient_id y doctor_id viven en los fields del form
    // (AppointmentResource::form() con ->default() closures). NO usar
    // mount() porque sobreescribir $this->data despues del form->fill
    // causa que dropdowns reverten a default cuando el usuario cambia
    // valores y otro field reactivo dispara re-render.

    protected function getFormHeroConfig(): array
    {
        return [
            'title'    => 'Nueva cita',
            'subtitle' => 'Agende la cita. Un día antes le sale en Recordatorios de mañana para mandarle el recordatorio por WhatsApp a 1 clic.',
            'gradient' => '#3b82f6 0%, #0891b2 40%, #0ea5e9 100%',
            'accent'   => '#3b82f6',
        ];
    }

    protected function afterCreate(): void
    {
        $this->avisarSiQuedoPegada();
    }

    /**
     * Si la cita quedó pegada a la de junto, decirlo — pero sin estorbar.
     *
     * No se bloquea a propósito: el doctor está viendo su propia agenda y a
     * veces tiene que meter la urgencia de las 3 de la tarde. Nada más que
     * sepa que ese día va a arrancar corriendo.
     */
    protected function avisarSiQuedoPegada(): void
    {
        $cita = $this->record;

        $aviso = \App\Models\Appointment::avisoDeEspacio(
            $cita->clinic_id,
            $cita->doctor_id,
            $cita->starts_at,
            $cita->ends_at,
            $cita->id,
        );

        if ($aviso) {
            \Filament\Notifications\Notification::make()
                ->title('La cita quedó sin tiempo de limpieza')
                ->body($aviso)
                ->warning()
                ->persistent()
                ->send();
        }
    }
}
