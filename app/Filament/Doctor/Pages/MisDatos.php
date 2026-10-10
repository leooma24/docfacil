<?php

namespace App\Filament\Doctor\Pages;

use Filament\Pages\Page;

/**
 * Bajar todos los datos del consultorio, y qué pasa con ellos si deja de
 * pagar. Es lo primero que pregunta quien ya perdió expedientes con otro
 * sistema.
 */
class MisDatos extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-arrow-down-tray';

    protected static ?int $navigationSort = 5;

    protected static ?string $navigationLabel = 'Sus datos';

    protected static ?string $title = 'Sus datos son suyos';

    protected static ?string $slug = 'sus-datos';

    protected static string $view = 'filament.doctor.pages.mis-datos';

    protected static ?string $navigationGroup = 'Mi cuenta';

    /** Bajar todos los expedientes es cosa del doctor, no de la asistente. */
    public static function canAccess(): bool
    {
        return auth()->check() && ! auth()->user()->esAsistente();
    }

    public function getViewData(): array
    {
        $clinica = auth()->user()->clinic;

        return [
            'pacientes' => $clinica->pacientesActuales(),
            'liga' => route('datos.exportar'),
        ];
    }
}
