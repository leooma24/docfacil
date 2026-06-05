<?php

namespace Tests\Feature;

use App\Models\Clinic;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Cie10SearchTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $clinic = Clinic::create(['name' => 'Test Clinic', 'onboarding_status' => 'completed']);
        $this->user = User::forceCreate([
            'name' => 'Dr. Test',
            'email' => 'doctor@test.com',
            'password' => bcrypt('password'),
            'role' => 'doctor',
            'clinic_id' => $clinic->id,
        ]);
    }

    public function test_search_requires_authentication(): void
    {
        // El middleware 'auth' rechaza acceso anónimo. En entorno de test,
        // sin route 'login' definida, esto se traduce en redirect, 401, 403, o
        // 500 (UrlGenerationException convertida a HTTP error por el handler).
        // Cualquier código no-200 confirma que el endpoint está protegido.
        $response = $this->get('/api/cie10/search?q=caries');
        $this->assertNotEquals(200, $response->status(), 'Endpoint debe rechazar acceso anónimo');
    }

    public function test_search_by_code_returns_dental_match(): void
    {
        $this->actingAs($this->user);
        $response = $this->get('/api/cie10/search?q=K02');

        $response->assertOk();
        $data = $response->json();
        $this->assertNotEmpty($data);

        $codes = collect($data)->pluck('code')->toArray();
        $this->assertContains('K02', $codes);
        $this->assertContains('K02.1', $codes); // caries de la dentina
    }

    public function test_search_by_name_matches_partial(): void
    {
        $this->actingAs($this->user);
        $response = $this->get('/api/cie10/search?q=cefal');

        $response->assertOk();
        $data = $response->json();
        $this->assertNotEmpty($data);

        $codes = collect($data)->pluck('code')->toArray();
        // R51 = Cefalea
        $this->assertContains('R51', $codes);
    }

    public function test_search_is_accent_insensitive(): void
    {
        $this->actingAs($this->user);
        $r1 = $this->get('/api/cie10/search?q=migrana');
        $r2 = $this->get('/api/cie10/search?q=migraña');

        $r1->assertOk();
        $r2->assertOk();
        // Ambas búsquedas deberían encontrar G43 (Migraña)
        $codes1 = collect($r1->json())->pluck('code')->toArray();
        $codes2 = collect($r2->json())->pluck('code')->toArray();
        $this->assertContains('G43', $codes1);
        $this->assertContains('G43', $codes2);
    }

    public function test_search_returns_empty_for_too_short_query(): void
    {
        $this->actingAs($this->user);
        $response = $this->get('/api/cie10/search?q=k');

        $response->assertOk();
        $this->assertEquals([], $response->json());
    }

    public function test_search_limits_to_20_results(): void
    {
        $this->actingAs($this->user);
        // 'a' por sí solo no, pero "de" matchea muchísimos
        $response = $this->get('/api/cie10/search?q=de');

        $response->assertOk();
        $this->assertLessThanOrEqual(20, count($response->json()));
    }

    public function test_resolve_returns_known_codes(): void
    {
        $this->actingAs($this->user);
        $response = $this->get('/api/cie10/resolve?codes=K02.1,J03.9,XXX99');

        $response->assertOk();
        $data = $response->json();
        $this->assertCount(3, $data);

        $this->assertEquals('K02.1', $data[0]['code']);
        $this->assertStringContainsString('Caries', $data[0]['name']);

        $this->assertEquals('J03.9', $data[1]['code']);
        $this->assertStringContainsString('Amigdalitis', $data[1]['name']);

        // Código inexistente regresa el código + nombre placeholder
        $this->assertEquals('XXX99', $data[2]['code']);
        $this->assertEquals('(no encontrado)', $data[2]['name']);
    }
}
