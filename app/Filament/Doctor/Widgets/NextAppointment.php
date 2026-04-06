<?php

namespace App\Filament\Doctor\Widgets;

use App\Models\Appointment;
use Filament\Widgets\Widget;

class NextAppointment extends Widget
{
    protected static ?int $sort = -1;

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
