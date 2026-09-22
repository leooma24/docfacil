<?php

namespace App\Filament\Doctor\Resources\HazardousWasteResource\Pages;

use App\Filament\Doctor\Concerns\HasListHero;
use App\Filament\Doctor\Resources\HazardousWasteResource;
use App\Models\HazardousWaste;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListHazardousWastes extends ListRecords
{
    use HasListHero;

    protected static string $resource = HazardousWasteResource::class;

    protected static string $view = 'filament.doctor.resources.list-with-hero';

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('Registrar residuo'),
        ];
    }

    public function getHeroConfig(): array
    {
        $clinicId = auth()->user()->clinic_id;

        $delAnio = HazardousWaste::where('clinic_id', $clinicId)
            ->whereYear('disposed_on', now()->year);

        $total = (clone $delAnio)->count();
        $sinRegistro = (clone $delAnio)
            ->where(fn ($q) => $q->whereNull('manifest_number')->orWhere('manifest_number', ''))
            ->count();
        $materiales = (clone $delAnio)->distinct()->count('material');

        return [
            'title'    => 'Residuos',
            'icon'     => '⚠️',
            'kicker'   => '🧾 Cumplimiento',
            'subtitle' => 'Amalgama, mercurio y residuos biológicos. No descuentan inventario: son el registro que se enseña en una revisión.',
            'gradient' => '#f59e0b 0%, #d97706 40%, #b45309 100%',
            'accent'   => '#f59e0b',
            'stats' => [
                ['label' => '📋 Este año',        'value' => number_format($total)],
                ['label' => '⚠️ Sin registro',    'value' => number_format($sinRegistro)],
                ['label' => '🧪 Materiales',      'value' => number_format($materiales)],
            ],
        ];
    }
}
