<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\ChatbotSalesService;
use Filament\Notifications\Auth\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * La contraseña no pasa por la conversación del chat de ventas.
 *
 * El bot la pedía como un mensaje más: viajaba al proveedor de IA, quedaba
 * en la caché de la conversación y, al convertirse, en el historial del
 * prospecto. Ahora se escribe en un cuadro aparte que va directo a crear la
 * cuenta.
 */
class ChatbotSinContrasenaTest extends TestCase
{
    use RefreshDatabase;

    public function test_el_bot_ya_no_pide_la_contrasena_en_la_conversacion(): void
    {
        $prompt = (fn () => $this->systemPrompt())->call(app(ChatbotSalesService::class));

        $this->assertStringNotContainsString('"password"', $prompt);
        $this->assertStringNotContainsString('INPUT type="password"', $prompt);
    }

    public function test_la_cuenta_se_crea_con_la_contrasena_del_cuadro(): void
    {
        Mail::fake();
        Notification::fake();

        $this->postJson('/chatbot/create-account', [
            'session_id' => (string) Str::uuid(),
            'name' => 'Dra. Laura Méndez',
            'email' => 'laura@test.com',
            'password' => 'contrasena-segura',
            'clinic_name' => 'Consultorio Norte',
            'license_number' => '12345678',
            'terms_accepted' => true,
        ])->assertOk()->assertJson(['ok' => true]);

        $usuario = User::where('email', 'laura@test.com')->firstOrFail();

        $this->assertTrue(Hash::check('contrasena-segura', $usuario->password));

        // Antes el correo de verificación tronaba por una ruta que no existe
        // y nunca llegaba.
        Notification::assertSentTo($usuario, VerifyEmail::class, fn (VerifyEmail $aviso) => str_contains($aviso->url, '/doctor/'));
    }
}
