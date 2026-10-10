<?php

namespace App\Filament\Doctor\Resources\PatientResource\Pages;

use App\Filament\Doctor\Concerns\HasFormHero;
use App\Filament\Doctor\Resources\PatientResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPatient extends EditRecord
{
    use HasFormHero;

    protected static string $resource = PatientResource::class;

    protected static string $view = 'filament.doctor.resources.edit-with-hero';

    protected function getHeaderActions(): array
    {
        return [
            // Con expediente no se ofrece borrar: se le dice por qué, en vez
            // de un botón que al final no hace nada.
            Actions\Action::make('no_se_borra')
                ->label(fn () => $this->record->tieneExpediente() ? 'Tiene expediente' : 'Tiene cobros')
                ->icon('heroicon-o-lock-closed')
                ->color('gray')
                ->disabled()
                ->tooltip(fn () => 'No se puede borrar. ' . $this->record->porQueNoSeBorra())
                ->visible(fn () => $this->record->porQueNoSeBorra() !== null),
            Actions\DeleteAction::make()
                ->modalDescription(fn () => ($citas = $this->record->appointments()->count())
                    ? "Se borran también sus {$citas} " . ($citas === 1 ? 'cita' : 'citas') . '. No se puede deshacer.'
                    : 'No se puede deshacer.')
                ->visible(fn () => $this->record->porQueNoSeBorra() === null),
        ];
    }

    protected function getFormHeroConfig(): array
    {

        return [
            'title'    => 'Editar paciente',
            'subtitle' => 'Cambie sus datos de contacto, antecedentes y preferencias.',
            'gradient' => '#0d9488 0%, #0891b2 40%, #06b6d4 100%',
            'accent'   => '#0d9488',
        ];
    }
}
