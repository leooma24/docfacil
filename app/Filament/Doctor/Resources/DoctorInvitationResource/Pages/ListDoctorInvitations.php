<?php

namespace App\Filament\Doctor\Resources\DoctorInvitationResource\Pages;

use App\Filament\Doctor\Concerns\HasListHero;
use App\Filament\Doctor\Resources\DoctorInvitationResource;
use App\Models\DoctorInvitation;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListDoctorInvitations extends ListRecords
{
    use HasListHero;

    protected static string $resource = DoctorInvitationResource::class;

    protected static string $view = 'filament.doctor.resources.list-with-hero';

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('Invitar Doctor'),
        ];
    }

    public function getHeroConfig(): array
    {
        $clinicId = auth()->user()->clinic_id;
        $base = DoctorInvitation::where('clinic_id', $clinicId);

        $total = (clone $base)->count();
        $pending = (clone $base)->where('status', 'pending')->where('expires_at', '>', now())->count();
        $accepted = (clone $base)->where('status', 'accepted')->count();
        $expired = (clone $base)->where('status', 'pending')->where('expires_at', '<=', now())->count();

        return [
            'title'    => 'Su equipo',
            'subtitle' => 'Invite a su asistente y, desde el plan Pro, a otros doctores. Les llega una liga para poner su contraseña.',
            'gradient' => '#ec4899 0%, #d946ef 40%, #a855f7 100%',
            'accent'   => '#ec4899',
            'stats' => [
                ['label' => 'Total',            'value' => number_format($total)],
                ['label' => 'Pendientes',       'value' => $pending],
                ['label' => 'Aceptadas',        'value' => $accepted],
                ['label' => 'Expiradas',        'value' => $expired],
            ],
        ];
    }
}
