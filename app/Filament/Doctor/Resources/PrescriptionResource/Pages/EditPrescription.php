<?php

namespace App\Filament\Doctor\Resources\PrescriptionResource\Pages;

use App\Filament\Doctor\Concerns\HasFormHero;
use App\Filament\Doctor\Resources\PrescriptionResource;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditPrescription extends EditRecord
{
    use HasFormHero;

    protected static string $resource = PrescriptionResource::class;

    protected static string $view = 'filament.doctor.resources.edit-with-hero';

    public function mount(int|string $record): void
    {
        parent::mount($record);

        // Igual que las notas: a las 24 horas la receta queda bloqueada.
        if ($this->record->isLocked()) {
            Notification::make()
                ->title('Esta receta ya no se puede editar')
                ->body('Pasaron 24 horas desde que se hizo. Si hay que corregirla, haz una receta nueva.')
                ->warning()
                ->send();

            $this->redirect(PrescriptionResource::getUrl('index'));
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->visible(fn () => ! $this->record->isLocked()),
        ];
    }

    protected function getFormHeroConfig(): array
    {
        $patient = $this->record->patient ?? null;
        $name = $patient ? trim($patient->first_name . ' ' . $patient->last_name) : 'Receta';

        return [
            'title'    => 'Editar receta',
            'icon'     => '💊',
            'kicker'   => '✏️ ' . $name,
            'subtitle' => 'Actualiza medicamentos, dosis o notas de la receta.',
            'gradient' => '#8b5cf6 0%, #a855f7 40%, #c084fc 100%',
            'accent'   => '#8b5cf6',
        ];
    }
}
