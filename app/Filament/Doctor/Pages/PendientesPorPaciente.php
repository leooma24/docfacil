<?php

namespace App\Filament\Doctor\Pages;

use App\Filament\Doctor\Concerns\GatedByPlanFeature;
use App\Filament\Doctor\Resources\TreatmentPlanResource;
use App\Support\PendientesDelConsultorio;
use Filament\Pages\Page;

/**
 * Lo que se quedó a medias con cada paciente.
 *
 * Del dentista (10-oct-2026): de cada 10 presupuestos grandes terminan 3 o 4,
 * y no hay una lista de los que dijeron "lo voy a pensar". Aquí están:
 * los presupuestos sin respuesta y los tratamientos que se quedaron a medias,
 * cada uno con lo que debe el paciente y su recordatorio a 1 clic. Un
 * recordatorio al mes, no más; nada sale solo.
 */
class PendientesPorPaciente extends Page
{
    use GatedByPlanFeature;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?string $navigationLabel = 'Pendientes';

    protected static ?string $title = 'Pendientes por paciente';

    protected static ?string $slug = 'pendientes';

    protected static string $view = 'filament.doctor.pages.pendientes-por-paciente';

    protected static ?string $navigationGroup = 'Pacientes';

    protected static ?int $navigationSort = 7;

    protected static function planFeature(): string
    {
        return 'treatment_plans';
    }

    public static function shouldRegisterNavigation(): bool
    {
        return static::clinicHasPlanFeature();
    }

    public static function canAccess(): bool
    {
        return static::clinicHasPlanFeature();
    }

    /** Cuántos presupuestos tocan hoy, para el globito del menú. */
    public static function getNavigationBadge(): ?string
    {
        $user = auth()->user();
        if (! $user || ! static::clinicHasPlanFeature()) {
            return null;
        }

        $tocan = PendientesDelConsultorio::cuantosTocan($user->clinic_id);

        return $tocan > 0 ? (string) $tocan : null;
    }

    public function getViewData(): array
    {
        $clinicId = auth()->user()->clinic_id;

        return [
            'sinRespuesta' => PendientesDelConsultorio::sinRespuesta($clinicId),
            'aMedias' => PendientesDelConsultorio::aMedias($clinicId),
            'diasEntreRecordatorios' => PendientesDelConsultorio::DIAS_ENTRE_RECORDATORIOS,
        ];
    }

    public function ligaParaAgendar($plan): string
    {
        return TreatmentPlanResource::urlParaAgendar($plan);
    }
}
