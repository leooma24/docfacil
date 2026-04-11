<?php

namespace App\Filament\Doctor\Resources\PatientResource\Pages;

use App\Filament\Doctor\Concerns\HasListHero;
use App\Filament\Doctor\Resources\PatientResource;
use App\Models\Patient;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPatients extends ListRecords
{
    use HasListHero;

    protected static string $resource = PatientResource::class;

    protected static string $view = 'filament.doctor.resources.list-with-hero';

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('Nuevo Paciente'),
        ];
    }

    public function getHeroConfig(): array
    {
        $clinicId = auth()->user()->clinic_id;
        $total = Patient::where('clinic_id', $clinicId)->count();
        $activeLastMonth = Patient::where('clinic_id', $clinicId)
            ->whereHas('appointments', fn ($q) => $q->where('starts_at', '>=', now()->subDays(30)))
            ->count();
        $newThisMonth = Patient::where('clinic_id', $clinicId)
            ->where('created_at', '>=', now()->startOfMonth())
            ->count();
        $withBalance = Patient::where('clinic_id', $clinicId)
            ->whereHas('payments', fn ($q) => $q->where('status', 'pending'))
            ->count();

        return [
            'title'    => 'Pacientes',
            'icon'     => '👤',
            'kicker'   => '🩺 Tu base de pacientes',
            'subtitle' => 'Buscar, crear y gestionar todos tus pacientes. Click en uno para ver su perfil completo.',
            'gradient' => '#0d9488 0%, #0891b2 40%, #06b6d4 100%',
            'accent'   => '#0d9488',
            'stats' => [
                ['label' => '👥 Total',             'value' => number_format($total)],
                ['label' => '✨ Activos 30 días',    'value' => number_format($activeLastMonth)],
                ['label' => '🆕 Nuevos este mes',    'value' => number_format($newThisMonth)],
                ['label' => '💰 Con saldo',          'value' => number_format($withBalance)],
            ],
        ];
    }
}
