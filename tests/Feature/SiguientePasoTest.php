<?php

namespace Tests\Feature;

use App\Models\Prospect;
use App\Support\SiguientePaso;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Una cosa a la vez, y nunca sin próximo paso.
 *
 * El CRM ya decía a quién escribirle. Lo que faltaba es qué hacer con cada
 * quien, y ahí estaba la fuga: de tres que contestaron, uno agendó demo
 * —porque se le pidió la cita— y a los otros dos la conversación se les quedó
 * a medias. Contestar no es diagnosticar, diagnosticar no es agendar, y
 * agendar no es cerrar.
 *
 * Cada prospecto tiene una sola acción que le toca ahora. Ni dos ni ninguna.
 */
class SiguientePasoTest extends TestCase
{
    use RefreshDatabase;

    private function prospecto(array $datos = []): Prospect
    {
        return Prospect::create(array_merge([
            'name' => 'Dra. Karla Ramírez',
            'phone' => '6681110356',
            'city' => 'Los Mochis',
            'specialty' => 'Odontopediatría',
            'source' => 'prospecting',
            'status' => 'new',
            'has_whatsapp' => true,
        ], $datos));
    }

    // ── Las etapas, en orden ─────────────────────────────────────

    public function test_al_que_no_ha_contestado_le_toca_la_cadencia(): void
    {
        $paso = SiguientePaso::para($this->prospecto([
            'status' => 'contacted',
            'contact_day' => 1,
            'last_contact_method' => 'whatsapp',
        ]));

        $this->assertSame('esperando', $paso['etapa']);
    }

    public function test_al_que_contesto_le_toca_la_segunda_pregunta(): void
    {
        $paso = SiguientePaso::para($this->prospecto([
            'status' => 'contacted',
            'contact_day' => 1,
            'replied_at' => now()->subHour(),
        ]));

        $this->assertSame('diagnosticar', $paso['etapa']);
        $this->assertStringContainsString('pregunta', strtolower($paso['que']));
        $this->assertNotNull($paso['mensaje']);
    }

    public function test_al_que_ya_dijo_su_dolor_le_toca_pedirle_la_cita(): void
    {
        $paso = SiguientePaso::para($this->prospecto([
            'status' => 'contacted',
            'contact_day' => 1,
            'replied_at' => now()->subHour(),
            'notes' => '{"dolor":"a_mano_whatsapp"}',
        ]));

        $this->assertSame('pedir_cita', $paso['etapa']);
        $this->assertStringContainsString('cita', strtolower($paso['que']));
    }

    public function test_con_la_demo_agendada_le_toca_prepararla(): void
    {
        $paso = SiguientePaso::para($this->prospecto([
            'status' => 'interested',
            'replied_at' => now()->subDay(),
            'notes' => '{"dolor":"a_mano_whatsapp"}',
            'demo_scheduled_at' => now()->addDays(2),
        ]));

        $this->assertSame('preparar_demo', $paso['etapa']);
    }

    public function test_cuando_la_demo_ya_paso_le_toca_marcar_si_se_hizo(): void
    {
        // Sin esto, demo_completed_at no se llena nunca y la tasa de cierre
        // —la única que falta por conocer— no se puede medir.
        $paso = SiguientePaso::para($this->prospecto([
            'status' => 'interested',
            'replied_at' => now()->subDays(3),
            'notes' => '{"dolor":"a_mano_whatsapp"}',
            'demo_scheduled_at' => now()->subHours(2),
        ]));

        $this->assertSame('marcar_demo', $paso['etapa']);
    }

    public function test_despues_de_la_demo_le_toca_pedir_el_cierre(): void
    {
        $paso = SiguientePaso::para($this->prospecto([
            'status' => 'interested',
            'replied_at' => now()->subDays(3),
            'demo_scheduled_at' => now()->subDay(),
            'demo_completed_at' => now()->subDay(),
        ]));

        $this->assertSame('cerrar', $paso['etapa']);
        $this->assertNotNull($paso['mensaje']);
    }

    public function test_al_cliente_le_toca_pedirle_referido(): void
    {
        $paso = SiguientePaso::para($this->prospecto([
            'status' => 'converted',
            'demo_completed_at' => now()->subWeek(),
        ]));

        $this->assertSame('referido', $paso['etapa']);
    }

    public function test_al_que_dijo_que_no_ya_no_le_toca_nada(): void
    {
        $paso = SiguientePaso::para($this->prospecto([
            'status' => 'lost',
            'replied_at' => now()->subDay(),
        ]));

        $this->assertSame('cerrado', $paso['etapa']);
        $this->assertNull($paso['mensaje']);
    }

    // ── El mensaje de cierre ─────────────────────────────────────

    public function test_el_cierre_lleva_la_oferta_publicada_y_pide_algo_a_cambio(): void
    {
        $mensaje = urldecode(SiguientePaso::para($this->prospecto([
            'status' => 'interested',
            'demo_completed_at' => now()->subHours(2),
        ]))['mensaje']);

        // Lo que promete la landing, leído de config/founders.php.
        $this->assertStringContainsString((string) config('founders.free_months'), $mensaje);
        $this->assertStringContainsString(number_format((float) config('founders.monthly_price')), $mensaje);
        // Y la parte que faltaba: el compromiso se pide, no se cobra.
        $this->assertStringContainsString('retroalimentación', $mensaje);
    }

    public function test_el_cierre_no_habla_de_lugares_cuando_no_hay_fundadores(): void
    {
        // "Quedan 10 de 10" no crea urgencia: suena a guion.
        $mensaje = urldecode(SiguientePaso::para($this->prospecto([
            'status' => 'interested',
            'demo_completed_at' => now(),
        ]))['mensaje']);

        $this->assertStringNotContainsString('lugares', strtolower($mensaje));
    }

    public function test_al_consultorio_de_un_doctor_se_le_cierra_con_dos_fechas(): void
    {
        $mensaje = urldecode(SiguientePaso::para($this->prospecto([
            'status' => 'interested',
            'demo_completed_at' => now(),
        ]))['mensaje']);

        $this->assertStringContainsString('hoy', strtolower($mensaje));
        $this->assertStringNotContainsString('999', $mensaje);
    }

    public function test_a_la_clinica_con_varios_doctores_se_le_cierra_con_los_dos_planes(): void
    {
        $mensaje = urldecode(SiguientePaso::para($this->prospecto([
            'name' => 'Dentina',
            'clinic_name' => 'Dentina',
            'status' => 'interested',
            'demo_completed_at' => now(),
            'notes' => '{"doctores":3}',
        ]))['mensaje']);

        $this->assertStringContainsString(number_format(\App\Models\Commission::monthlyPriceForPlan('profesional')), $mensaje);
    }
}
