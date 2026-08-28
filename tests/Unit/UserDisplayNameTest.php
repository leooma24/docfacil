<?php

namespace Tests\Unit;

use App\Models\User;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * El panel doctor antes anteponía "Dr." al nombre, y como la mayoría de
 * los doctores se registran escribiendo su título, salía "Dr. Dr. Roberto".
 * Estos helpers son la fuente de verdad para mostrar el nombre.
 */
class UserDisplayNameTest extends TestCase
{
    public static function nombres(): array
    {
        return [
            //                nombre guardado          completo                  corto
            'con Dr.'    => ['Dr. Roberto García',    'Dr. Roberto García',     'Dr. Roberto'],
            'con Dra.'   => ['Dra. Ana López Ruiz',   'Dra. Ana López Ruiz',    'Dra. Ana'],
            'sin título' => ['Roberto García',        'Roberto García',         'Roberto'],
            'cirujano'   => ['C.D. Luis Mendoza',     'C.D. Luis Mendoza',      'C.D. Luis'],
            'sin punto'  => ['Dr Manuel Soto',        'Dr Manuel Soto',         'Dr Manuel'],
            'minúscula'  => ['dra. Sofía Ramos',      'dra. Sofía Ramos',       'dra. Sofía'],
            'un nombre'  => ['Roberto',               'Roberto',                'Roberto'],
            'espacios'   => ['  Dr.  Ana  Ruiz  ',    'Dr.  Ana  Ruiz',         'Dr. Ana'],
        ];
    }

    #[DataProvider('nombres')]
    public function test_muestra_el_nombre_sin_duplicar_el_titulo(
        string $guardado,
        string $completo,
        string $corto,
    ): void {
        $user = new User(['name' => $guardado]);

        $this->assertSame($completo, $user->displayName());
        $this->assertSame($corto, $user->shortDisplayName());
    }

    public function test_nombre_vacio_cae_en_doctor(): void
    {
        $user = new User(['name' => '']);

        $this->assertSame('Doctor', $user->displayName());
        $this->assertSame('Doctor', $user->shortDisplayName());
    }

    public function test_solo_el_titulo_no_se_pierde(): void
    {
        // "Dr." sin apellido: se conserva tal cual en vez de quedar vacío.
        $user = new User(['name' => 'Dr.']);

        $this->assertSame('Dr.', $user->displayName());
        $this->assertSame('Dr.', $user->shortDisplayName());
    }
}
