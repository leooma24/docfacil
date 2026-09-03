<?php

namespace Tests\Feature;

use App\Models\Clinic;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

/**
 * El programa Fundador en la landing.
 *
 * Antes ahí había un reloj de "oferta de lanzamiento" que terminaba cada fin
 * de mes y volvía a empezar. El anual a 10 meses no es promoción, es el
 * precio de siempre, así que el reloj le ponía fecha a algo que nunca iba a
 * cambiar — y el dentista que volvía en octubre veía el mismo reloj
 * corriendo.
 *
 * Lo que sí es escaso son los lugares, y el número sale de contar fundadores
 * reales: no se reinicia y no puede mentir.
 */
class LugaresDeFundadorTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Cache::forget('fundadores.tomados');
        config(['founders.seats' => 10]);
    }

    private function clinica(bool $fundador = false): Clinic
    {
        return Clinic::create([
            'name' => 'Consultorio ' . uniqid(),
            'slug' => 'c-' . uniqid(),
            'plan' => 'profesional',
            'onboarding_status' => 'completed',
            'is_founder' => $fundador,
        ]);
    }

    public function test_sin_fundadores_estan_los_diez_lugares(): void
    {
        $lugares = Clinic::lugaresDeFundador();

        $this->assertSame(10, $lugares['total']);
        $this->assertSame(0, $lugares['tomados']);
        $this->assertSame(10, $lugares['quedan']);
        $this->assertTrue($lugares['hay']);
    }

    public function test_cada_fundador_ocupa_un_lugar(): void
    {
        $this->clinica(fundador: true);
        $this->clinica(fundador: true);
        Cache::forget('fundadores.tomados');

        $lugares = Clinic::lugaresDeFundador();

        $this->assertSame(2, $lugares['tomados']);
        $this->assertSame(8, $lugares['quedan']);
    }

    public function test_una_clinica_normal_no_ocupa_lugar(): void
    {
        $this->clinica();
        $this->clinica();
        Cache::forget('fundadores.tomados');

        $this->assertSame(0, Clinic::lugaresDeFundador()['tomados']);
    }

    public function test_cuando_se_llenan_ya_no_hay(): void
    {
        for ($i = 0; $i < 10; $i++) {
            $this->clinica(fundador: true);
        }
        Cache::forget('fundadores.tomados');

        $lugares = Clinic::lugaresDeFundador();

        $this->assertSame(0, $lugares['quedan']);
        $this->assertFalse($lugares['hay']);
    }

    public function test_pasarse_del_total_no_deja_lugares_negativos(): void
    {
        // Si alguien marca 12 fundadores con 10 lugares, la landing no puede
        // decir "quedan -2".
        for ($i = 0; $i < 12; $i++) {
            $this->clinica(fundador: true);
        }
        Cache::forget('fundadores.tomados');

        $lugares = Clinic::lugaresDeFundador();

        $this->assertSame(10, $lugares['tomados']);
        $this->assertSame(0, $lugares['quedan']);
        $this->assertFalse($lugares['hay']);
    }

    public function test_marcar_un_fundador_refresca_el_conteo_de_inmediato(): void
    {
        // El conteo se cachea 5 minutos. Si un fundador acaba de entrar y la
        // landing sigue diciendo el número viejo, la promesa se rompe.
        Clinic::lugaresDeFundador();

        $clinica = $this->clinica();
        $clinica->update(['is_founder' => true]);

        $this->assertSame(1, Clinic::lugaresDeFundador()['tomados']);
    }

    // ── Lo que ve el dentista ────────────────────────────────────

    public function test_la_landing_ofrece_los_lugares_cuando_hay(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('Programa Fundador')
            ->assertSee('Quiero un lugar', false);
    }

    public function test_sin_fundadores_no_se_anuncia_que_esta_vacio(): void
    {
        // "Quedan 10 de 10" le dice al dentista que nadie ha entrado, que es
        // lo contrario de lo que queremos que sienta.
        $this->get('/')
            ->assertOk()
            ->assertSee('Busco los primeros 10 consultorios')
            ->assertDontSee('Quedan 10 de 10');
    }

    public function test_con_lugares_tomados_si_se_enseña_el_conteo(): void
    {
        $this->clinica(fundador: true);
        $this->clinica(fundador: true);
        Cache::forget('fundadores.tomados');

        $this->get('/')
            ->assertOk()
            ->assertSee('Quedan 8 de 10 lugares');
    }

    public function test_llenos_los_lugares_la_landing_deja_de_ofrecerlos(): void
    {
        for ($i = 0; $i < 10; $i++) {
            $this->clinica(fundador: true);
        }
        Cache::forget('fundadores.tomados');

        $this->get('/')
            ->assertOk()
            ->assertDontSee('Programa Fundador')
            ->assertSee('Paga anual y ahorra 2 meses');
    }

    public function test_la_landing_ya_no_tiene_el_reloj_que_se_reiniciaba(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertDontSee('launchCountdown', false)
            ->assertDontSee('Oferta de lanzamiento');
    }

    public function test_el_precio_de_fundador_sale_de_la_configuracion(): void
    {
        // Para que no se desincronice del que pone el panel de admin al
        // marcar la clínica como fundadora.
        $this->assertSame(499.0, (float) config('founders.monthly_price'));
        $this->assertSame(6, (int) config('founders.free_months'));

        $this->get('/')->assertSee('$499/mes de por vida', false);
    }
}
