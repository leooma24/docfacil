<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * La página prometía "Recupera $8,000 al mes", "1 de cada 3 pacientes no
 * llega", "Triplica reseñas" y una tabla de "$15,700 perdidos": cifras sin
 * fuente, en un producto que todavía no tiene clientes. Tampoco se ofrece
 * factura CFDI: el consultorio que va empezando no factura.
 */
class LandingSinCifrasInventadasTest extends TestCase
{
    use RefreshDatabase;

    private const PROHIBIDO = [
        '$8,000 al mes', '1 de cada 3', 'hasta 70%', '$15,700', 'Triplica', 'Sube 20%', '$10-30k',
        'Próximamente', 'CFDI', 'Caso de éxito', 'Lo que dicen', 'Tu competencia ya se digitalizó',
        'recordatorios automáticos',
    ];

    public function test_la_pagina_no_promete_cifras_sin_fuente(): void
    {
        foreach (['/', '/dentistas'] as $url) {
            $html = $this->get($url)->assertOk()->getContent();
            foreach (self::PROHIBIDO as $frase) {
                $this->assertStringNotContainsStringIgnoringCase($frase, $html, "{$url} dice \"{$frase}\"");
            }
        }
    }

    public function test_el_precio_de_presupuestos_sale_de_la_configuracion(): void
    {
        config(['addons.treatment_plans.monthly_price' => 150]);

        $this->get('/')->assertSee('$150 al mes aparte', false);
    }
}
