<?php

namespace Tests\Feature;

use App\Filament\Sales\Pages\ColaDelDia;
use App\Filament\Sales\Resources\ProspectResource;
use App\Models\Clinic;
use App\Models\Prospect;
use App\Models\ProspectMensaje;
use App\Models\User;
use App\Support\CargaDeTrabajo;
use App\Support\MensajesDeVenta;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * La promoción de fundador a los que ya recibieron un mensaje.
 *
 * Omar, 7-oct-2026: "mándales promoción a los que ya les he enviado, con un
 * mensaje de ganar-ganar; a los que dijeron que no les interesa, no". Les
 * llega una sola vez, sale con su botón en la cola del día (él da enviar
 * desde su WhatsApp) y queda medida como una versión más.
 */
class PromocionFundadorTest extends TestCase
{
    use RefreshDatabase;

    private User $omar;

    protected function setUp(): void
    {
        parent::setUp();
        $this->travelTo(today()->setTime(10, 0));
        $this->omar = User::forceCreate(['name' => 'Omar', 'email' => 'ventas@test.com', 'password' => bcrypt('x'), 'role' => 'sales', 'email_verified_at' => now()]);
        Filament::setCurrentPanel(Filament::getPanel('ventas'));
        $this->actingAs($this->omar);
    }

    private function prospecto(array $datos = []): Prospect
    {
        static $n = 0;
        $n++;

        return Prospect::create(array_merge([
            'name' => "Dra. Prueba{$n} López", 'phone' => '66812345' . str_pad((string) $n, 2, '0', STR_PAD_LEFT), 'city' => 'Los Mochis',
            'source' => 'prospecting', 'status' => 'contacted', 'has_whatsapp' => true, 'contact_day' => 3,
            'last_contact_method' => 'whatsapp', 'last_followup_at' => now()->subDays(4), 'next_contact_at' => now()->addDays(2),
            'assigned_to_sales_rep_id' => $this->omar->id,
        ], $datos));
    }

    public function test_le_toca_al_que_ya_recibio_un_mensaje_y_tiene_whatsapp(): void
    {
        $si = $this->prospecto(['name' => 'Dra. Sí Recibió']);
        $contesto = $this->prospecto(['name' => 'Dr. Contestó', 'replied_at' => now()->subDays(2), 'status' => 'interested']);
        $this->prospecto(['name' => 'Dr. No Interesa', 'status' => 'lost']);
        $this->prospecto(['name' => 'Dr. Ya Cliente', 'status' => 'converted']);
        $this->prospecto(['name' => 'Dr. Nunca Escrito', 'status' => 'new', 'contact_day' => 0, 'last_followup_at' => null]);
        $this->prospecto(['name' => 'Dr. Sin WhatsApp', 'has_whatsapp' => null]);
        $this->prospecto(['name' => 'Dr. Hoy Ya', 'last_followup_at' => now()->subHour()]);
        $ya = $this->prospecto(['name' => 'Dr. Ya La Tiene']);
        ProspectMensaje::create(['prospect_id' => $ya->id, 'user_id' => $this->omar->id, 'paso' => 99, 'version' => 'promo-fundador', 'enviado_at' => now()->subDays(3)]);

        $nombres = CargaDeTrabajo::promocionFundador($this->omar->id)->pluck('name')->all();

        $this->assertEqualsCanonicalizing(['Dra. Sí Recibió', 'Dr. Contestó'], $nombres);
    }

    public function test_el_mensaje_es_el_del_programa_y_dice_cuantos_lugares_quedan(): void
    {
        Clinic::create(['name' => 'Fundadora', 'slug' => 'fundadora', 'is_founder' => true]);
        $p = $this->prospecto(['name' => 'Dra. Karla Ramírez']);

        parse_str(parse_url(ProspectResource::buildPromoFundadorWhatsappUrl($p), PHP_URL_QUERY), $q);
        $texto = $q['text'];

        $this->assertStringStartsWith('Dra. Karla', $texto);
        $this->assertStringContainsString('ganamos los dos', $texto);
        $this->assertStringContainsString('6 meses sin costo', $texto);
        $this->assertStringContainsString('$499 al mes de por vida', $texto);
        $this->assertStringContainsString('Me quedan 9 lugares', $texto);
        // Omar, 7-oct: él les deja cargada la semana; solo se ocupa nombre, WhatsApp y día y hora.
        $this->assertStringContainsString('nombre del paciente, su WhatsApp y el día y la hora', $texto);
        $this->assertStringContainsString('si al final no le sirve, lo deja y listo, sin compromiso', $texto);
        $this->assertStringNotContainsStringIgnoringCase('automátic', $texto);
        $this->assertSame(1, substr_count($texto, '?'), 'Una sola pregunta.');
    }

    public function test_abrir_el_chat_la_registra_sin_mover_la_cadencia(): void
    {
        $p = $this->prospecto(['contact_day' => 3]);

        Livewire::test(ColaDelDia::class)->call('registrarPromocion', $p->id)->call('registrarPromocion', $p->id);

        $p->refresh();
        $this->assertSame(3, $p->contact_day);
        $this->assertTrue($p->last_followup_at->isSameMinute(now()));
        $this->assertTrue($p->next_contact_at->gte(now()->addDays(3)->startOfMinute()), 'El seguimiento normal no le cae encima.');
        $this->assertSame(1, ProspectMensaje::where('version', 'promo-fundador')->count());
        $this->assertSame([], CargaDeTrabajo::promocionFundador($this->omar->id)->all());
        $this->assertArrayHasKey('promo-fundador', MensajesDeVenta::ETIQUETAS);
    }

    public function test_la_cola_la_ensena_con_la_imagen_para_adjuntar(): void
    {
        $this->prospecto(['name' => 'Dra. Abigail Báez']);

        Livewire::test(ColaDelDia::class)
            ->assertSee('Programa Fundador')
            ->assertSee('Abigail')
            ->assertSee('images/promo/fundador.png');

        $this->assertFileExists(public_path('images/promo/fundador.png'));
    }

    public function test_sin_lugares_no_sale(): void
    {
        config(['founders.seats' => 1]);
        Clinic::create(['name' => 'Fundadora', 'slug' => 'fundadora', 'is_founder' => true]);
        \Illuminate\Support\Facades\Cache::flush();
        $this->prospecto();

        $this->assertSame([], CargaDeTrabajo::promocionFundador($this->omar->id)->all());
    }
}
