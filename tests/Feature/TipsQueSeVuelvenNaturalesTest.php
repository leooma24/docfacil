<?php

namespace Tests\Feature;

use App\Models\TipDeVenta;
use App\Models\User;
use App\Support\TipsDeVenta;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Un tip a la vez, hasta que salga sin pensarlo.
 *
 * Omar lo pidió así: no quiere una lista de veinticinco consejos —esas se leen
 * una vez y no se vuelven a abrir— sino que el mismo le aparezca pegado al
 * botón, justo antes de hacer la cosa, hasta que le salga natural.
 *
 * De ahí las tres reglas que se prueban aquí: sale el de la etapa en la que
 * está, rota para que no sea siempre el mismo, y el que ya domina deja de
 * estorbar pero regresa a repaso, porque lo que no se practica se olvida.
 */
class TipsQueSeVuelvenNaturalesTest extends TestCase
{
    use RefreshDatabase;

    private User $vendedor;

    protected function setUp(): void
    {
        parent::setUp();

        $this->vendedor = User::forceCreate([
            'name' => 'Omar',
            'email' => 'ventas@test.com',
            'password' => bcrypt('password'),
            'role' => 'sales',
            'email_verified_at' => now(),
        ]);
    }

    // ── El que toca ──────────────────────────────────────────────

    public function test_sale_un_tip_de_la_etapa_en_la_que_esta(): void
    {
        $tip = TipDeVenta::paraMostrar($this->vendedor->id, 'cerrar');

        $this->assertNotNull($tip);
        $this->assertSame('cerrar', $tip['etapa']);
    }

    public function test_una_etapa_sin_tips_no_inventa_ninguno(): void
    {
        $this->assertNull(TipDeVenta::paraMostrar($this->vendedor->id, 'etapa_que_no_existe'));
    }

    public function test_rota_para_que_no_salga_siempre_el_mismo(): void
    {
        $primero = TipDeVenta::paraMostrar($this->vendedor->id, 'cerrar');
        TipDeVenta::anotarQueSeVio($this->vendedor->id, $primero['clave']);

        $segundo = TipDeVenta::paraMostrar($this->vendedor->id, 'cerrar');

        $this->assertNotSame($primero['clave'], $segundo['clave']);
    }

    // ── La cuenta ────────────────────────────────────────────────

    public function test_lleva_la_cuenta_de_cuantas_veces_lo_has_visto(): void
    {
        TipDeVenta::anotarQueSeVio($this->vendedor->id, 'silencio');

        $this->assertSame(1, TipDeVenta::where('clave', 'silencio')->first()->veces_visto);
    }

    public function test_recargar_la_pantalla_no_cuenta_como_practicar(): void
    {
        TipDeVenta::anotarQueSeVio($this->vendedor->id, 'silencio');
        TipDeVenta::anotarQueSeVio($this->vendedor->id, 'silencio');
        TipDeVenta::anotarQueSeVio($this->vendedor->id, 'silencio');

        $this->assertSame(1, TipDeVenta::where('clave', 'silencio')->first()->veces_visto);
    }

    public function test_al_dia_siguiente_si_vuelve_a_contar(): void
    {
        TipDeVenta::anotarQueSeVio($this->vendedor->id, 'silencio');

        $this->travel(1)->days();
        TipDeVenta::anotarQueSeVio($this->vendedor->id, 'silencio');

        $this->assertSame(2, TipDeVenta::where('clave', 'silencio')->first()->veces_visto);
    }

    // ── Ya me sale solo ──────────────────────────────────────────

    public function test_el_dominado_deja_de_aparecer(): void
    {
        foreach (TipsDeVenta::paraLaEtapa('cerrar') as $tip) {
            TipDeVenta::marcarDominado($this->vendedor->id, $tip['clave']);
        }

        $this->assertNull(TipDeVenta::paraMostrar($this->vendedor->id, 'cerrar'));
    }

    public function test_el_dominado_vuelve_a_repaso_a_los_quince_dias(): void
    {
        // Lo que no se practica se olvida: vuelve una vez, a comprobar.
        foreach (TipsDeVenta::paraLaEtapa('cerrar') as $tip) {
            TipDeVenta::marcarDominado($this->vendedor->id, $tip['clave']);
        }

        $this->travel(TipDeVenta::DIAS_PARA_REPASO + 1)->days();

        $tip = TipDeVenta::paraMostrar($this->vendedor->id, 'cerrar');

        $this->assertNotNull($tip);
        $this->assertTrue($tip['es_repaso']);
    }

    public function test_cada_quien_lleva_su_propio_avance(): void
    {
        $otro = User::forceCreate([
            'name' => 'Otro vendedor',
            'email' => 'otro@test.com',
            'password' => bcrypt('password'),
            'role' => 'sales',
            'email_verified_at' => now(),
        ]);

        TipDeVenta::marcarDominado($this->vendedor->id, 'silencio');

        $this->assertNull(TipDeVenta::where('user_id', $otro->id)->first());
        $this->assertNotNull(TipDeVenta::paraMostrar($otro->id, 'cerrar'));
    }

    // ── El catálogo ──────────────────────────────────────────────

    public function test_ningun_tip_trae_cifras_que_no_podamos_sostener(): void
    {
        $todo = collect(TipsDeVenta::CATALOGO)->map(fn ($t) => $t['tip'] . ' ' . $t['porque'])->implode(' ');

        foreach (['2 o 3 citas', '$6,000', '30%', '80%', '16x'] as $invento) {
            $this->assertStringNotContainsString($invento, $todo);
        }
    }

    public function test_ningun_tip_contradice_lo_que_ya_medimos(): void
    {
        $todo = strtolower(collect(TipsDeVenta::CATALOGO)->map(fn ($t) => $t['tip'] . ' ' . $t['porque'])->implode(' '));

        // La línea de salida —"si no le interesa me lo dice y no lo molesto
        // más"— es justo la que hizo que contestaran. Un tip que diga lo
        // contrario no entra.
        $this->assertStringNotContainsString('no des salida', $todo);
    }
}
