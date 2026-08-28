<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Regresión: rutas protegidas fuera de un panel Filament reventaban con
 * 500 "Route [login] not defined" porque Laravel busca por defecto una
 * ruta llamada 'login' que este proyecto no tiene (cada panel tiene la
 * suya: /doctor/login, /admin/login, ...).
 *
 * Fix en bootstrap/app.php → $middleware->redirectGuestsTo(...).
 */
class GuestRedirectTest extends TestCase
{
    use RefreshDatabase;

    public function test_protected_api_route_redirects_instead_of_500(): void
    {
        $response = $this->get('/api/cie10/search?q=caries');

        $response->assertStatus(302);
        $response->assertRedirect('/doctor/login');
    }

    public function test_spei_receipt_route_redirects_instead_of_500(): void
    {
        $response = $this->get('/billing/spei-receipts/1');

        $response->assertStatus(302);
        $response->assertRedirect('/doctor/login');
    }

    public function test_admin_panel_redirects_to_admin_login(): void
    {
        $response = $this->get('/admin');

        $response->assertStatus(302);
        $this->assertStringContainsString('/admin/login', $response->headers->get('Location'));
    }

    public function test_paciente_panel_redirects_to_paciente_login(): void
    {
        $response = $this->get('/paciente');

        $response->assertStatus(302);
        $this->assertStringContainsString('/paciente/login', $response->headers->get('Location'));
    }

    public function test_ventas_panel_redirects_to_ventas_login(): void
    {
        $response = $this->get('/ventas');

        $response->assertStatus(302);
        $this->assertStringContainsString('/ventas/login', $response->headers->get('Location'));
    }

    public function test_doctor_panel_redirects_to_doctor_login(): void
    {
        $response = $this->get('/doctor/pacientes');

        $response->assertStatus(302);
        $this->assertStringContainsString('/doctor/login', $response->headers->get('Location'));
    }

    public function test_json_request_gets_401_not_redirect(): void
    {
        $response = $this->getJson('/api/cie10/search?q=caries');

        $response->assertStatus(401);
    }
}
