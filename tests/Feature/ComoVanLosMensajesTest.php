<?php

namespace Tests\Feature;

use App\Filament\Sales\Widgets\ComoVanLosMensajesWidget;
use App\Models\Prospect;
use App\Models\ProspectMensaje;
use App\Models\User;
use App\Support\MensajesDeVenta;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Cuántos contestan a cada versión de cada mensaje.
 *
 * Al 5-oct-2026 no se podía saber si los mensajes nuevos (el primero corto,
 * el segundo con video) funcionaban mejor que los de antes: el CRM guardaba
 * el último envío y la respuesta, pero no qué mensaje se mandó. Ahora cada
 * envío queda registrado con su paso y su versión, y la respuesta se le
 * cuenta al último mensaje que recibió la persona antes de contestar.
 */
class ComoVanLosMensajesTest extends TestCase
{
    use RefreshDatabase;

    private User $vendedor;

    protected function setUp(): void
    {
        parent::setUp();
        $this->vendedor = User::forceCreate(['name' => 'Omar', 'email' => 'ventas@test.com', 'password' => bcrypt('x'), 'role' => 'sales', 'email_verified_at' => now()]);
        Filament::setCurrentPanel(Filament::getPanel('ventas'));
        $this->actingAs($this->vendedor);
    }

    private function prospecto(array $datos = []): Prospect
    {
        static $n = 0;
        $n++;

        return Prospect::create(array_merge([
            'name' => "Dr. Prueba {$n}", 'phone' => '66812345' . str_pad((string) $n, 2, '0', STR_PAD_LEFT), 'city' => 'Los Mochis',
            'source' => 'prospecting', 'status' => 'new', 'has_whatsapp' => true, 'contact_day' => 0,
            'assigned_to_sales_rep_id' => $this->vendedor->id,
        ], $datos));
    }

    // ── Cada envío queda registrado ──────────────────────────────

    public function test_cada_envio_guarda_su_paso_y_su_version(): void
    {
        $p = $this->prospecto();

        $p->advanceContactDay('whatsapp');
        $this->travel(1)->days();
        $p->fresh()->advanceContactDay('whatsapp');

        $mensajes = ProspectMensaje::orderBy('id')->get();
        $this->assertSame([0, 1], $mensajes->pluck('paso')->all());
        $this->assertSame([MensajesDeVenta::ACTUAL[0], MensajesDeVenta::ACTUAL[1]], $mensajes->pluck('version')->all());
        $this->assertSame($this->vendedor->id, $mensajes->first()->user_id);
        $this->assertFalse($mensajes->first()->estimado);
    }

    public function test_la_version_actual_es_la_de_los_mensajes_nuevos(): void
    {
        $this->assertSame('p0-corta', MensajesDeVenta::ACTUAL[0]);
        $this->assertSame('p1-video', MensajesDeVenta::ACTUAL[1]);
    }

    // ── La respuesta se cuenta al último mensaje antes de contestar ─

    public function test_la_respuesta_cuenta_para_el_ultimo_mensaje_recibido(): void
    {
        $contesto = $this->prospecto();
        $contesto->advanceContactDay('whatsapp');          // paso 0
        $this->travel(1)->days();
        $contesto->fresh()->advanceContactDay('whatsapp');  // paso 1, con video
        $this->travel(2)->hours();
        $contesto->update(['replied_at' => now()]);

        $nada = $this->prospecto();
        $nada->advanceContactDay('whatsapp');

        $filas = collect(MensajesDeVenta::resultados($this->vendedor->id))->keyBy('version');

        $this->assertSame(2, $filas['p0-corta']['enviados']);
        $this->assertSame(0, $filas['p0-corta']['contestaron']);
        $this->assertSame(1, $filas['p1-video']['enviados']);
        $this->assertSame(1, $filas['p1-video']['contestaron']);
    }

    public function test_solo_cuenta_los_suyos(): void
    {
        $otro = User::forceCreate(['name' => 'Otro', 'email' => 'o@test.com', 'password' => bcrypt('x'), 'role' => 'sales', 'email_verified_at' => now()]);
        $this->prospecto(['assigned_to_sales_rep_id' => $otro->id])->advanceContactDay('whatsapp');

        $this->assertSame([], MensajesDeVenta::resultados($this->vendedor->id));
    }

    // ── La tarjeta ───────────────────────────────────────────────

    public function test_la_tarjeta_compara_las_versiones_y_avisa_cuando_son_pocos(): void
    {
        $this->prospecto()->advanceContactDay('whatsapp');

        Livewire::test(ComoVanLosMensajesWidget::class)
            ->assertSee('Cómo van los mensajes')
            ->assertSee('Primer mensaje corto')
            ->assertSee('Todavía son pocos');
    }

    public function test_la_tarjeta_esta_en_el_escritorio_de_ventas(): void
    {
        $this->assertContains(ComoVanLosMensajesWidget::class, (new \App\Filament\Sales\Pages\Dashboard())->getWidgets());
    }

    // ── Lo que ya se había mandado, estimado por fechas ─────────

    public function test_los_envios_de_antes_se_cargan_estimados_por_fecha(): void
    {
        $viejo = $this->prospecto(['status' => 'contacted', 'contact_day' => 3, 'last_contact_method' => 'whatsapp',
            'outreach_started_at' => '2026-09-20 10:00:00', 'contacted_at' => '2026-09-20 10:00:00', 'last_followup_at' => '2026-09-21 11:00:00']);
        $nuevo = $this->prospecto(['status' => 'contacted', 'contact_day' => 1, 'last_contact_method' => 'whatsapp',
            'outreach_started_at' => '2026-10-02 18:00:00', 'contacted_at' => '2026-10-02 18:00:00', 'last_followup_at' => '2026-10-02 18:00:00']);

        $migracion = require database_path('migrations/2026_10_05_120000_registrar_cada_mensaje_de_venta.php');
        $migracion->cargarLoDeAntes();

        $this->assertSame(['p0-larga', 'p1-repite'], ProspectMensaje::where('prospect_id', $viejo->id)->orderBy('paso')->pluck('version')->all());
        $this->assertSame(['p0-corta'], ProspectMensaje::where('prospect_id', $nuevo->id)->pluck('version')->all());
        $this->assertTrue(ProspectMensaje::where('prospect_id', $viejo->id)->first()->estimado);
    }
}
