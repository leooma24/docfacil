<?php

namespace Tests\Feature;

use App\Console\Commands\SendProspectEmails;
use Tests\TestCase;

/**
 * El seguimiento por WhatsApp a prospectos contaba el caso de "un dentista
 * aquí en Culiacán" que bajó sus faltas de 6-8 a 1-2 y recupera ~$8,000 al
 * mes. Ese dentista no existe: DocFácil todavía no tiene clientes. El cron
 * lo mandaba cada hora a prospectos reales.
 */
class MensajesAProspectosSinCasosInventadosTest extends TestCase
{
    public function test_ningun_mensaje_a_prospectos_cuenta_un_caso_inventado(): void
    {
        $mensajes = (new \ReflectionClassConstant(SendProspectEmails::class, 'WA_MESSAGES'))->getValue();

        foreach ($mensajes as $tipo => $texto) {
            $this->assertStringNotContainsString('Culiacán', $texto, $tipo);
            $this->assertStringNotContainsString('Hay un dentista', $texto, $tipo);
            $this->assertStringNotContainsString('recupera ~$8,000', $texto, $tipo);
        }
    }

    public function test_el_seguimiento_no_promete_lista_de_espera_automatica(): void
    {
        $mensajes = (new \ReflectionClassConstant(SendProspectEmails::class, 'WA_MESSAGES'))->getValue();

        $this->assertStringNotContainsString('automáticamente', $mensajes['prospect_followup']);
    }
}
