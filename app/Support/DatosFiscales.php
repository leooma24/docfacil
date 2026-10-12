<?php

namespace App\Support;

/**
 * Los catálogos del SAT que necesita el contador para facturarle a un
 * paciente (11-oct-2026). Solo los que le tocan a un consultorio dental.
 */
class DatosFiscales
{
    /** RFC de persona física (13) o moral (12). */
    public const RFC = '/^[A-ZÑ&]{3,4}\d{6}[A-Z0-9]{3}$/u';

    public const REGIMENES = [
        '605' => '605 · Sueldos y salarios',
        '612' => '612 · Actividades empresariales y profesionales',
        '626' => '626 · Régimen Simplificado de Confianza (RESICO)',
        '606' => '606 · Arrendamiento',
        '621' => '621 · Incorporación fiscal',
        '625' => '625 · Plataformas tecnológicas',
        '616' => '616 · Sin obligaciones fiscales',
        '601' => '601 · General de Ley Personas Morales',
        '603' => '603 · Personas morales con fines no lucrativos',
    ];

    public const USOS = [
        'D01' => 'D01 · Honorarios médicos, dentales y gastos hospitalarios',
        'G03' => 'G03 · Gastos en general',
        'S01' => 'S01 · Sin efectos fiscales',
    ];
}
