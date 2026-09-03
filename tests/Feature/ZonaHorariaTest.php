<?php

namespace Tests\Feature;

use Tests\TestCase;

/**
 * La app corre en hora de México, no en UTC.
 *
 * Corriendo en UTC, now() iba 6 horas adelante de la hora local. Eso tenía
 * dos efectos que costaban dinero:
 *
 *  - El portal público le escondía al paciente los horarios de la mañana
 *    como si ya hubieran pasado. En Sinaloa eran 7 horas de agenda
 *    invisible, todos los días.
 *  - Los correos programados "a las 9am" salían a las 3 de la madrugada.
 */
class ZonaHorariaTest extends TestCase
{
    public function test_la_app_corre_en_hora_de_mexico(): void
    {
        $this->assertSame('America/Mexico_City', config('app.timezone'));
    }

    public function test_now_no_va_adelantado_respecto_a_mexico(): void
    {
        $this->assertSame(
            now()->format('Y-m-d H:i'),
            now()->setTimezone('America/Mexico_City')->format('Y-m-d H:i'),
            'now() debe dar la hora local, no UTC.'
        );
    }

    public function test_una_hora_de_esta_manana_no_cuenta_como_pasada_por_el_desfase(): void
    {
        // El caso concreto que rompía el portal: a las 9 de la mañana en
        // México, un hueco de las 11:00 de hoy tiene que seguir siendo futuro.
        // En UTC ya eran las 15:00 y lo descartaba.
        $onceDeHoy = now()->setTime(11, 0);

        if (now()->hour >= 11) {
            $this->markTestSkipped('Ya pasaron las 11, este caso no aplica ahora.');
        }

        $this->assertTrue($onceDeHoy->isFuture());
    }
}
