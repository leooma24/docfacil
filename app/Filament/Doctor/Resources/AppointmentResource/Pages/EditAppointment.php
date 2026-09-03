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

    protected function afterSave(): void
    {
        $this->avisarSiQuedoPegada();
        $this->avisarSiSeMovioDeMas();
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

    /**
     * Cuando una cita ya se movió de más, decirlo.
     *
     * El que reagenda tres veces casi nunca llega a la cuarta. No se le
     * quita el lugar — nada más que el doctor sepa que conviene confirmarle
     * por WhatsApp antes de guardarle la hora.
     */
    protected function avisarSiSeMovioDeMas(): void
    {
        $cita = $this->record;

        if (! $cita->seHaMovidoDeMas()) {
            return;
        }

        $paciente = trim(($cita->patient->first_name ?? '') . ' ' . ($cita->patient->last_name ?? ''));
        $quien = $paciente !== '' ? $paciente : 'Este paciente';

        \Filament\Notifications\Notification::make()
            ->title('Esta cita ya se movió ' . $cita->veces_reagendada . ' veces')
            ->body($quien . ' ha cambiado de horario varias veces. Vale la pena confirmarle por WhatsApp antes de apartarle el lugar.')
            ->warning()
            ->persistent()
            ->send();
    }
}
