<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * La página de inicio se recorre como la serie de limas: un paso por color,
 * de la agenda a Omar. Cada paso enseña algo que el sistema ya hace, con el
 * texto que el sistema usa de verdad.
 */
class LandingRecorridoTest extends TestCase
{
    use RefreshDatabase;

    private const PASOS = ['agenda', 'recordatorio', 'consulta', 'odontograma', 'mensualidades', 'omar'];

    private function html(): string
    {
        return $this->get('/')->assertOk()->getContent();
    }

    public function test_los_seis_pasos_en_orden(): void
    {
        $html = $this->html();

        $posiciones = array_map(fn ($p) => strpos($html, 'data-paso="' . $p . '"'), self::PASOS);
        $this->assertNotContains(false, $posiciones, 'Falta un paso del recorrido');
        $this->assertSame($posiciones, array_values(collect($posiciones)->sort()->all()));
    }

    public function test_el_riel_lleva_a_cada_paso(): void
    {
        $html = $this->html();

        foreach (self::PASOS as $p) {
            $this->assertStringContainsString('href="#paso-' . $p . '"', $html);
            $this->assertStringContainsString('id="paso-' . $p . '"', $html);
        }
    }

    /**
     * El recordatorio de ejemplo es el mismo mensaje que arma
     * RecordatorioDeCita, y se abre en el WhatsApp del visitante: así se
     * ve que lo manda él, no DocFácil.
     */
    public function test_el_recordatorio_de_prueba_es_el_mensaje_real(): void
    {
        $html = $this->html();

        $this->assertStringContainsString('le recordamos su cita en', $html);
        $this->assertStringContainsString('Confirme o cancele aquí', $html);
        $this->assertStringContainsString('¡Le esperamos!', $html);
        $this->assertStringContainsString('type="tel"', $html);
        $this->assertStringContainsString('https://wa.me/', $html);
        $this->assertStringContainsString('usted da enviar', $html);
    }

    public function test_el_odontograma_es_el_del_sistema(): void
    {
        $this->assertStringContainsString('odo-arcadas', $this->html());
    }

    public function test_los_datos_de_ejemplo_se_dicen_de_ejemplo(): void
    {
        $this->assertGreaterThanOrEqual(3, substr_count($this->html(), 'Datos de ejemplo'));
    }

    public function test_omar_en_grande_y_liviano(): void
    {
        $foto = public_path('images/landing/omar.jpg');
        $this->assertFileExists($foto);
        $this->assertLessThan(200 * 1024, filesize($foto));
        $this->assertStringContainsString('images/landing/omar.jpg', $this->html());
    }

    /**
     * El que no quiere registrarse todavía entra al demo desde el inicio y
     * desde la barra (también la del celular).
     */
    public function test_el_demo_esta_a_la_mano(): void
    {
        $html = $this->html();
        $demo = 'href="' . route('demo') . '"';

        $this->assertGreaterThanOrEqual(3, substr_count($html, $demo), 'Falta la liga al demo en la barra, el menú del celular o el inicio');
        $this->assertStringContainsString('data-track-location="hero_demo"', $html);
    }

    /**
     * Lo que la página enseña se llama como en el sistema: los estados de la
     * cita, el botón "Recordado", el estado de cada mensualidad. Y no promete
     * lo que el sistema no hace (la lista de espera anota a quién se ofreció
     * el hueco, no lo aparta) ni tiempos que nadie midió.
     */
    public function test_la_pagina_habla_como_el_sistema(): void
    {
        $html = $this->html();

        foreach (['Programada', 'Recordado', 'Confirmada', 'Pagada', 'Vencida', 'Por pagar'] as $etiqueta) {
            $this->assertStringContainsString($etiqueta, $html, "La página no dice \"{$etiqueta}\" como el sistema");
        }
        foreach (['Por confirmar', 'Confirmó', 'Por venir', 'apartado el hueco', 'dos minutos'] as $frase) {
            $this->assertStringNotContainsString($frase, $html, "La página dice \"{$frase}\"");
        }
    }

    public function test_sin_rayas_largas_en_el_texto(): void
    {
        $html = $this->html();
        $texto = html_entity_decode(strip_tags(preg_replace(['/<script\b.*?<\/script>/is', '/<style\b.*?<\/style>/is'], '', $html)));

        $this->assertStringNotContainsString('—', $texto);
        $this->assertStringNotContainsString('–', $texto);
    }
}
