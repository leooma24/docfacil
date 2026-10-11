<?php

namespace App\Filament\Doctor\Pages;

use App\Models\User;
use Filament\Pages\Page;
use Spatie\Activitylog\Models\Activity;

/**
 * Todo lo que pasó en el consultorio, quién y cuándo (día 8 del plan,
 * 10-oct-2026): quién abrió cada expediente y quién creó o cambió notas,
 * recetas, cobros y citas. Solo el dueño la ve. Cada paciente tiene además
 * su propia pestaña Bitácora.
 */
class BitacoraDelConsultorio extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-finger-print';

    protected static ?string $navigationLabel = 'Bitácora';

    protected static ?string $title = 'Bitácora del consultorio';

    protected static ?string $slug = 'bitacora';

    protected static string $view = 'filament.doctor.pages.bitacora-del-consultorio';

    protected static ?string $navigationGroup = 'Mi cuenta';

    protected static ?int $navigationSort = 6;

    public ?string $quien = null;

    public ?string $dia = null;

    public static function canAccess(): bool
    {
        return (bool) auth()->user()?->esDuenoDelConsultorio();
    }

    public function getViewData(): array
    {
        $equipo = User::where('clinic_id', auth()->user()->clinic_id)->orderBy('name')->pluck('name', 'id');

        $registros = Activity::with(['causer', 'subject'])
            ->where('causer_type', User::class)
            ->whereIn('causer_id', $equipo->keys())
            ->when($this->quien && $equipo->has($this->quien), fn ($q) => $q->where('causer_id', $this->quien))
            ->when($this->dia && preg_match('/^\d{4}-\d{2}-\d{2}$/', $this->dia), fn ($q) => $q->whereDate('created_at', $this->dia))
            ->latest('id')
            ->limit(200)
            ->get();

        return ['equipo' => $equipo, 'registros' => $registros];
    }

    /** De quién es lo que se tocó: el paciente, o el paciente de la nota, receta o cobro. */
    public static function deQuien(?object $sujeto): string
    {
        $paciente = $sujeto instanceof \App\Models\Patient ? $sujeto : ($sujeto->patient ?? null);

        return $paciente ? trim($paciente->first_name . ' ' . $paciente->last_name) : '';
    }
}
