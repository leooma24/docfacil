<?php

namespace App\Support;

/**
 * Lo que el consultorio prende y apaga (11-oct-2026).
 *
 * Lo de todos los días (escritorio, agenda, citas, consulta, pacientes,
 * expediente, odontograma, recetas, cobros y presupuestos) siempre está. Lo
 * demás es un módulo: el dentista nuevo empieza con todos apagados y prende
 * lo que use. Romita: «nada de ruido».
 *
 * Cada módulo apaga la función de plan que lo controla (Clinic::hasFeature),
 * así se esconden juntos el menú, los avisos y los botones que dependen de
 * ella. Planes de pago no tiene función de plan: lo revisa su recurso.
 */
class Modulos
{
    /** clave => [nombre, qué es, función de plan que apaga (o null)] */
    public const LISTA = [
        'gastos' => ['Gastos y corte del mes', 'Lo que gasta el consultorio y cuánto le quedó al mes.', 'expenses'],
        'planes_de_pago' => ['Planes de pago', 'Tratamientos que el paciente paga en mensualidades.', null],
        'laboratorio' => ['Laboratorio', 'Trabajos mandados al laboratorio: qué ya llegó y cuánto se le debe.', 'laboratorio'],
        'consentimientos' => ['Consentimientos', 'Consentimientos informados que el paciente firma en pantalla.', 'consent_forms'],
        'lista_de_espera' => ['Lista de espera', 'Pacientes que quieren una cita antes, para llenar los huecos.', 'waitlist'],
        'llegada_qr' => ['Llegada con QR', 'El paciente escanea un QR en la recepción y avisa que llegó.', 'qr_checkin'],
        'inventario' => ['Inventario', 'Insumos, sus movimientos y los residuos peligrosos.', 'inventory'],
    ];

    public static function existe(string $clave): bool
    {
        return isset(self::LISTA[$clave]);
    }

    /** El módulo que controla esa función de plan, si hay uno. */
    public static function deLaFuncion(string $funcion): ?string
    {
        foreach (self::LISTA as $clave => [, , $deLaFuncion]) {
            if ($deLaFuncion === $funcion) {
                return $clave;
            }
        }

        return null;
    }
}
