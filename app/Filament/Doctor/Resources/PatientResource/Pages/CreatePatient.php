<?php

namespace App\Filament\Doctor\Resources\PatientResource\Pages;

use App\Filament\Doctor\Concerns\HasFormHero;
use App\Filament\Doctor\Resources\PatientResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePatient extends CreateRecord
{
    use HasFormHero;

    protected static string $resource = PatientResource::class;

    protected static string $view = 'filament.doctor.resources.create-with-hero';

    /**
     * Si ya no caben, se detiene antes de guardar.
     *
     * El modelo lanza la excepción de todos modos, pero al doctor le sale
     * una pantalla de error. Aquí se le dice con palabras y se le deja el
     * camino a actualizar su plan.
     */
    protected function beforeCreate(): void
    {
        $clinica = auth()->user()->clinic;

        if ($clinica && ! $clinica->puedeAgregarPacientes()) {
            \Filament\Notifications\Notification::make()
                ->title('Llegaste al tope de tu plan')
                ->body($clinica->mensajeDeTopeDePacientes())
                ->warning()
                ->persistent()
                ->actions([
                    \Filament\Notifications\Actions\Action::make('subir')
                        ->label('Ver planes')
                        ->url(\App\Filament\Doctor\Pages\Upgrade::getUrl())
                        ->button(),
                ])
                ->send();

            $this->halt();
        }
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['clinic_id'] = auth()->user()->clinic_id;

        return $data;
    }

    protected function getFormHeroConfig(): array
    {
        return [
            'title'    => 'Nuevo paciente',
            'icon'     => '👤',
            'kicker'   => '➕ Agregar paciente',
            'subtitle' => 'Datos básicos del paciente — teléfono y nombre bastan para empezar.',
            'gradient' => '#0d9488 0%, #0891b2 40%, #06b6d4 100%',
            'accent'   => '#0d9488',
        ];
    }
}
