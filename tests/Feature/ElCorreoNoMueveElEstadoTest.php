<?php

namespace Tests\Feature;

use App\Mail\ProspectBetaInviteMail;
use App\Mail\ProspectFollowupMail;
use App\Models\LifecycleEmail;
use App\Models\Prospect;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/**
 * El correo informa; la persona es la que contacta.
 *
 * Este comando corre solo cada hora y venía avanzando el `status` de los
 * prospectos —nuevo, contactado, interesado, perdido— con cada correo. Nadie
 * había hablado con ellos, pero el CRM los mostraba como trabajados, y la cola
 * del vendedor se llenó de 1,313 contactos que nunca ocurrieron. Peor: al
 * tercer correo los marcaba como perdidos sin que nadie hubiera intentado.
 *
 * Ahora el avance del correo vive donde siempre debió: en `lifecycle_emails`.
 * El `status` queda para lo que hizo una persona.
 */
class ElCorreoNoMueveElEstadoTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
    }

    private function prospecto(array $datos = []): Prospect
    {
        return Prospect::create(array_merge([
            'name' => 'Dr. Prueba',
            'email' => 'doctor@ejemplo.com',
            'phone' => '6681234567',
            'city' => 'Los Mochis',
            'source' => 'prospecting',
            'status' => 'new',
        ], $datos));
    }

    private function correoPrevio(Prospect $p, string $tipo, int $diasAtras): void
    {
        LifecycleEmail::create([
            'emailable_type' => Prospect::class,
            'emailable_id' => $p->id,
            'type' => $tipo,
            'subject' => 'previo',
            'sent_at' => now()->subDays($diasAtras),
        ]);
    }

    public function test_manda_el_primer_correo_y_deja_el_estado_en_nuevo(): void
    {
        $p = $this->prospecto();

        $this->artisan('docfacil:send-prospect-emails')->assertSuccessful();

        Mail::assertSent(ProspectBetaInviteMail::class);

        $p->refresh();

        $this->assertSame('new', $p->status);
        $this->assertNull($p->contacted_at);
        $this->assertNull($p->outreach_started_at);
    }

    public function test_el_envio_queda_registrado_en_el_historial(): void
    {
        $p = $this->prospecto();

        $this->artisan('docfacil:send-prospect-emails')->assertSuccessful();

        $this->assertDatabaseHas('lifecycle_emails', [
            'emailable_id' => $p->id,
            'type' => 'prospect_beta_invite',
        ]);
    }

    public function test_no_manda_dos_veces_el_mismo_correo(): void
    {
        $p = $this->prospecto();
        $this->correoPrevio($p, 'prospect_beta_invite', 0);

        $this->artisan('docfacil:send-prospect-emails')->assertSuccessful();

        Mail::assertNotSent(ProspectBetaInviteMail::class);
    }

    public function test_el_seguimiento_sale_por_el_correo_anterior_y_no_por_el_estado(): void
    {
        // Sigue en 'new' porque nadie le ha escrito a mano. Antes esto lo
        // dejaba fuera del segundo correo para siempre.
        $p = $this->prospecto(['status' => 'new']);
        $this->correoPrevio($p, 'prospect_beta_invite', 10);

        $this->artisan('docfacil:send-prospect-emails')->assertSuccessful();

        Mail::assertSent(ProspectFollowupMail::class);
        $this->assertSame('new', $p->fresh()->status);
    }

    public function test_el_ultimo_correo_ya_no_lo_marca_como_perdido(): void
    {
        $p = $this->prospecto();
        $this->correoPrevio($p, 'prospect_beta_invite', 20);
        $this->correoPrevio($p, 'prospect_followup', 10);

        $this->artisan('docfacil:send-prospect-emails')->assertSuccessful();

        $this->assertSame('new', $p->fresh()->status);
    }

    public function test_al_que_ya_trabajo_una_persona_no_le_manda_la_secuencia_fria(): void
    {
        $p = $this->prospecto([
            'status' => 'interested',
            'contact_day' => 3,
            'last_contact_method' => 'whatsapp',
            'replied_at' => now()->subDay(),
        ]);

        $this->artisan('docfacil:send-prospect-emails')->assertSuccessful();

        Mail::assertNothingSent();
        $this->assertSame('interested', $p->fresh()->status);
    }

    public function test_al_que_dijo_que_no_no_se_le_escribe(): void
    {
        $this->prospecto(['status' => 'lost']);

        $this->artisan('docfacil:send-prospect-emails')->assertSuccessful();

        Mail::assertNothingSent();
    }
}
