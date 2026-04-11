<?php

namespace App\Filament\Doctor\Resources\MedicalRecordResource\Pages;

use App\Filament\Doctor\Concerns\HasListHero;
use App\Filament\Doctor\Resources\MedicalRecordResource;
use App\Models\MedicalRecord;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMedicalRecords extends ListRecords
{
    use HasListHero;

    protected static string $resource = MedicalRecordResource::class;

    protected static string $view = 'filament.doctor.resources.list-with-hero';

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('Nueva Consulta'),
        ];
    }

    public function getHeroConfig(): array
    {
        $clinicId = auth()->user()->clinic_id;
        $base = MedicalRecord::where('clinic_id', $clinicId);

        $total = (clone $base)->count();
        $today = (clone $base)->whereDate('created_at', today())->count();
        $month = (clone $base)->where('created_at', '>=', now()->startOfMonth())->count();
        $uniquePatients = (clone $base)->distinct('patient_id')->count('patient_id');

        return [
            'title'    => 'Expediente Clínico',
            'icon'     => '📋',
            'kicker'   => '🩺 Historial médico',
            'subtitle' => 'Todas las consultas, diagnósticos y tratamientos registrados. Cumple con la NOM-004 y es inmutable.',
            'gradient' => '#ef4444 0%, #f97316 40%, #f59e0b 100%',
            'accent'   => '#ef4444',
            'stats' => [
                ['label' => '📋 Total',            'value' => number_format($total)],
                ['label' => '🩺 Hoy',              'value' => $today],
                ['label' => '📅 Este mes',         'value' => $month],
                ['label' => '👥 Pacientes atendidos','value' => number_format($uniquePatients)],
            ],
        ];
    }
}
