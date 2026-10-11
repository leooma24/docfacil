<?php

namespace App\Filament\Doctor\Concerns;

use App\Models\Patient;
use App\Support\AlertasClinicas;
use Filament\Forms;
use Illuminate\Support\HtmlString;

/**
 * Las alertas médicas del paciente en cualquier formulario donde se elige
 * al paciente (día 10 del plan, 10-oct-2026): consentimientos, presupuestos.
 * Ya salían en la consulta, las recetas y las citas.
 */
class AvisoDeAlertas
{
    public static function campo(string $campoPaciente = 'patient_id'): Forms\Components\Placeholder
    {
        $etiquetas = fn (Forms\Get $get) => AlertasClinicas::etiquetas(
            Patient::where('clinic_id', auth()->user()->clinic_id)->find($get($campoPaciente)),
            5,
        );

        return Forms\Components\Placeholder::make('alertas_del_paciente')
            ->hiddenLabel()
            ->columnSpanFull()
            ->visible(fn (Forms\Get $get) => $etiquetas($get) !== [])
            ->content(fn (Forms\Get $get) => new HtmlString(self::chips($etiquetas($get))));
    }

    /** @param list<string> $etiquetas */
    public static function chips(array $etiquetas): string
    {
        return '<div style="display:flex;flex-wrap:wrap;gap:6px;padding:8px 10px;border-radius:8px;background:#fef2f2;border-left:4px solid #ef4444;">'
            . collect($etiquetas)->map(fn ($e) => '<span style="font-size:12px;font-weight:700;color:#991b1b;">' . e($e) . '</span>')->implode('<span style="color:#fca5a5;">·</span>')
            . '</div>';
    }
}
