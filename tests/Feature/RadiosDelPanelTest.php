<?php

namespace Tests\Feature;

use Tests\TestCase;

/**
 * Las esquinas del panel, en tres tamaños.
 *
 * En octubre de 2026 había unos 30 radios distintos (8, 9, 10, 11, 12, 14,
 * 16, 18, 20, 24 px y sus versiones en rem): cada pantalla se veía hecha
 * por alguien distinto. Ahora:
 *
 *  - 8 px:  chips, calendario y cajas chicas.
 *  - 12 px: botones, campos y cajas dentro de una tarjeta (avisos, renglones).
 *  - 16 px: tarjetas, secciones y ventanas (lo mismo que la página de inicio).
 *  - 999 px las píldoras, 50 % los círculos, y 2 a 4 px las rayitas.
 */
class RadiosDelPanelTest extends TestCase
{
    private const PERMITIDOS = ['8px', '12px', '16px', '999px', '50%', '2px', '3px', '4px', 'inherit', '0'];

    private function vistas(): array
    {
        $globs = [
            'resources/views/filament/doctor/*.blade.php',
            'resources/views/filament/doctor/*/*.blade.php',
            'resources/views/filament/doctor/*/*/*.blade.php',
            'resources/views/filament/doctor/*/*/*/*.blade.php',
            'resources/views/filament/custom/*.blade.php',
            'resources/views/livewire/*.blade.php',
            'resources/views/components/odontograma/*.blade.php',
        ];

        return collect($globs)->flatMap(fn ($g) => glob(base_path($g)))->unique()->values()->all();
    }

    public function test_cada_esquina_usa_uno_de_los_tres_tamanos(): void
    {
        $fuera = [];

        foreach ($this->vistas() as $vista) {
            preg_match_all('/border-radius\s*:\s*([^;"\'}]+)/', file_get_contents($vista), $m);
            foreach ($m[1] as $valor) {
                $valor = trim(str_replace('!important', '', $valor));
                // Las burbujas de chat llevan una esquina distinta a propósito.
                if (str_contains($valor, ' ')) {
                    continue;
                }
                if (! in_array($valor, self::PERMITIDOS, true)) {
                    $fuera[] = str_replace(base_path() . '/', '', $vista) . ": {$valor}";
                }
            }
        }

        $this->assertSame([], array_values(array_unique($fuera)));
    }

    public function test_las_clases_de_tailwind_tambien(): void
    {
        $fuera = [];

        foreach ($this->vistas() as $vista) {
            // rounded (4px), rounded-md (6px), rounded-sm, rounded-3xl: fuera de la escala.
            if (preg_match_all('/\brounded(?:-(?:sm|md|3xl))?(?=["\s])/', file_get_contents($vista), $m)) {
                $fuera[] = str_replace(base_path() . '/', '', $vista) . ': ' . implode(', ', array_unique($m[0]));
            }
        }

        $this->assertSame([], $fuera);
    }
}
