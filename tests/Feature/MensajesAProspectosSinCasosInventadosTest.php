<?php

namespace Tests\Feature;

use Tests\TestCase;

/**
 * El seguimiento a prospectos contaba el caso de "un dentista aquí en
 * Culiacán" que bajó sus faltas de 6-8 a 1-2 y recupera ~$8,000 al mes. Ese
 * dentista no existe: DocFácil todavía no tiene clientes. Lo que sale solo
 * hacia prospectos (el comando y sus correos) no cuenta casos inventados.
 */
class MensajesAProspectosSinCasosInventadosTest extends TestCase
{
    private const INVENTADOS = ['Hay un dentista', 'aquí en Culiacán', 'recupera ~$8,000', 'bajaron a 1-2'];

    public function test_lo_que_sale_solo_a_prospectos_no_cuenta_casos_inventados(): void
    {
        $archivos = [
            app_path('Console/Commands/SendProspectEmails.php'),
            resource_path('views/emails/prospect-beta-invite.blade.php'),
            resource_path('views/emails/prospect-followup.blade.php'),
            resource_path('views/emails/prospect-last-chance.blade.php'),
        ];

        foreach ($archivos as $archivo) {
            $texto = file_get_contents($archivo);
            foreach (self::INVENTADOS as $frase) {
                $this->assertStringNotContainsString($frase, $texto, basename($archivo));
            }
        }
    }
}
