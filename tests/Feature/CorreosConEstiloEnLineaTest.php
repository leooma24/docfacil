<?php

namespace Tests\Feature;

use App\Models\User;
use Filament\Notifications\Auth\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Los correos llevan sus estilos pegados a cada elemento (11-oct-2026).
 *
 * Gmail a veces ignora el <style> del encabezado y el correo llegaba plano:
 * sin el botón verde, sin la franja de la marca. Omar lo vio con el correo de
 * bienvenida. Ahora, antes de salir, cada correo con <style> se pasa a
 * estilos en línea.
 */
class CorreosConEstiloEnLineaTest extends TestCase
{
    use RefreshDatabase;

    public function test_el_correo_de_bienvenida_sale_con_los_estilos_en_cada_elemento(): void
    {
        config(['mail.default' => 'array']);
        $usuario = User::forceCreate(['name' => 'Dra. Laura Méndez', 'email' => 'laura@test.com', 'password' => bcrypt('x'), 'role' => 'doctor']);

        $aviso = new VerifyEmail();
        $aviso->url = 'https://docfacil.tu-app.co/verificar/abc123';
        $usuario->notifyNow($aviso);

        $html = app('mailer')->getSymfonyTransport()->messages()->first()->getOriginalMessage()->getHtmlBody();

        $this->assertMatchesRegularExpression('/<td bgcolor="#0d9488" style="background-color:#0d9488[^"]*">\s*<a [^>]*class="btn"/', $html, 'El botón trae su fondo en la celda, que Gmail sí respeta');
        // Gmail tira el estilo entero si trae !important, y la letra blanca
        // del botón quedaba sobre blanco (lo vio Omar el 11-oct-2026).
        $this->assertStringNotContainsString('!important', $html);
        $this->assertMatchesRegularExpression('/class="franja"[^>]*style="[^"]*background-color: ?#0d9488/', $html, 'La franja de la marca trae su color pegado');
        $this->assertStringContainsString('/images/logo_doc_facil.png', $html, 'Trae el logo');
        $this->assertStringContainsString('Confirmar mi correo', $html);
    }
}
