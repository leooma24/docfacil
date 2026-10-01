<?php

namespace Tests\Feature;

use App\Http\Controllers\BrochureController;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * DocFácil todavía no tiene clientes que hayan dado su testimonio. El folleto
 * traía una "Dra. María Fernández" con su caso (30% → 8% de inasistencia) y
 * otros dos doctores que no existen: publicidad engañosa, y la primera vez
 * que un prospecto pide el contacto se cae la venta. Los testimonios reales
 * llegan por TestimonioFundadorWidget, con permiso del doctor.
 */
class BrochureSinTestimoniosInventadosTest extends TestCase
{
    use RefreshDatabase;

    private const INVENTADOS = ['Fernández', 'Carlos Mendoza', 'Ana Torres', 'Caso de éxito', 'Lo que dicen'];

    public function test_el_folleto_web_no_trae_testimonios_inventados(): void
    {
        $html = $this->get('/brochure')->assertOk()->getContent();

        foreach (self::INVENTADOS as $texto) {
            $this->assertStringNotContainsString($texto, $html);
        }
    }

    public function test_el_folleto_pdf_no_trae_testimonios_inventados(): void
    {
        $datos = (new \ReflectionMethod(BrochureController::class, 'viewData'))
            ->invoke(new BrochureController(), 'pdf');
        $html = view('pdf.brochure', $datos)->render();

        foreach (self::INVENTADOS as $texto) {
            $this->assertStringNotContainsString($texto, $html);
        }
    }

    public function test_el_folleto_pdf_se_sigue_generando(): void
    {
        $this->get('/brochure.pdf?view=1')
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');
    }
}
