<?php

namespace App\Filament\Doctor\Resources\PatientResource\Pages;

use App\Filament\Doctor\Concerns\HasListHero;
use App\Filament\Doctor\Imports\PatientImporter;
use App\Filament\Doctor\Resources\PatientResource;
use App\Models\Patient;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\HtmlString;

class ListPatients extends ListRecords
{
    use HasListHero;

    protected static string $resource = PatientResource::class;

    protected static string $view = 'filament.doctor.resources.list-with-hero';

    protected function getHeaderActions(): array
    {
        $clinica = auth()->user()->clinic;
        $restantes = $clinica?->pacientesRestantes();
        $lleno = $restantes !== null && $restantes < 1;

        // Al llegar al tope no se esconde el botón: se cambia por el que
        // sí sirve. Un botón que no hace nada, o que truena al picarle,
        // deja al doctor sin saber qué pasó.
        if ($lleno) {
            return [
                Actions\Action::make('subir_plan')
                    ->label('Actualiza tu plan para agregar más')
                    ->icon('heroicon-o-arrow-up-circle')
                    ->color('warning')
                    ->url(\App\Filament\Doctor\Pages\Upgrade::getUrl())
                    ->tooltip($clinica->mensajeDeTopeDePacientes()),
            ];
        }

        return [
            // Sin esto, un dentista con 300 pacientes tendría que capturarlos
            // a mano uno por uno. Es la razón más común de que alguien diga
            // "sí, se ve bien" y nunca empiece.
            Actions\ImportAction::make()
                ->importer(PatientImporter::class)
                ->label('Importar de Excel')
                ->icon('heroicon-o-arrow-up-tray')
                ->color('gray')
                ->modalHeading('Importar tus pacientes')
                ->modalDescription(new HtmlString(
                    '<p style="margin-bottom:.5rem;">Sube el archivo con los pacientes que ya tenías. Sirve Excel, Google Sheets o cualquier sistema viejo.</p>'
                    . '<p style="margin-bottom:.5rem;"><strong>Si los tienes en Google Sheets:</strong> Archivo → Descargar → Valores separados por comas (.csv).</p>'
                    . '<p style="margin-bottom:.5rem;"><strong>Si los tienes en Excel:</strong> Archivo → Guardar como → CSV UTF-8.</p>'
                    . '<p>Después te dejamos decir qué columna es cuál. No importa el orden ni cómo se llamen.</p>'
                    . ($restantes !== null
                        ? '<p style="margin-top:.75rem;color:#b45309;"><strong>Tu plan permite ' . $restantes . ' más.</strong> Si el archivo trae de sobra, se importan los que caben y el resto te lo decimos al final.</p>'
                        : '')
                ))
                ->modalSubmitActionLabel('Importar'),
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

        $clinica = auth()->user()->clinic;
        $limite = $clinica?->limitePacientes();
        $restantes = $clinica?->pacientesRestantes();

        return [
            'title'    => 'Pacientes',
            'icon'     => '👤',
            'kicker'   => '🩺 Tu base de pacientes',
            'subtitle' => 'Buscar, crear y gestionar todos tus pacientes. Click en uno para ver su perfil completo.',
            'gradient' => '#0d9488 0%, #0891b2 40%, #06b6d4 100%',
            'accent'   => '#0d9488',
            'stats' => [
                ['label' => '👥 Total',             'value' => number_format($total)
                    . ($restantes !== null ? ' de ' . number_format($limite) : '')],
                ['label' => '✨ Activos 30 días',    'value' => number_format($activeLastMonth)],
                ['label' => '🆕 Nuevos este mes',    'value' => number_format($newThisMonth)],
                $restantes !== null
                    ? ['label' => '📦 Te quedan', 'value' => number_format($restantes)]
                    : ['label' => '💰 Con saldo', 'value' => number_format($withBalance)],
            ],
        ];
    }
}
