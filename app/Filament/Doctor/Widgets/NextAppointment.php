<?php

namespace App\Filament\Doctor\Widgets;

use App\Models\Appointment;
use Filament\Widgets\Widget;

class NextAppointment extends Widget
{
    public static function canView(): bool
    {
        // El que va empezando solo ve «Agendar mi primera cita» (11-oct-2026).
        if (auth()->user()?->clinic?->vaEmpezando()) {
            return false;
        }

        return true;
    }

    protected static ?int $sort = -6;

    protected int|string|array $columnSpan = 'full';

    protected static string $view = 'filament.doctor.widgets.next-appointment';

    public function getNextAppointment(): ?Appointment
    {
        return Appointment::where('clinic_id', auth()->user()->clinic_id)
            ->where('starts_at', '>=', now())
            ->whereIn('status', ['scheduled', 'confirmed'])
            ->with(['patient', 'doctor.user', 'service'])
            ->orderBy('starts_at')
            ->first();
    }
}
