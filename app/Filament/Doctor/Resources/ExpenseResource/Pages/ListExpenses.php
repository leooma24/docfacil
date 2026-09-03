<?php

namespace App\Filament\Doctor\Resources\ExpenseResource\Pages;

use App\Filament\Doctor\Concerns\HasListHero;
use App\Filament\Doctor\Resources\ExpenseResource;
use App\Models\Expense;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListExpenses extends ListRecords
{
    use HasListHero;

    protected static string $resource = ExpenseResource::class;

    protected static string $view = 'filament.doctor.resources.list-with-hero';

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('Nuevo gasto'),
        ];
    }

    public function getHeroConfig(): array
    {
        $clinicId = auth()->user()->clinic_id;

        $esteMes = Expense::totalEntre($clinicId, now()->startOfMonth(), now()->endOfMonth());
        $mesPasado = Expense::totalEntre(
            $clinicId,
            now()->subMonthNoOverflow()->startOfMonth(),
            now()->subMonthNoOverflow()->endOfMonth(),
        );

        $porCategoria = Expense::porCategoria($clinicId, now()->startOfMonth(), now()->endOfMonth());
        $mayor = array_key_first($porCategoria);

        return [
            'title'    => 'Gastos',
            'icon'     => '💸',
            'kicker'   => '📉 Lo que sale',
            'subtitle' => 'Anota lo que gastas y el corte del mes te dice cuánto te quedó de verdad.',
            'gradient' => '#b45309 0%, #d97706 40%, #f59e0b 100%',
            'accent'   => '#d97706',
            'stats' => [
                ['label' => '📅 Este mes',      'value' => '$' . number_format($esteMes, 2)],
                ['label' => '⏮️ Mes pasado',    'value' => '$' . number_format($mesPasado, 2)],
                ['label' => '🔝 Mayor gasto',   'value' => $mayor ?? '—'],
                ['label' => '🔁 Cada mes',      'value' => Expense::where('clinic_id', $clinicId)->where('is_recurring', true)->count()],
            ],
        ];
    }
}
