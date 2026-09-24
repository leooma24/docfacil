<?php

namespace Tests\Feature;

use App\Filament\Sales\Pages\ColaDelDia;
use App\Models\Prospect;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * La pantalla que contesta "¿a quién le escribo hoy?".
 *
 * Sin esto, el vendedor abre el CRM con 1,526 prospectos y termina
 * preguntándole a alguien más a quién mandarle. Tres bloques y nada más:
 * a quién le toca primer contacto, a quién le toca seguimiento, y quién ya
 * contestó y sigue esperando respuesta —ese es el más caro de dejar enfriar.
 *
 * La regla dura: al primer contacto solo entran números verificados. De los
 * lotes de directorio, 9 de cada 10 no existían en WhatsApp, y cada mensaje
 * al vacío acerca el número de quien vende a un bloqueo.
 */
class ColaDelDiaTest extends TestCase
{
    use RefreshDatabase;

    private User $vendedor;

    protected function setUp(): void
    {
        parent::setUp();

        $this->vendedor = User::forceCreate([
            'name' => 'Omar',
            'email' => 'ventas@test.com',
            'password' => bcrypt('password'),
            'role' => 'sales',
            'email_verified_at' => now(),
        ]);

        Filament::setCurrentPanel(Filament::getPanel('ventas'));
        $this->actingAs($this->vendedor);
    }

    private function prospecto(array $datos = []): Prospect
    {
        static $n = 0;
        $n++;

        return Prospect::create(array_merge([
            'name' => "Dr. Prueba {$n}",
            'phone' => '66812345' . str_pad((string) $n, 2, '0', STR_PAD_LEFT),
            'city' => 'Los Mochis',
            'source' => 'prospecting',
            'status' => 'new',
            'has_whatsapp' => true,
            'notes' => '{"verificado_wa":true}',
            'assigned_to_sales_rep_id' => $this->vendedor->id,
        ], $datos));
    }

    private function cola(): array
    {
        return Livewire::test(ColaDelDia::class)->instance()->getViewData();
    }

    // ── Primer contacto ──────────────────────────────────────────

    public function test_el_verificado_sin_contactar_aparece(): void
    {
        $p = $this->prospecto();

        $this->assertTrue($this->cola()['primerContacto']->contains('id', $p->id));
    }

    public function test_el_no_verificado_no_aparece(): void
    {
        $sinVerificar = $this->prospecto(['has_whatsapp' => null, 'notes' => null]);
        $sinWhatsApp = $this->prospecto(['has_whatsapp' => false, 'notes' => '{"sin_whatsapp":true}']);

        $primerContacto = $this->cola()['primerContacto'];

        $this->assertFalse($primerContacto->contains('id', $sinVerificar->id));
        $this->assertFalse($primerContacto->contains('id', $sinWhatsApp->id));
    }

    public function test_el_que_ya_recibio_whatsapp_no_vuelve_a_primer_contacto(): void
    {
        $p = $this->prospecto([
            'status' => 'contacted',
            'contact_day' => 1,
            'last_contact_method' => 'whatsapp',
            'next_contact_at' => now()->addDays(2),
        ]);

        $this->assertFalse($this->cola()['primerContacto']->contains('id', $p->id));
    }

    public function test_nunca_pasa_del_tope_del_dia(): void
    {
        for ($i = 0; $i < ColaDelDia::TOPE_DIARIO + 5; $i++) {
            $this->prospecto();
        }

        $this->assertCount(ColaDelDia::TOPE_DIARIO, $this->cola()['primerContacto']);
    }

    public function test_los_de_otro_vendedor_no_salen(): void
    {
        $ajeno = $this->prospecto(['assigned_to_sales_rep_id' => null]);

        $this->assertFalse($this->cola()['primerContacto']->contains('id', $ajeno->id));
    }

    // ── Seguimientos ─────────────────────────────────────────────

    public function test_el_seguimiento_vencido_aparece(): void
    {
        $p = $this->prospecto([
            'status' => 'contacted',
            'contact_day' => 3,
            'last_contact_method' => 'whatsapp',
            'next_contact_at' => now()->subDay(),
        ]);

        $this->assertTrue($this->cola()['seguimientos']->contains('id', $p->id));
    }

    public function test_el_seguimiento_que_no_le_toca_todavia_no_aparece(): void
    {
        $p = $this->prospecto([
            'status' => 'contacted',
            'contact_day' => 3,
            'last_contact_method' => 'whatsapp',
            'next_contact_at' => now()->addDays(3),
        ]);

        $this->assertFalse($this->cola()['seguimientos']->contains('id', $p->id));
    }

    public function test_el_contacto_falso_del_correo_no_cuenta_como_seguimiento(): void
    {
        // Día de cadencia en cero: el correo lo marcó, nadie le escribió.
        $p = $this->prospecto([
            'status' => 'contacted',
            'contact_day' => 0,
            'last_contact_method' => 'email',
            'next_contact_at' => now()->subMonths(4),
        ]);

        $this->assertFalse($this->cola()['seguimientos']->contains('id', $p->id));
    }

    // ── Los que contestaron ──────────────────────────────────────

    public function test_el_que_contesto_aparece_hasta_arriba(): void
    {
        $p = $this->prospecto([
            'status' => 'interested',
            'contact_day' => 1,
            'last_contact_method' => 'whatsapp',
            'replied_at' => now()->subHours(3),
        ]);

        $this->assertTrue($this->cola()['contestaron']->contains('id', $p->id));
    }

    public function test_el_que_dijo_que_no_ya_no_aparece_en_ningun_bloque(): void
    {
        $p = $this->prospecto([
            'status' => 'lost',
            'contact_day' => 1,
            'replied_at' => now()->subDay(),
            'next_contact_at' => null,
        ]);

        $cola = $this->cola();

        $this->assertFalse($cola['contestaron']->contains('id', $p->id));
        $this->assertFalse($cola['seguimientos']->contains('id', $p->id));
        $this->assertFalse($cola['primerContacto']->contains('id', $p->id));
    }

    // ── Los números de arriba ────────────────────────────────────

    public function test_cuenta_los_enviados_de_hoy_y_las_respuestas(): void
    {
        $this->prospecto([
            'status' => 'contacted',
            'contact_day' => 1,
            'last_contact_method' => 'whatsapp',
            'last_followup_at' => now(),
        ]);

        $this->prospecto([
            'status' => 'contacted',
            'contact_day' => 1,
            'last_contact_method' => 'whatsapp',
            'last_followup_at' => now()->subDays(2),
            'replied_at' => now(),
        ]);

        $numeros = $this->cola()['numeros'];

        $this->assertSame(1, $numeros['enviadosHoy']);
        $this->assertSame(1, $numeros['respuestasHoy']);
        $this->assertSame(2, $numeros['enviadosSemana']);
    }
}
