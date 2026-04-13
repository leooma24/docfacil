<?php

namespace App\Filament\Doctor\Resources\ConsentFormResource\Pages;

use App\Filament\Doctor\Concerns\HasFormHero;
use App\Filament\Doctor\Resources\ConsentFormResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditConsentForm extends EditRecord
{
    use HasFormHero;

    protected static string $resource = ConsentFormResource::class;

    protected static string $view = 'filament.doctor.resources.edit-with-hero';

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function getFormHeroConfig(): array
    {
        $title = $this->record->title ?? 'Consentimiento';
        $signed = $this->record->signed_at ? ' · ✅ Firmado' : ' · ⏳ Pendiente';

        return [
            'title'    => 'Editar consentimiento',
            'icon'     => '✍️',
            'kicker'   => '✏️ ' . $title . $signed,
            'subtitle' => 'Actualiza el texto del consentimiento o recoge la firma del paciente.',
            'gradient' => '#6366f1 0%, #8b5cf6 40%, #a855f7 100%',
            'accent'   => '#6366f1',
        ];
    }
}
