<?php

namespace App\Filament\Doctor\Resources\ServiceResource\Pages;

use App\Filament\Doctor\Concerns\HasListHero;
use App\Filament\Doctor\Resources\ServiceResource;
use App\Models\Service;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListServices extends ListRecords
{
    use HasListHero;

    protected static string $resource = ServiceResource::class;

    protected static string $view = 'filament.doctor.resources.list-with-hero';

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('Nuevo Servicio'),
        ];
    }

    public function getHeroConfig(): array
    {
        $clinicId = auth()->user()->clinic_id;
        $base = Service::where('clinic_id', $clinicId);

        $total = (clone $base)->count();
        $active = (clone $base)->where('is_active', true)->count();
        $avgPrice = (clone $base)->where('is_active', true)->avg('price') ?? 0;
        $maxPrice = (clone $base)->where('is_active', true)->max('price') ?? 0;

        return [
            'title'    => 'Servicios',
            'icon'     => '🩺',
            'kicker'   => '💼 Tu catálogo',
            'subtitle' => 'Servicios que ofreces con precios y duración. Se asignan a citas y cobros automáticos.',
            'gradient' => '#f59e0b 0%, #f97316 40%, #ea580c 100%',
            'accent'   => '#f59e0b',
            'stats' => [
                ['label' => '🩺 Total',          'value' => number_format($total)],
                ['label' => '✅ Activos',        'value' => number_format($active)],
                ['label' => '💰 Precio promedio','value' => '$' . number_format($avgPrice)],
                ['label' => '🏆 Más caro',       'value' => '$' . number_format($maxPrice)],
            ],
        ];
    }
}
