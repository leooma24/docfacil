<?php

namespace App\Filament\Doctor\Widgets;

use App\Models\Appointment;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class TopServicesChart extends ChartWidget
{
    protected static ?string $heading = 'Servicios más solicitados';

    protected static ?int $sort = 4;

    protected function getData(): array
    {
        $clinicId = auth()->user()->clinic_id;

        $services = Appointment::where('appointments.clinic_id', $clinicId)
            ->whereNotNull('service_id')
            ->join('services', 'appointments.service_id', '=', 'services.id')
            ->select('services.name', DB::raw('count(*) as total'))
            ->groupBy('services.name')
            ->orderByDesc('total')
            ->limit(6)
            ->get();

        $colors = [
            'rgb(20, 184, 166)', 'rgb(59, 130, 246)', 'rgb(245, 158, 11)',
            'rgb(239, 68, 68)', 'rgb(139, 92, 246)', 'rgb(236, 72, 153)',
        ];

        return [
            'datasets' => [
                [
                    'label' => 'Citas',
                    'data' => $services->pluck('total')->toArray(),
                    'backgroundColor' => array_slice($colors, 0, $services->count()),
                ],
            ],
            'labels' => $services->pluck('name')->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}
