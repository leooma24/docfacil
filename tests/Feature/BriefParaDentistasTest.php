<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * El brief comercial (12-oct-2026): dos páginas para enseñarle a un dentista,
 * solo con lo que existe hoy, con capturas nuevas, la demo y sus accesos, y
 * un contacto real. El anterior ofrecía "Dashboard con gráficas" (ya no está),
 * "Cifrado TLS" sin comprobar y un correo contacto@ que no es buzón real.
 */
class BriefParaDentistasTest extends TestCase
{
    use RefreshDatabase;

    public function test_el_brief_dice_lo_que_resuelve_y_como_probarlo(): void
    {
        $this->get('/brief')->assertOk()
            ->assertSee('Le deben')
            ->assertSee('Lo último en cada diente')
            ->assertSee('usted da enviar')
            ->assertSee('la hace su contador')
            ->assertSee(str_replace(['https://', 'http://'], '', url('/demo')))
            ->assertSee('demo@docfacil.com')
            ->assertSee('demo2026')
            ->assertSee('668 249 3398')
            ->assertSee('images/brief/consulta.jpg', false);
    }

    public function test_no_trae_lo_que_ya_no_es_cierto(): void
    {
        $this->get('/brief')->assertOk()
            ->assertDontSee('Dashboard con gráficas')
            ->assertDontSee('contacto@docfacil.com')
            ->assertDontSee('TLS');
    }

    public function test_los_planes_salen_de_la_misma_fuente(): void
    {
        $this->get('/brief')->assertOk()
            ->assertSee('Básico')->assertSee('$499')
            ->assertSee('Pro')->assertSee('$999')
            ->assertSee(\App\Support\LoQueTraeCadaPlan::plan('basico')['limits']);
    }

    public function test_el_pdf_se_descarga(): void
    {
        $r = $this->get('/brief.pdf');

        $r->assertOk();
        $this->assertSame('application/pdf', $r->headers->get('content-type'));
    }
}
