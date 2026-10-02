<?php

namespace Tests\Feature;

use Tests\TestCase;

/**
 * La página prometía "Garantía de 30 días, te devolvemos tu dinero" y los
 * términos no decían nada: una promesa que no estaba por escrito. Ahora la
 * garantía vive en los términos, con sus reglas, y la página enlaza a ella.
 */
class GarantiaEnTerminosTest extends TestCase
{
    public function test_los_terminos_traen_la_garantia_con_sus_reglas(): void
    {
        $this->get('/terminos')
            ->assertOk()
            ->assertSee('Garantía de 30 días')
            ->assertSee('primer pago')
            ->assertSee('10 días hábiles');
    }

    public function test_la_pagina_promete_lo_mismo_y_enlaza_a_los_terminos(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('Garantía de 30 días')
            ->assertSee('/terminos#garantia', false);
    }
}
