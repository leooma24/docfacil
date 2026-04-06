<?php

namespace App\Filament\Doctor\Widgets;

use App\Models\Payment;
use Filament\Widgets\ChartWidget;

class IncomeChart extends ChartWidget
{
    protected static ?string $heading = 'Ingresos mensuales';

    protected static ?int $sort = 3;

    protected function getData(): array
    {
        $clinicId = auth()->user()->clinic_id;
        $months = collect();

        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);

            $income = Payment::where('clinic_id', $clinicId)
                ->where('status', 'paid')
                ->whereMonth('payment_date', $date->month)
                ->whereYear('payment_date', $date->year)
                ->sum('amount');

            $months->push([
                'label' => $date->translatedFormat('M Y'),
                'income' => $income,
            ]);
        }

        return [
            'datasets' => [
                [
                    'label' => 'Ingresos',
                    'data' => $months->pluck('income')->toArray(),
                    'backgroundColor' => 'rgba(16, 185, 129, 0.2)',
                    'borderColor' => 'rgb(20, 184, 166)',
                    'fill' => true,
                ],
            ],
            'labels' => $months->pluck('label')->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
