<?php

namespace App\Filament\Doctor\Resources\AppointmentResource\Pages;

use App\Filament\Doctor\Concerns\HasListHero;
use App\Filament\Doctor\Resources\AppointmentResource;
use App\Models\Appointment;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAppointments extends ListRecords
{
    use HasListHero;

    protected static string $resource = AppointmentResource::class;

    protected static string $view = 'filament.doctor.resources.list-with-hero';

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('Nueva Cita'),
        ];
    }

    public function getHeroConfig(): array
    {
        $clinicId = auth()->user()->clinic_id;
        $base = Appointment::where('clinic_id', $clinicId);

        $today = (clone $base)->whereDate('starts_at', today())->count();
        $week = (clone $base)->whereBetween('starts_at', [now()->startOfWeek(), now()->endOfWeek()])->count();
        $pending = (clone $base)->whereIn('status', ['scheduled', 'confirmed'])->where('starts_at', '>=', now())->count();
        $completed = (clone $base)->where('status', 'completed')
            ->where('starts_at', '>=', now()->startOfMonth())->count();

        return [
            'title'    => 'Citas',
            'icon'     => '📅',
            'kicker'   => '🗓️ Tu agenda completa',
            'subtitle' => 'Lista de todas las citas. Usa el Calendario para arrastrar y reagendar visualmente.',
            'gradient' => '#3b82f6 0%, #0891b2 40%, #0ea5e9 100%',
            'accent'   => '#3b82f6',
            'stats' => [
                ['label' => '📌 Hoy',             'value' => $today],
                ['label' => '📊 Esta semana',     'value' => $week],
                ['label' => '⏳ Próximas',        'value' => $pending],
                ['label' => '✅ Terminadas mes',  'value' => $completed],
            ],
        ];
    }
}
