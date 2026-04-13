<?php

namespace App\Filament\Doctor\Resources\DoctorInvitationResource\Pages;

use App\Filament\Doctor\Concerns\HasFormHero;
use App\Filament\Doctor\Resources\DoctorInvitationResource;
use Filament\Resources\Pages\CreateRecord;

class CreateDoctorInvitation extends CreateRecord
{
    use HasFormHero;

    protected static string $resource = DoctorInvitationResource::class;

    protected static string $view = 'filament.doctor.resources.create-with-hero';

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['clinic_id'] = auth()->user()->clinic_id;
        $data['invited_by'] = auth()->id();

        return $data;
    }

    protected function getFormHeroConfig(): array
    {
        return [
            'title'    => 'Invitar doctor',
            'icon'     => '👨‍⚕️',
            'kicker'   => '➕ Agregar al equipo',
            'subtitle' => 'Invita a un doctor a unirse a tu clínica. Recibirá un link para registrarse por email o WhatsApp.',
            'gradient' => '#ec4899 0%, #d946ef 40%, #a855f7 100%',
            'accent'   => '#ec4899',
        ];
    }
}
