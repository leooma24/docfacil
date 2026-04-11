<?php

namespace App\Filament\Doctor\Resources\PrescriptionResource\Pages;

use App\Filament\Doctor\Concerns\HasListHero;
use App\Filament\Doctor\Resources\PrescriptionResource;
use App\Models\Prescription;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPrescriptions extends ListRecords
{
    use HasListHero;

    protected static string $resource = PrescriptionResource::class;

    protected static string $view = 'filament.doctor.resources.list-with-hero';

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('Nueva Receta'),
        ];
    }

    public function getHeroConfig(): array
    {
        $clinicId = auth()->user()->clinic_id;
        $base = Prescription::where('clinic_id', $clinicId);

        $total = (clone $base)->count();
        $today = (clone $base)->whereDate('created_at', today())->count();
        $month = (clone $base)->where('created_at', '>=', now()->startOfMonth())->count();
        $uniquePatients = (clone $base)->distinct('patient_id')->count('patient_id');

        return [
            'title'    => 'Recetas',
            'icon'     => '💊',
            'kicker'   => '📝 Recetas digitales',
            'subtitle' => 'Genera recetas PDF con tu membrete, cédula y firma. Descarga o envía por WhatsApp.',
            'gradient' => '#8b5cf6 0%, #a855f7 40%, #c084fc 100%',
            'accent'   => '#8b5cf6',
            'stats' => [
                ['label' => '💊 Total',           'value' => number_format($total)],
                ['label' => '✨ Hoy',             'value' => $today],
                ['label' => '📅 Este mes',        'value' => $month],
                ['label' => '👥 Pacientes',       'value' => number_format($uniquePatients)],
            ],
        ];
    }
}
