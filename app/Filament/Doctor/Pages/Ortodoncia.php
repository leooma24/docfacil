<?php

namespace App\Filament\Doctor\Pages;

use App\Support\TableroDeOrtodoncia;
use Filament\Pages\Page;

/**
 * Los pacientes en tratamiento de ortodoncia (día 7 del plan, 10-oct-2026):
 * control atrasado, sin próxima cita y mensualidad vencida. Sale en el menú
 * solo si el consultorio tiene servicios de ortodoncia.
 */
class Ortodoncia extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-face-smile';

    protected static ?string $navigationLabel = 'Ortodoncia';

    protected static ?string $title = 'Ortodoncia';

    protected static ?string $slug = 'ortodoncia';

    protected static string $view = 'filament.doctor.pages.ortodoncia';

    protected static ?string $navigationGroup = 'Pacientes';

    protected static ?int $navigationSort = 8;

    /** todos | atrasados | sin_cita | deben */
    public string $filtro = 'todos';

    public static function shouldRegisterNavigation(): bool
    {
        $clinicId = auth()->user()?->clinic_id;

        return $clinicId && TableroDeOrtodoncia::laHace($clinicId);
    }

    public function getViewData(): array
    {
        $todos = TableroDeOrtodoncia::de(auth()->user()->clinic_id);
        $filas = match ($this->filtro) {
            'atrasados' => $todos->where('atrasado', true),
            'sin_cita' => $todos->whereNull('proximo'),
            'deben' => $todos->where('vencido', '>', 0),
            default => $todos,
        };

        return [
            'filas' => $filas->values(),
            'cuantos' => [
                'todos' => $todos->count(),
                'atrasados' => $todos->where('atrasado', true)->count(),
                'sin_cita' => $todos->whereNull('proximo')->count(),
                'deben' => $todos->where('vencido', '>', 0)->count(),
            ],
            'servicio' => TableroDeOrtodoncia::serviciosDe(auth()->user()->clinic_id)->first(),
        ];
    }
}
