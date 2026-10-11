<?php

namespace Tests\Feature;

use Tests\TestCase;

/**
 * Meta pide la política de privacidad para publicar la app de WhatsApp
 * (10-oct-2026). Desde los recordatorios automáticos, DocFácil sí manda
 * mensajes por la API de WhatsApp cuando el consultorio lo prende: la
 * página no puede seguir diciendo que nunca los envía.
 */
class PrivacidadDiceLoDeMetaTest extends TestCase
{
    public function test_nombra_a_meta_como_proveedor_y_no_dice_que_nunca_manda(): void
    {
        $this->get('/privacidad')->assertOk()
            ->assertSee('Meta Platforms')
            ->assertSee('Confirmo')
            ->assertDontSee('DocFácil no los envía');
    }
}
