<?php

namespace App\Filament\Doctor\Pages;

use App\Models\Clinic;
use App\Support\Modulos as LosModulos;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

/**
 * Prender y apagar módulos (11-oct-2026). Lo de todos los días siempre está;
 * lo demás lo prende el dueño cuando lo vaya a usar. Romita: «nada de ruido».
 */
class Modulos extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-squares-plus';

    protected static ?string $navigationLabel = 'Módulos';

    protected static ?string $title = 'Módulos';

    protected static ?string $slug = 'modulos';

    protected static string $view = 'filament.doctor.pages.modulos';

    protected static ?string $navigationGroup = 'Mi cuenta';

    protected static ?int $navigationSort = 2;

    public static function canAccess(): bool
    {
        return (bool) auth()->user()?->esDuenoDelConsultorio();
    }

    public function getSubheading(): ?string
    {
        return 'Lo de todos los días (agenda, citas, consulta, pacientes, expediente, odontograma, recetas, cobros y presupuestos) siempre está. Prenda lo demás cuando lo vaya a usar; al apagarlo no se borra nada.';
    }

    public function getViewData(): array
    {
        $clinica = auth()->user()->clinic;

        return [
            'modulos' => collect(LosModulos::LISTA)->map(fn ($m, $clave) => [
                'clave' => $clave,
                'nombre' => $m[0],
                'que_es' => $m[1],
                'prendido' => $clinica->tieneModulo($clave),
                'desde' => $m[2] && ! $clinica->planIncluye($m[2]) ? self::desdeQuePlan($m[2]) : null,
            ])->values()->all(),
        ];
    }

    public function alternar(string $clave): void
    {
        if (! LosModulos::existe($clave)) {
            return;
        }

        $clinica = auth()->user()->clinic;
        // NULL era «todo prendido»: al primer cambio se vuelve la lista completa.
        $prendidos = $clinica->modulos ?? array_keys(LosModulos::LISTA);
        $prendidos = in_array($clave, $prendidos, true)
            ? array_values(array_diff($prendidos, [$clave]))
            : [...$prendidos, $clave];

        $clinica->update(['modulos' => $prendidos]);

        $nombre = LosModulos::LISTA[$clave][0];
        Notification::make()
            ->title(in_array($clave, $prendidos, true) ? "{$nombre} quedó prendido" : "{$nombre} quedó apagado")
            ->success()
            ->send();

        $this->redirect(static::getUrl(), navigate: false);
    }

    private static function desdeQuePlan(string $funcion): string
    {
        foreach (['basico' => 'Básico', 'profesional' => 'Pro', 'clinica' => 'Clínica'] as $plan => $nombre) {
            if (in_array($funcion, Clinic::featuresForPlan($plan), true)) {
                return "Desde el plan {$nombre}";
            }
        }

        return 'No viene en su plan';
    }
}
