<?php

namespace Tests\Feature;

use Tests\TestCase;

/**
 * Producción corre con APP_LOCALE=es y APP_FALLBACK_LOCALE=es. Cuando falta
 * un archivo de idioma no hay a dónde caer, y Laravel imprime la clave tal
 * cual: el doctor que tecleaba mal su contraseña veía "auth.failed" en
 * pantalla, y el de recuperar contraseña decía "passwords.sent".
 */
class TraduccionesTest extends TestCase
{
    /** Las que el usuario ve en pantalla en los caminos de error más comunes. */
    private const CLAVES = [
        'auth.failed',
        'auth.password',
        'auth.throttle',
        'passwords.sent',
        'passwords.reset',
        'passwords.throttled',
        'passwords.token',
        'passwords.user',
        'pagination.previous',
        'pagination.next',
    ];

    protected function setUp(): void
    {
        parent::setUp();
        app()->setLocale('es');
    }

    public function test_ninguna_clave_visible_sale_sin_traducir(): void
    {
        $crudas = array_values(array_filter(
            self::CLAVES,
            fn (string $clave) => __($clave) === $clave
        ));

        $this->assertSame(
            [],
            $crudas,
            'Estas claves se le mostrarían al doctor tal cual: ' . implode(', ', $crudas)
        );
    }

    public function test_los_mensajes_estan_en_espanol(): void
    {
        // Un centinela: si alguien borra lang/es/auth.php y el fallback
        // vuelve a inglés, esto lo cacha.
        $this->assertStringNotContainsString('credentials', __('auth.failed'));
        $this->assertStringNotContainsString('We have emailed', __('passwords.sent'));
    }
}
