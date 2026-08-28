<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class LandingTest extends TestCase
{
    use RefreshDatabase;
    public function test_landing_page_loads(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);
        $response->assertSee('DocFacil');
    }

    /**
     * El programa beta se retiró el 2026-04-24 (contradecía el pricing de
     * planes pagados). /beta ahora es un redirect 301 permanente a la
     * landing dental, para no perder el SEO de los links viejos.
     */
    public function test_beta_page_redirects_to_dentistas(): void
    {
        $response = $this->get('/beta');
        $response->assertStatus(301);
        $response->assertRedirect('/dentistas');
    }

    /**
     * /demo prellena las credenciales de la cuenta demo en sesión y manda
     * al login del panel doctor. NO entra directo — el usuario ve el login
     * con los datos ya puestos y solo da click en "Entrar".
     */
    public function test_demo_route_redirects_to_doctor_login(): void
    {
        $response = $this->get('/demo');

        $response->assertRedirect('/doctor/login');
        $response->assertSessionHas('demo_credentials');
    }

    public function test_sitemap_returns_xml(): void
    {
        $response = $this->get('/sitemap.xml');
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/xml');
    }

    public function test_city_landing_loads(): void
    {
        $response = $this->get('/software-dental/cdmx');
        $response->assertStatus(200);
        $response->assertSee('Ciudad de México');
    }

    public function test_city_landing_404_for_invalid_city(): void
    {
        $response = $this->get('/software-dental/invalid-city');
        $response->assertStatus(404);
    }

    public function test_contact_form_creates_prospect(): void
    {
        $response = $this->post('/contacto', [
            'name' => 'Dr. Test',
            'email' => 'test@doctor.com',
            'phone' => '5551234567',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('prospects', ['email' => 'test@doctor.com']);
    }

    public function test_contact_form_honeypot_blocks_bots(): void
    {
        $response = $this->post('/contacto', [
            'name' => 'Bot',
            'email' => 'bot@spam.com',
            'website_url' => 'http://spam.com',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseMissing('prospects', ['email' => 'bot@spam.com']);
    }

    /**
     * POST /beta también quedó como redirect 301 tras retirar el programa
     * beta. Ya no crea prospects — la captura de leads ahora pasa por el
     * formulario de contacto y por el registro directo.
     */
    public function test_beta_post_redirects_and_creates_no_prospect(): void
    {
        $response = $this->post('/beta', [
            'name' => 'Dr. Beta',
            'email' => 'beta@doctor.com',
            'phone' => '5559876543',
        ]);

        $response->assertRedirect('/dentistas');
        $this->assertDatabaseMissing('prospects', [
            'email' => 'beta@doctor.com',
        ]);
    }
}
