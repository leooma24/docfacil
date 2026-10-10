<?php

namespace App\Support;

use App\Models\Appointment;
use App\Models\MedicalRecord;

/**
 * La nota en 30 segundos: botones con lo de siempre en vez de escribir
 * párrafos (entrevista del 10-oct-2026: "anotar en 30 s con botones").
 */
class NotaRapida
{
    /** Lo que más se anota en una consulta dental de control. */
    public const FRASES = [
        'Sin dolor',
        'Sin novedad',
        'Higiene buena',
        'Higiene regular, se reforzó la técnica de cepillado',
        'Profilaxis',
        'Ajuste de brackets y cambio de ligas',
        'Ajuste de oclusión',
        'Se tomó radiografía',
        'Se dieron indicaciones por escrito',
    ];

    /** Agrega la frase al final de la nota, sin repetirla. */
    public static function agregar(?string $nota, string $frase): string
    {
        $nota = trim((string) $nota);
        $frase = trim($frase, " .");

        if ($frase === '' || preg_match('/(^|\.\s*)' . preg_quote($frase, '/') . '\./u', $nota . '.')) {
            return $nota;
        }

        return ltrim(($nota === '' ? '' : rtrim($nota, '. ') . '. ') . $frase . '.');
    }

    /** Lo que se le hizo la vez pasada, para "Igual que la vez pasada". */
    public static function laVezPasada(Appointment $cita): ?string
    {
        $nota = MedicalRecord::withoutGlobalScopes()
            ->where('clinic_id', $cita->clinic_id)
            ->where('patient_id', $cita->patient_id)
            ->where(fn ($q) => $q->whereNull('appointment_id')->orWhere('appointment_id', '!=', $cita->id))
            ->where(fn ($q) => $q->whereNotNull('treatment')->where('treatment', '!=', '')->orWhere(fn ($q) => $q->whereNotNull('notes')->where('notes', '!=', '')))
            ->orderByDesc('visit_date')->orderByDesc('id')
            ->first();

        return $nota ? (trim((string) ($nota->treatment ?: $nota->notes)) ?: null) : null;
    }
}
