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

    public function test_beta_page_loads(): void
    {
        $response = $this->get('/beta');
        $response->assertStatus(200);
        $response->assertSee('Programa Beta');
    }

    public function test_demo_route_redirects(): void
    {
        \App\Models\User::forceCreate([
            'name' => 'Demo',
            'email' => 'demo@docfacil.com',
            'password' => bcrypt('demo2026'),
            'role' => 'doctor',
        ]);

        $response = $this->get('/demo');
        $response->assertRedirect('/doctor');
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

    public function test_beta_registration_creates_prospect(): void
    {
        $response = $this->post('/beta', [
            'name' => 'Dr. Beta',
            'email' => 'beta@doctor.com',
            'phone' => '5559876543',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('prospects', [
            'email' => 'beta@doctor.com',
            'status' => 'interested',
        ]);
    }
}
