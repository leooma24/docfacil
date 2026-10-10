<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Las 40 páginas /software-dental/{ciudad} se retiraron (12-oct-2026): en dos
 * semanas no trajeron a ningún dentista (todas las visitas eran robots y
 * ninguna venía de Google), y 40 copias del mismo texto cambiando la ciudad
 * es lo que Google llama páginas puerta. Las ligas viejas llevan al inicio.
 *
 * Y el brief comercial queda a la mano: en el pie de la página de inicio y en
 * la Guía de Venta del panel de ventas.
 */
class SinPaginasPorCiudadTest extends TestCase
{
    use RefreshDatabase;

    public function test_la_liga_vieja_de_una_ciudad_lleva_al_inicio(): void
    {
        $this->get('/software-dental/culiacan')->assertStatus(301)->assertRedirect(route('landing.home'));
        $this->get('/software-dental/ciudad-que-nunca-existio')->assertStatus(301);
    }

    public function test_el_mapa_del_sitio_ya_no_las_trae(): void
    {
        $this->assertStringNotContainsString('/software-dental/', $this->get('/sitemap.xml')->getContent());
    }

    public function test_el_inicio_ya_no_las_liga_y_si_liga_el_brief(): void
    {
        $this->get('/')->assertOk()
            ->assertDontSee('/software-dental/', false)
            ->assertSee(route('brief.web'), false)
            ->assertSee(route('brief.pdf'), false);
    }

    public function test_la_guia_de_venta_trae_los_materiales_para_mandar(): void
    {
        $vendedor = User::forceCreate(['name' => 'Vendedor', 'email' => 'v@test.com', 'password' => bcrypt('x'), 'role' => 'sales',
            'is_active_sales_rep' => true, 'email_verified_at' => now()]);

        $this->actingAs($vendedor)->get('/ventas/guia-venta')->assertOk()
            ->assertSee('Para mandar')
            ->assertSee(route('brief.web'), false)
            ->assertSee(route('brief.pdf'), false)
            ->assertSee(route('brochure.web'), false)
            ->assertSee('demo@docfacil.com');
    }
}
