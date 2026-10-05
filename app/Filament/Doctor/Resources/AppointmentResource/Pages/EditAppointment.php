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
            Actions\Action::make('iniciarConsulta')
                ->label('Iniciar consulta')
                ->icon('heroicon-o-play-circle')
                ->url(fn () => route('filament.doctor.pages.consulta', ['appointment' => $this->record->id]))
                ->visible(fn () => in_array($this->record->status, ['scheduled', 'confirmed', 'in_progress'], true)),
            // La cita por venir se cancela, no se borra: así queda el hueco
            // y la lista de espera se entera. Borrar queda para la que ya
            // pasó o ya estaba cancelada.
            Actions\Action::make('cancelarCita')
                ->label('Cancelar cita')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->requiresConfirmation()
                ->visible(fn () => $this->porVenir())
                ->action(function () {
                    $this->record->update(['status' => 'cancelled']);
                    AppointmentResource::avisarDelHueco($this->record);
                    $this->refreshFormData(['status']);
                }),
            Actions\DeleteAction::make()
                ->visible(fn () => ! $this->porVenir()),
        ];
    }

    private function porVenir(): bool
    {
        return in_array($this->record->status, ['scheduled', 'confirmed'], true) && $this->record->starts_at?->isFuture();
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
