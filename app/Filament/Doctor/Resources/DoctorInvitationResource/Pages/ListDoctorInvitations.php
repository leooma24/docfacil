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
            'title'    => 'Invitar Doctores',
            'icon'     => '👨‍⚕️',
            'kicker'   => '🩺 Tu equipo médico',
            'subtitle' => 'Invita a otros doctores a unirse a tu clínica. Reciben un link de registro por email y WhatsApp.',
            'gradient' => '#ec4899 0%, #d946ef 40%, #a855f7 100%',
            'accent'   => '#ec4899',
            'stats' => [
                ['label' => '📨 Total',            'value' => number_format($total)],
                ['label' => '⏳ Pendientes',       'value' => $pending],
                ['label' => '✅ Aceptadas',        'value' => $accepted],
                ['label' => '⏰ Expiradas',        'value' => $expired],
            ],
        ];
    }
}
