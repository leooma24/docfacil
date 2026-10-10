<?php

namespace Tests\Feature;

use App\Filament\Doctor\Pages\Consultation;
use App\Filament\Doctor\Pages\RecordatoriosDeManana;
use App\Filament\Doctor\Widgets\AlertsWidget;
use App\Models\Appointment;
use App\Models\Clinic;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Payment;
use App\Models\Service;
use App\Models\TreatmentPlan;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Lo que reportaron los 20 doctores simulados (12-oct-2026,
 * docs/PRUEBA-20-DOCTORES-2026-10-12.md): palabras raras, un menú que
 * abruma, una promesa que no se cumple, la IA apagada que se ofrecía, el
 * corte que dice "te quedaron $100 de cada $100" sin gastos, y detalles.
 */
class LoQueDijeronLosDoctoresTest extends TestCase
{
    use RefreshDatabase;

    private Clinic $clinica;
    private User $usuario;
    private Doctor $doctor;

    protected function setUp(): void
    {
        parent::setUp();
        $this->travelTo(\Carbon\Carbon::parse('2026-10-14 16:00'));
        config(['services.ai.enabled' => false]);
        $this->clinica = Clinic::create(['name' => 'Consultorio Sonrisas', 'plan' => 'profesional', 'plan_ends_at' => now()->addMonth(), 'onboarding_status' => 'completed']);
        $this->usuario = User::forceCreate(['name' => 'Dr. Javier', 'email' => 'javier@test.com', 'password' => bcrypt('x'), 'role' => 'doctor', 'email_verified_at' => now(), 'clinic_id' => $this->clinica->id]);
        $this->doctor = Doctor::create(['user_id' => $this->usuario->id, 'clinic_id' => $this->clinica->id, 'specialty' => 'Odontología']);
        Filament::setCurrentPanel(Filament::getPanel('doctor'));
        $this->actingAs($this->usuario);
    }

    private function paciente(string $nombre, array $extra = []): Patient
    {
        return Patient::create(array_merge(['clinic_id' => $this->clinica->id, 'first_name' => $nombre, 'last_name' => 'Ruiz', 'phone' => '6681234567'], $extra));
    }

    private function cobro(Patient $p, float $monto, float $pagado, string $estado, string $fecha): Payment
    {
        return Payment::create(['clinic_id' => $this->clinica->id, 'patient_id' => $p->id, 'amount' => $monto, 'amount_paid' => $pagado,
            'status' => $estado, 'payment_method' => 'cash', 'payment_date' => $fecha]);
    }

    /** @return array<string, list<string>> */
    private function menu(): array
    {
        $menu = [];
        foreach (Filament::getNavigation() as $grupo) {
            $menu[(string) $grupo->getLabel()] = collect($grupo->getItems())->map(fn ($i) => (string) $i->getLabel())->all();
        }

        return $menu;
    }

    // ── Palabras y menú ─────────────────────────────────────────

    public function test_el_menu_habla_en_espanol_y_no_trae_lo_que_no_usan(): void
    {
        $this->get('/doctor')->assertOk();
        $todo = collect($this->menu())->flatten()->all();

        $this->assertContains('Llegada con QR', $todo);
        $this->assertContains('Extras', $todo);
        foreach (['Check-in QR', 'Add-ons', 'Roadmap', 'Servicios premium', 'Campos de consulta'] as $fuera) {
            $this->assertNotContains($fuera, $todo, "Sobra en el menú: {$fuera}");
        }
    }

    public function test_inventario_va_cerrado(): void
    {
        $this->get('/doctor')->assertOk();
        $grupo = collect(Filament::getNavigation())->first(fn ($g) => $g->getLabel() === 'Inventario');

        $this->assertTrue($grupo->isCollapsed());
    }

    public function test_la_consulta_no_dice_dx_ni_rx(): void
    {
        $p = $this->paciente('Rosa');
        $s = Service::create(['clinic_id' => $this->clinica->id, 'name' => 'Consulta', 'price' => 300, 'duration_minutes' => 30, 'is_active' => true]);

        $html = Livewire::test(Consultation::class)->set('data.walkin_patient_id', (string) $p->id)->set('data.walkin_service_id', (string) $s->id)->call('startWalkIn')->html();

        $this->assertDoesNotMatchRegularExpression('/>\s*(Dx|Rx)\s*</', $html);
    }

    // ── La IA apagada no se ofrece ──────────────────────────────

    public function test_con_la_ia_apagada_no_sale_slot_magico(): void
    {
        $this->get('/doctor/calendario')->assertOk()->assertDontSee('Slot mágico');
    }

    // ── Decir solo lo que hace ──────────────────────────────────

    public function test_nueva_cita_no_promete_recordatorios_que_salen_solos(): void
    {
        $this->get('/doctor/citas/create')->assertOk()
            ->assertDontSee('24h y 2h')
            ->assertSee('Recordatorios de mañana');
    }

    // ── El corte ────────────────────────────────────────────────

    public function test_sin_gastos_el_corte_no_dice_que_le_quedo_todo(): void
    {
        $this->cobro($this->paciente('Ana'), 600, 600, 'paid', '2026-10-10');

        $this->get('/doctor/corte')->assertOk()
            ->assertDontSee('De cada $100')
            ->assertSee('Todavía no anota gastos');
    }

    public function test_el_corte_dice_de_que_es_lo_que_le_deben(): void
    {
        $this->cobro($this->paciente('Beto'), 600, 600, 'paid', '2026-10-09');     // lo que sí entró
        $this->cobro($this->paciente('Ana'), 800, 0, 'pending', '2026-10-10');      // de este mes
        $this->cobro($this->paciente('Rosa'), 3500, 1500, 'partial', '2026-09-20'); // de antes

        $this->get('/doctor/corte')->assertOk()
            ->assertSee('Le deben de este periodo')
            ->assertSee('$800')
            ->assertSee('En total le deben $2,800');
    }

    // ── Caja: la factura ────────────────────────────────────────

    public function test_la_caja_explica_que_la_factura_la_hace_su_contador(): void
    {
        $this->cobro($this->paciente('Ana'), 600, 600, 'paid', '2026-10-14');

        $this->get('/doctor/caja')->assertOk()->assertSee('la hace su contador');
    }

    // ── Detalles ────────────────────────────────────────────────

    public function test_el_nino_sin_telefono_dice_a_quien_le_llega(): void
    {
        $ana = $this->paciente('Ana');
        $mateo = $this->paciente('Mateo', ['phone' => null, 'responsable_id' => $ana->id]);
        Appointment::create(['clinic_id' => $this->clinica->id, 'doctor_id' => $this->doctor->id, 'patient_id' => $mateo->id,
            'starts_at' => now()->addDay()->setTime(9, 0), 'ends_at' => now()->addDay()->setTime(9, 30), 'status' => 'scheduled']);

        Livewire::test(RecordatoriosDeManana::class)->assertSee('le llega a Ana');
    }

    public function test_un_pago_vencido_en_singular(): void
    {
        $p = $this->paciente('Ana');
        Payment::create(['clinic_id' => $this->clinica->id, 'patient_id' => $p->id, 'amount' => 500, 'amount_paid' => 0, 'status' => 'pending',
            'payment_method' => 'cash', 'payment_date' => '2026-10-01', 'due_date' => '2026-10-05']);

        $titulos = collect((new AlertsWidget())->getAlerts())->pluck('title')->implode(' | ');

        $this->assertStringContainsString('1 pago vencido', $titulos);
        $this->assertStringNotContainsString('1 pagos', $titulos);
    }

    public function test_el_presupuesto_dice_los_dias_igual_que_en_pendientes(): void
    {
        TreatmentPlan::create(['clinic_id' => $this->clinica->id, 'patient_id' => $this->paciente('Beto')->id, 'title' => 'Corona', 'status' => 'sent',
            'sent_at' => now()->subDays(12), 'subtotal' => 100, 'discount' => 0, 'total' => 100]);

        $this->get('/doctor/presupuestos')->assertOk()->assertSee('hace 12 días')->assertDontSee('hace 1 semana');
    }

    public function test_la_consulta_no_repite_los_anticoagulantes(): void
    {
        $p = $this->paciente('Rosa', ['riesgos' => ['hipertension', 'anticoagulado']]);
        $s = Service::create(['clinic_id' => $this->clinica->id, 'name' => 'Consulta', 'price' => 300, 'duration_minutes' => 30, 'is_active' => true]);

        Livewire::test(Consultation::class)->set('data.walkin_patient_id', (string) $p->id)->set('data.walkin_service_id', (string) $s->id)->call('startWalkIn')
            ->assertSee('Toma anticoagulantes')
            ->assertDontSee('Hipertensión · Anticoagulantes');
    }
}
