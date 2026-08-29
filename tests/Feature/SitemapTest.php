<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * El sitemap es la puerta por donde Google descubre las 40 páginas de ciudad
 * y las 6 de comparativa. Si se rompe en silencio, el SEO se apaga sin que
 * nadie lo note hasta meses después.
 */
class SitemapTest extends TestCase
{
    // El sitemap incluye los articulos del blog, que ahora salen de la base.
    use RefreshDatabase;

    private function sitemap(): string
    {
        return $this->get('/sitemap.xml')->assertSuccessful()->getContent();
    }

    public function test_el_sitemap_responde_como_xml(): void
    {
        $this->get('/sitemap.xml')
            ->assertSuccessful()
            ->assertHeader('Content-Type', 'application/xml');
    }

    public function test_incluye_la_landing_y_las_paginas_que_venden(): void
    {
        $xml = $this->sitemap();

        foreach (['/dentistas', '/doctor/register', '/blog', '/herramientas/calculadora-consultorio'] as $ruta) {
            $this->assertStringContainsString($ruta, $xml, "Falta {$ruta} en el sitemap.");
        }
    }

    public function test_incluye_las_ciudades_y_las_comparativas(): void
    {
        $xml = $this->sitemap();

        $ciudades = substr_count($xml, '/software-dental/');
        $this->assertGreaterThanOrEqual(30, $ciudades, "Solo {$ciudades} ciudades en el sitemap.");

        foreach (['dentalink', 'doctorum', 'eaglesoft'] as $competidor) {
            $this->assertStringContainsString("/vs/{$competidor}", $xml);
            $this->assertStringContainsString("/alternativas-a-{$competidor}", $xml);
        }
    }

    public function test_no_publica_pantallas_privadas(): void
    {
        $xml = $this->sitemap();

        // El login no aporta nada en busqueda; los paneles son privados.
        foreach (['/doctor/login', '/admin', '/ventas', '/paciente/login'] as $privada) {
            $this->assertStringNotContainsString("<loc>" . url($privada) . "</loc>", $xml);
        }
    }

    public function test_las_comparativas_estan_enlazadas_desde_la_landing(): void
    {
        // Estaban huérfanas: en el sitemap pero sin un solo enlace interno,
        // que es lo que hace que Google las rastree seguido.
        $html = $this->get('/dentistas')->assertSuccessful()->getContent();

        foreach (['dentalink', 'doctorum', 'eaglesoft'] as $competidor) {
            $this->assertStringContainsString("/vs/{$competidor}", $html);
            $this->assertStringContainsString("/alternativas-a-{$competidor}", $html);
        }
    }
}
