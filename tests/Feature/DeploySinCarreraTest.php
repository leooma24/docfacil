<?php

namespace Tests\Feature;

use Tests\TestCase;

/**
 * Subir a producción sin tirar páginas a medio deploy.
 *
 * El 1-oct-2026 a las 17:44 un doctor vio un error: "File does not exist at
 * path storage/framework/views/…". El deploy corría optimize:clear, que borra
 * las vistas compiladas mientras el sitio sigue atendiendo; la página que se
 * pidió en ese instante buscó su vista y ya no estaba. (view:cache tampoco
 * sirve: también borra antes de compilar.)
 *
 * No hace falta borrarlas: Blade vuelve a compilar sola la vista cuyo archivo
 * cambió (compara las fechas). Y el caché de Filament se sobrescribe sin
 * borrarse. deploy.sh es la única forma de subir.
 */
class DeploySinCarreraTest extends TestCase
{
    private function script(): string
    {
        $ruta = base_path('deploy.sh');
        $this->assertFileExists($ruta);

        // Sin los comentarios: lo que cuenta es lo que corre.
        return implode("\n", array_filter(
            explode("\n", file_get_contents($ruta)),
            fn ($linea) => ! str_starts_with(ltrim($linea), '#'),
        ));
    }

    public function test_no_borra_las_vistas_mientras_el_sitio_atiende(): void
    {
        $script = $this->script();

        foreach (['optimize:clear', 'view:clear', 'view:cache', 'filament:clear-cached-components', 'cache:clear'] as $comando) {
            $this->assertStringNotContainsString($comando, $script, "deploy.sh no debe correr {$comando}");
        }
    }

    public function test_hace_lo_que_si_hace_falta(): void
    {
        $script = $this->script();

        $this->assertStringContainsString('set -euo pipefail', $script);
        $this->assertStringContainsString('git pull --ff-only', $script);
        $this->assertStringContainsString('migrate --force', $script);
        $this->assertStringContainsString('filament:cache-components', $script);
        $this->assertStringContainsString('systemctl restart docfacil-queue', $script);
    }
}
