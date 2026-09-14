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
            Actions\Action::make('tiene_expediente')
                ->label('Tiene expediente')
                ->icon('heroicon-o-lock-closed')
                ->color('gray')
                ->disabled()
                ->tooltip('No se puede borrar: la NOM-004 pide conservar su expediente al menos 5 años.')
                ->visible(fn () => $this->record->tieneExpediente()),
            Actions\DeleteAction::make()
                ->visible(fn () => ! $this->record->tieneExpediente()),
        ];
    }

    protected function getFormHeroConfig(): array
    {
        $name = trim(($this->record->first_name ?? '') . ' ' . ($this->record->last_name ?? ''));

        return [
            'title'    => 'Editar paciente',
            'icon'     => '👤',
            'kicker'   => '✏️ ' . ($name ?: 'Paciente'),
            'subtitle' => 'Actualiza datos de contacto, antecedentes y preferencias del paciente.',
            'gradient' => '#0d9488 0%, #0891b2 40%, #06b6d4 100%',
            'accent'   => '#0d9488',
        ];
    }
}
