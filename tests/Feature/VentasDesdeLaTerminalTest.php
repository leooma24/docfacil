<?php

namespace Tests\Feature;

use App\Models\Prospect;
use App\Models\ProspectMensaje;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

/**
 * Lo que el skill de ventas lee del CRM, sin cambiar nada.
 *
 * El skill (.claude/skills/ventas-docfacil) corre estos dos comandos en el
 * servidor para contestarle a Omar "¿cómo va mi día?" y "¿quién es este
 * prospecto y qué le digo?". Decisión de Omar (5-oct-2026): solo leer; lo
 * que se marca en el CRM lo marca él desde /ventas.
 */
class VentasDesdeLaTerminalTest extends TestCase
{
    use RefreshDatabase;

    private User $omar;

    protected function setUp(): void
    {
        parent::setUp();
        $this->travelTo(today()->setTime(10, 0));
        $this->omar = User::forceCreate(['name' => 'Omar', 'email' => 'ventas@test.com', 'password' => bcrypt('x'), 'role' => 'sales', 'email_verified_at' => now()]);
    }

    private function prospecto(array $datos = []): Prospect
    {
        static $n = 0;
        $n++;

        return Prospect::create(array_merge([
            'name' => "Dra. Prueba {$n}", 'phone' => '66812345' . str_pad((string) $n, 2, '0', STR_PAD_LEFT), 'city' => 'Los Mochis',
            'source' => 'prospecting', 'status' => 'contacted', 'has_whatsapp' => true, 'contact_day' => 1,
            'last_contact_method' => 'whatsapp', 'last_followup_at' => now()->subDays(2), 'next_contact_at' => now()->subDay(),
            'assigned_to_sales_rep_id' => $this->omar->id,
        ], $datos));
    }

    private function correr(string $comando, array $args = []): string
    {
        Artisan::call($comando, $args);

        return Artisan::output();
    }

    public function test_el_resumen_del_dia_dice_quien_contesto_y_a_quien_le_toca(): void
    {
        $this->prospecto(['name' => 'Dra. Abigail Báez', 'clinic_name' => 'Báez Clínica Dental', 'replied_at' => now()->subDays(3),
            'notes' => json_encode(['dolor' => 'los manda uno por uno', 'dijo' => 'Lo voy a revisar'])]);
        $this->prospecto(['name' => 'Dr. Luis Mora', 'specialty' => 'Ortodoncia']);

        $salida = $this->correr('docfacil:ventas-hoy', ['--rep' => $this->omar->id]);

        $this->assertStringContainsString('Contestaron', $salida);
        $this->assertStringContainsString('Abigail', $salida);
        $this->assertStringContainsString('Lo voy a revisar', $salida);
        $this->assertStringContainsString('Seguimiento de hoy', $salida);
        $this->assertStringContainsString('Luis', $salida);
        $this->assertStringContainsString('Mensualidades de brackets', $salida);
        $this->assertStringContainsString('Cómo van los mensajes', $salida);
    }

    public function test_el_resumen_encuentra_solo_al_vendedor_cuando_hay_uno(): void
    {
        $this->prospecto(['name' => 'Dra. Única']);

        $this->assertStringContainsString('Única', $this->correr('docfacil:ventas-hoy'));
    }

    public function test_la_ficha_del_prospecto_trae_lo_que_dijo_y_el_siguiente_paso(): void
    {
        $p = $this->prospecto(['name' => 'Dra. Abigail Báez', 'clinic_name' => 'Báez Clínica Dental', 'replied_at' => now()->subDay(),
            'notes' => json_encode(['dolor' => 'los manda uno por uno', 'dijo' => 'Lo voy a revisar'])]);
        ProspectMensaje::create(['prospect_id' => $p->id, 'user_id' => $this->omar->id, 'paso' => 0, 'version' => 'p0-corta', 'enviado_at' => now()->subDays(3)]);

        $salida = $this->correr('docfacil:ventas-prospecto', ['buscar' => 'abigail']);

        $this->assertStringContainsString('Báez Clínica Dental', $salida);
        $this->assertStringContainsString('los manda uno por uno', $salida);
        $this->assertStringContainsString('Primer mensaje corto', $salida);
        $this->assertStringContainsString('Siguiente paso', $salida);
        $this->assertStringContainsString('Pídele la cita', $salida);
    }

    public function test_si_hay_varios_parecidos_los_lista_y_no_escoge(): void
    {
        $this->prospecto(['name' => 'Dr. Carlos Ruiz']);
        $this->prospecto(['name' => 'Dr. Carlos Peña']);

        $salida = $this->correr('docfacil:ventas-prospecto', ['buscar' => 'carlos']);

        $this->assertStringContainsString('Hay 2', $salida);
        $this->assertStringContainsString('Ruiz', $salida);
        $this->assertStringContainsString('Peña', $salida);
    }

    public function test_ninguno_de_los_dos_cambia_nada(): void
    {
        $p = $this->prospecto(['replied_at' => now()->subDay()]);
        $antes = $p->fresh()->updated_at;

        $this->travel(5)->minutes();
        $this->correr('docfacil:ventas-hoy', ['--rep' => $this->omar->id]);
        $this->correr('docfacil:ventas-prospecto', ['buscar' => $p->name]);

        $this->assertEquals($antes, $p->fresh()->updated_at);
        $this->assertSame(0, ProspectMensaje::count());
    }
}
