<?php

namespace App\Filament\Doctor\Pages;

use App\Models\Encuesta;
use Filament\Pages\Page;

/**
 * Lo que dicen los pacientes después de su visita (día 9 del plan,
 * 10-oct-2026): el promedio de estrellas y sus comentarios. Lo contestan en
 * su portal; solo lo ve el consultorio.
 */
class Opiniones extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-star';

    protected static ?string $navigationLabel = 'Opiniones';

    protected static ?string $title = 'Lo que dicen sus pacientes';

    protected static ?string $slug = 'opiniones';

    protected static string $view = 'filament.doctor.pages.opiniones';

    protected static ?string $navigationGroup = 'Consultorio';

    protected static ?int $navigationSort = 20;

    public static function shouldRegisterNavigation(): bool
    {
        return (bool) auth()->user()?->clinic?->hasFeature('patient_portal');
    }

    public function getViewData(): array
    {
        $clinicId = auth()->user()->clinic_id;
        $todas = Encuesta::with(['patient', 'doctor.user'])->where('clinic_id', $clinicId)->latest()->limit(100)->get();

        return [
            'encuestas' => $todas,
            'promedio' => round((float) Encuesta::where('clinic_id', $clinicId)->avg('estrellas'), 1),
            'cuantas' => Encuesta::where('clinic_id', $clinicId)->count(),
        ];
    }
}
