<?php

namespace Tests\Feature;

use App\Models\Prospect;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * El CRM decía que 1,313 prospectos ya estaban contactados y era mentira.
 *
 * El envío de correos corre solo cada hora y venía moviendo el estado —nuevo,
 * contactado, interesado, perdido— sin que nadie hablara con el prospecto. El
 * resultado: la lista de "seguimiento pendiente" llena de gente a la que nunca
 * se le escribió, y un vendedor que abre el panel y no sabe a quién escribirle.
 *
 * Y la palomita de WhatsApp venía de los CSV, donde se puso en 1 a todos por
 * defecto. Este comando separa lo que de verdad pasó de lo que nunca pasó.
 */
class LimpiarProspectosTest extends TestCase
{
    use RefreshDatabase;

    private function prospecto(array $datos = []): Prospect
    {
        return Prospect::create(array_merge([
            'name' => 'Dr. Prueba',
            'phone' => '6681234567',
            'city' => 'Los Mochis',
            'source' => 'prospecting',
            'status' => 'new',
        ], $datos));
    }

    // ── El contacto que nunca fue ────────────────────────────────

    public function test_el_marcado_por_el_correo_regresa_a_nuevo(): void
    {
        $p = $this->prospecto([
            'status' => 'contacted',
            'contact_day' => 0,
            'outreach_started_at' => now()->subMonths(5),
            'next_contact_at' => now()->subMonths(4),
            'last_contact_method' => 'email',
        ]);

        $this->artisan('docfacil:limpiar-prospectos')->assertSuccessful();

        $p->refresh();

        $this->assertSame('new', $p->status);
        $this->assertNull($p->outreach_started_at);
        $this->assertNull($p->next_contact_at);
    }

    public function test_al_que_si_se_le_escribio_por_whatsapp_no_se_le_toca(): void
    {
        $p = $this->prospecto([
            'status' => 'contacted',
            'contact_day' => 3,
            'outreach_started_at' => now()->subDays(3),
            'next_contact_at' => now()->addDays(4),
            'last_contact_method' => 'whatsapp',
        ]);

        $this->artisan('docfacil:limpiar-prospectos')->assertSuccessful();

        $p->refresh();

        $this->assertSame('contacted', $p->status);
        $this->assertNotNull($p->outreach_started_at);
        $this->assertSame(3, $p->contact_day);
    }

    public function test_el_que_contesto_o_ya_va_avanzado_se_respeta(): void
    {
        $interesado = $this->prospecto([
            'status' => 'interested',
            'contact_day' => 0,
            'outreach_started_at' => now()->subMonth(),
            'last_contact_method' => 'whatsapp',
            'replied_at' => now()->subMonth(),
        ]);

        $convertido = $this->prospecto([
            'status' => 'converted',
            'contact_day' => 0,
            'outreach_started_at' => now()->subMonth(),
        ]);

        $this->artisan('docfacil:limpiar-prospectos')->assertSuccessful();

        $this->assertSame('interested', $interesado->fresh()->status);
        $this->assertSame('converted', $convertido->fresh()->status);
    }

    public function test_el_nuevo_con_fecha_de_contacto_falsa_la_pierde(): void
    {
        $p = $this->prospecto([
            'status' => 'new',
            'contact_day' => 0,
            'outreach_started_at' => now()->subMonths(5),
            'next_contact_at' => now()->subMonths(4),
        ]);

        $this->artisan('docfacil:limpiar-prospectos')->assertSuccessful();

        $p->refresh();

        $this->assertNull($p->outreach_started_at);
        $this->assertNull($p->next_contact_at);
        $this->assertSame('new', $p->status);
    }

    public function test_el_que_el_correo_dio_por_perdido_se_recupera(): void
    {
        // El tercer correo lo marcaba como perdido sin que nadie intentara.
        $p = $this->prospecto([
            'status' => 'lost',
            'contact_day' => 0,
            'outreach_started_at' => now()->subMonths(4),
        ]);

        $this->artisan('docfacil:limpiar-prospectos')->assertSuccessful();

        $this->assertSame('new', $p->fresh()->status);
    }

    public function test_el_que_dijo_que_no_sigue_perdido(): void
    {
        $dijoQueNo = $this->prospecto([
            'status' => 'lost',
            'contact_day' => 1,
            'replied_at' => now()->subDay(),
            'last_contact_method' => 'whatsapp',
        ]);

        $numeroMuerto = $this->prospecto([
            'phone' => '6688888888',
            'status' => 'lost',
            'contact_day' => 0,
            'has_whatsapp' => false,
            'notes' => '{"sin_whatsapp":true}',
        ]);

        $this->artisan('docfacil:limpiar-prospectos')->assertSuccessful();

        $this->assertSame('lost', $dijoQueNo->fresh()->status);
        $this->assertSame('lost', $numeroMuerto->fresh()->status);
    }

    // ── La palomita que nadie verificó ───────────────────────────

    public function test_la_palomita_sin_verificar_queda_en_no_sabemos(): void
    {
        $p = $this->prospecto(['has_whatsapp' => true, 'notes' => '{"batch":"dentistas-los-mochis-2026-06-08"}']);

        $this->artisan('docfacil:limpiar-prospectos')->assertSuccessful();

        $this->assertNull($p->fresh()->has_whatsapp);
    }

    public function test_el_verificado_de_verdad_conserva_su_palomita(): void
    {
        $vivo = $this->prospecto([
            'has_whatsapp' => true,
            'notes' => '{"verificado_wa":true,"verificado_at":"2026-09-22 18:00:00"}',
        ]);

        $muerto = $this->prospecto([
            'phone' => '6689999999',
            'has_whatsapp' => false,
            'status' => 'lost',
            'notes' => '{"sin_whatsapp":true,"verificado_at":"2026-09-22 18:00:00"}',
        ]);

        $this->artisan('docfacil:limpiar-prospectos')->assertSuccessful();

        $this->assertTrue($vivo->fresh()->has_whatsapp);
        $this->assertFalse($muerto->fresh()->has_whatsapp);
    }

    // ── Cómo se comporta el comando ──────────────────────────────

    public function test_con_dry_run_no_escribe_nada(): void
    {
        $p = $this->prospecto([
            'status' => 'contacted',
            'contact_day' => 0,
            'outreach_started_at' => now()->subMonths(5),
            'has_whatsapp' => true,
        ]);

        $this->artisan('docfacil:limpiar-prospectos --dry-run')->assertSuccessful();

        $p->refresh();

        $this->assertSame('contacted', $p->status);
        $this->assertNotNull($p->outreach_started_at);
        $this->assertTrue($p->has_whatsapp);
    }

    public function test_correrlo_dos_veces_deja_lo_mismo(): void
    {
        $this->prospecto([
            'status' => 'contacted',
            'contact_day' => 0,
            'outreach_started_at' => now()->subMonths(5),
            'has_whatsapp' => true,
        ]);

        $this->artisan('docfacil:limpiar-prospectos')->assertSuccessful();
        $primera = Prospect::first()->toArray();

        $this->artisan('docfacil:limpiar-prospectos')->assertSuccessful();
        $segunda = Prospect::first()->toArray();

        $this->assertSame($primera['status'], $segunda['status']);
        $this->assertSame($primera['has_whatsapp'], $segunda['has_whatsapp']);
        $this->assertSame($primera['outreach_started_at'], $segunda['outreach_started_at']);
    }
}
