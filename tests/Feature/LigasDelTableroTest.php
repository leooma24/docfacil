<?php

namespace Tests\Feature;

use App\Models\Prospect;
use App\Models\ProspectMensaje;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

/**
 * Los botones del tablero de Omar: abren WhatsApp con el mensaje armado y, cuando es un envío de la cadencia,
 * lo dejan anotado en el CRM igual que el botón de la cola del día. Pasan por aquí para que lo que se hace en
 * el tablero se vea en /ventas. Solo para el vendedor dueño del prospecto y con su sesión abierta.
 */
class LigasDelTableroTest extends TestCase
{
    use RefreshDatabase;

    private User $omar;

    protected function setUp(): void
    {
        parent::setUp();
        $this->travelTo(today()->setTime(10, 0));
        $this->omar = User::forceCreate(['name' => 'Omar', 'email' => 'ventas@test.com', 'password' => bcrypt('x'), 'role' => 'sales', 'email_verified_at' => now()]);
    }

    private function prospecto(array $datos = []): Prospect
    {
        static $n = 0;
        $n++;

        return Prospect::create(array_merge([
            'name' => "Dra. Prueba {$n}", 'phone' => '66812345'.str_pad((string) $n, 2, '0', STR_PAD_LEFT), 'city' => 'Los Mochis',
            'source' => 'prospecting', 'status' => 'contacted', 'has_whatsapp' => true, 'contact_day' => 1,
            'last_contact_method' => 'whatsapp', 'next_contact_at' => now()->subHour(),
            'assigned_to_sales_rep_id' => $this->omar->id,
        ], $datos));
    }

    private function resumen(): array
    {
        Artisan::call('docfacil:resumen-json', ['--rep' => $this->omar->id]);

        return json_decode(Artisan::output(), true);
    }

    public function test_el_resumen_trae_la_cola_con_su_liga_y_los_que_contestaron_con_la_suya(): void
    {
        $seg = $this->prospecto();
        $nuevo = $this->prospecto(['status' => 'new', 'contact_day' => 0, 'next_contact_at' => null, 'last_contact_method' => null]);
        $contesto = $this->prospecto(['replied_at' => now()->subDay(), 'clinic_name' => 'Sonrisas']);

        $datos = $this->resumen();

        $cola = collect($datos['cola']);
        $this->assertSame(route('ventas.enviar', $seg), $cola->firstWhere('id', $seg->id)['liga']);
        $this->assertSame(route('ventas.registrar', $seg), $cola->firstWhere('id', $seg->id)['registrar']);
        $this->assertSame('seguimiento', $cola->firstWhere('id', $seg->id)['tipo']);
        $this->assertSame('primer contacto', $cola->firstWhere('id', $nuevo->id)['tipo']);
        $c = collect($datos['contestaron'])->firstWhere('nombre', $contesto->name);
        $this->assertSame(route('ventas.responder', $contesto), $c['liga']);
        $this->assertSame($contesto->phone, $c['telefono']);
    }

    public function test_enviar_solo_abre_whatsapp_con_el_mensaje_del_paso_sin_anotar(): void
    {
        $p = $this->prospecto();

        $r = $this->actingAs($this->omar)->get(route('ventas.enviar', $p));

        $r->assertRedirect();
        $this->assertStringStartsWith('https://wa.me/52'.$p->phone.'?text=', $r->headers->get('Location'));
        $this->assertSame(1, $p->fresh()->contact_day);
        $this->assertSame(0, ProspectMensaje::count());
    }

    public function test_si_lo_envie_anota_el_envio_y_lo_confirma(): void
    {
        $p = $this->prospecto();

        $this->actingAs($this->omar)->get(route('ventas.registrar', $p))->assertOk()->assertSee('Anotado en DocFácil');

        $this->assertSame(3, $p->fresh()->contact_day);
        $this->assertSame(1, ProspectMensaje::where('prospect_id', $p->id)->count());
    }

    public function test_dos_clics_seguidos_no_anotan_dos_envios(): void
    {
        $p = $this->prospecto();
        $this->actingAs($this->omar)->get(route('ventas.registrar', $p));
        $this->actingAs($this->omar)->get(route('ventas.registrar', $p))->assertOk()->assertSee('Ya estaba anotado');

        $this->assertSame(1, ProspectMensaje::where('prospect_id', $p->id)->count());
        $this->assertSame(3, $p->fresh()->contact_day);
    }

    public function test_el_primer_contacto_pasa_a_contactado(): void
    {
        $p = $this->prospecto(['status' => 'new', 'contact_day' => 0, 'next_contact_at' => null, 'last_contact_method' => null]);

        $this->actingAs($this->omar)->get(route('ventas.registrar', $p))->assertOk();

        $this->assertSame('contacted', $p->fresh()->status);
        $this->assertSame(1, $p->fresh()->contact_day);
    }

    public function test_responder_abre_el_mensaje_del_siguiente_paso_sin_mover_la_cadencia(): void
    {
        $p = $this->prospecto(['replied_at' => now()->subDay(), 'notes' => json_encode(['dolor' => 'Pacientes que no llegan'])]);

        $r = $this->actingAs($this->omar)->get(route('ventas.responder', $p));

        $r->assertRedirect();
        $this->assertStringStartsWith('https://wa.me/', $r->headers->get('Location'));
        $this->assertSame(1, $p->fresh()->contact_day);
        $this->assertSame(0, ProspectMensaje::count());
    }

    public function test_sin_sesion_o_de_otro_vendedor_no_se_puede(): void
    {
        $p = $this->prospecto();
        $this->get(route('ventas.enviar', $p))->assertRedirect();
        $this->assertSame(0, ProspectMensaje::count());

        $otro = User::forceCreate(['name' => 'Otro', 'email' => 'otro@test.com', 'password' => bcrypt('x'), 'role' => 'sales', 'email_verified_at' => now()]);
        $this->actingAs($otro)->get(route('ventas.enviar', $p))->assertForbidden();
        $this->actingAs($otro)->get(route('ventas.responder', $p))->assertForbidden();
        $this->actingAs($otro)->get(route('ventas.registrar', $p))->assertForbidden();
        $this->assertSame(0, ProspectMensaje::count());
    }
}
