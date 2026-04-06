<?php

namespace App\Filament\Doctor\Widgets;

use App\Models\Appointment;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

class AppointmentsChart extends ChartWidget
{
    protected static ?string $heading = 'Citas por semana';

    protected static ?int $sort = 2;

    protected function getData(): array
    {
        $clinicId = auth()->user()->clinic_id;
        $weeks = collect();

        for ($i = 7; $i >= 0; $i--) {
            $startOfWeek = now()->subWeeks($i)->startOfWeek();
            $endOfWeek = now()->subWeeks($i)->endOfWeek();

            $count = Appointment::where('clinic_id', $clinicId)
                ->whereBetween('starts_at', [$startOfWeek, $endOfWeek])
                ->count();

            $weeks->push([
                'label' => $startOfWeek->format('d M'),
                'count' => $count,
            ]);
        }

        return [
            'datasets' => [
                [
                    'label' => 'Citas',
                    'data' => $weeks->pluck('count')->toArray(),
                    'backgroundColor' => 'rgba(16, 185, 129, 0.2)',
                    'borderColor' => 'rgb(20, 184, 166)',
                ],
            ],
            'labels' => $weeks->pluck('label')->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
