<?php

namespace App\Filament\Doctor\Resources\ServiceResource\Pages;

use App\Filament\Doctor\Concerns\HasFormHero;
use App\Filament\Doctor\Resources\ServiceResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditService extends EditRecord
{
    use HasFormHero;

    protected static string $resource = ServiceResource::class;

    protected static string $view = 'filament.doctor.resources.edit-with-hero';

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function getFormHeroConfig(): array
    {
        $name = $this->record->name ?? 'Servicio';
        $price = '$' . number_format($this->record->price ?? 0, 0);

        return [
            'title'    => 'Editar servicio',
            'icon'     => '🩺',
            'kicker'   => '✏️ ' . $name . ' · ' . $price,
            'subtitle' => 'Ajusta precio, duración o descripción del servicio.',
            'gradient' => '#f59e0b 0%, #f97316 40%, #ea580c 100%',
            'accent'   => '#f59e0b',
        ];
    }
}
