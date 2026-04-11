<?php

namespace App\Filament\Doctor\Pages;

use App\Filament\Doctor\Widgets\CalendarWidget;
use App\Models\Appointment;
use Filament\Pages\Page;

class CalendarPage extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-calendar';

    protected static ?string $navigationLabel = 'Calendario';

    protected static ?string $title = 'Calendario';

    protected static ?string $slug = 'calendario';

    protected static ?int $navigationSort = 0;

    protected static string $view = 'filament.doctor.pages.calendar-page';

    // NOTE: CalendarWidget se renderiza manualmente en la vista, NO como header widget.
    // Tener ambos causaba que se mostrara 2 veces.

    public function getStatsProperty(): array
    {
        $clinicId = auth()->user()->clinic_id;
        $now = now();

        return [
            'today' => Appointment::where('clinic_id', $clinicId)
                ->whereDate('starts_at', $now->toDateString())
                ->whereIn('status', ['scheduled', 'confirmed', 'in_progress'])
                ->count(),
            'week' => Appointment::where('clinic_id', $clinicId)
                ->whereBetween('starts_at', [$now->copy()->startOfWeek(), $now->copy()->endOfWeek()])
                ->count(),
            'pending_confirmation' => Appointment::where('clinic_id', $clinicId)
                ->where('status', 'scheduled')
                ->where('starts_at', '>=', $now)
                ->count(),
            'completed_this_week' => Appointment::where('clinic_id', $clinicId)
                ->whereBetween('starts_at', [$now->copy()->startOfWeek(), $now])
                ->where('status', 'completed')
                ->count(),
        ];
    }
}
