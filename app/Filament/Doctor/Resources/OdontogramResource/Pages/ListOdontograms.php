<?php

namespace App\Filament\Doctor\Resources\OdontogramResource\Pages;

use App\Filament\Doctor\Concerns\HasListHero;
use App\Filament\Doctor\Resources\OdontogramResource;
use App\Models\Odontogram;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListOdontograms extends ListRecords
{
    use HasListHero;

    protected static string $resource = OdontogramResource::class;

    protected static string $view = 'filament.doctor.resources.list-with-hero';

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('Nuevo Odontograma'),
        ];
    }

    public function getHeroConfig(): array
    {
        $clinicId = auth()->user()->clinic_id;
        $base = Odontogram::where('clinic_id', $clinicId);

        $total = (clone $base)->count();
        $month = (clone $base)->where('created_at', '>=', now()->startOfMonth())->count();
        $uniquePatients = (clone $base)->distinct('patient_id')->count('patient_id');
        $updatedThisWeek = (clone $base)->where('updated_at', '>=', now()->startOfWeek())->count();

        return [
            'title'    => 'Odontogramas',
            'icon'     => '🦷',
            'kicker'   => '🦷 Diagramas dentales',
            'subtitle' => 'Diagrama dental interactivo con 13 condiciones. Ideal para dentistas y compartible con el paciente.',
            'gradient' => '#06b6d4 0%, #0ea5e9 40%, #3b82f6 100%',
            'accent'   => '#0ea5e9',
            'stats' => [
                ['label' => '🦷 Total',            'value' => number_format($total)],
                ['label' => '📅 Este mes',         'value' => $month],
                ['label' => '👥 Pacientes',        'value' => number_format($uniquePatients)],
                ['label' => '✏️ Editados semana',  'value' => $updatedThisWeek],
            ],
        ];
    }
}
