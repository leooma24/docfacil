<?php

namespace App\Filament\Doctor\Resources\OdontogramResource\Pages;

use App\Filament\Doctor\Concerns\HasFormHero;
use App\Filament\Doctor\Resources\OdontogramResource;
use App\Support\OdontogramaClinico;
use Filament\Resources\Pages\CreateRecord;

class CreateOdontogram extends CreateRecord
{
    use HasFormHero;

    protected static string $resource = OdontogramResource::class;

    protected static string $view = 'filament.doctor.resources.create-with-hero';

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['clinic_id'] = auth()->user()->clinic_id;

        return $data;
    }

    protected function afterCreate(): void
    {
        $this->arrancarDeLoAnterior();

        // Redirect to edit so user can use the odontogram editor
        $this->redirect(OdontogramResource::getUrl('edit', ['record' => $this->record]));
    }

    /**
     * El odontograma nuevo arranca de lo que ya se sabe del paciente: el
     * doctor marca lo que cambió en vez de volver a capturar la boca.
     */
    public function arrancarDeLoAnterior(): void
    {
        $anterior = OdontogramaClinico::ultimo($this->record->clinic_id, $this->record->patient_id, antesDe: $this->record->id);

        if ($anterior) {
            OdontogramaClinico::copiarDientes($anterior, $this->record);
        }
    }

    protected function getFormHeroConfig(): array
    {
        return [
            'title'    => 'Nuevo odontograma',
            'icon'     => '🦷',
            'kicker'   => '➕ Crear diagrama dental',
            'subtitle' => 'Selecciona el paciente. En el siguiente paso marcarás las condiciones dientes por dientes.',
            'gradient' => '#06b6d4 0%, #0ea5e9 40%, #3b82f6 100%',
            'accent'   => '#0ea5e9',
        ];
    }
}
