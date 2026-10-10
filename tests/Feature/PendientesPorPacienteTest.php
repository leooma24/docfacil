<?php

namespace Tests\Feature;

use App\Filament\Doctor\Pages\PendientesPorPaciente;
use App\Filament\Doctor\Widgets\AlertsWidget;
use App\Models\Appointment;
use App\Models\Clinic;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Payment;
use App\Models\TreatmentPlan;
use App\Models\TreatmentPlanItem;
use App\Models\User;
use App\Support\PendientesDelConsultorio;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Los presupuestos que se quedaron en "lo voy a pensar" y los tratamientos
 * que se quedaron a medias.
 *
 * Del dentista, 10-oct-2026: "de cada 10 presupuestos grandes se terminan
 * completos 3 o 4... cuando dice 'lo voy a pensar', ahí se queda, porque no
 * tenemos una lista de presupuestos pendientes. Luego regresa al año con
 * dolor y ya es endodoncia en lugar de resina". Y una regla suya: nada de
 * escribirle al paciente cada tres días ("en Los Mochis todo mundo se
 * conoce"); un recordatorio amable al mes, que él decida mandar.
 */
class PendientesPorPacienteTest extends TestCase
{
    use RefreshDatabase;

    private Clinic $clinica;
    private User $usuario;
    private Doctor $doctor;

    protected function setUp(): void
    {
        parent::setUp();
        $this->travelTo(today()->setTime(10, 0));
        $this->clinica = Clinic::create(['name' => 'Consultorio Sonrisas', 'plan' => 'basico', 'plan_ends_at' => now()->addMonth(), 'onboarding_status' => 'completed']);
        $this->usuario = User::forceCreate(['name' => 'Dr. Javier', 'email' => 'javier@test.com', 'password' => bcrypt('x'), 'role' => 'doctor', 'email_verified_at' => now(), 'clinic_id' => $this->clinica->id]);
        $this->doctor = Doctor::create(['user_id' => $this->usuario->id, 'clinic_id' => $this->clinica->id, 'specialty' => 'Odontología']);
        Filament::setCurrentPanel(Filament::getPanel('doctor'));
        $this->actingAs($this->usuario);
    }

    private function paciente(string $nombre = 'Rosa', ?string $telefono = '6681234567'): Patient
    {
        return Patient::create(['clinic_id' => $this->clinica->id, 'first_name' => $nombre, 'last_name' => 'Valenzuela', 'phone' => $telefono]);
    }

    private function plan(Patient $paciente, string $estado, array $extra = [], int $tratamientos = 0, int $hechos = 0): TreatmentPlan
    {
        $plan = TreatmentPlan::create(array_merge([
            'clinic_id' => $this->clinica->id, 'patient_id' => $paciente->id, 'doctor_id' => $this->doctor->id,
            'title' => 'Plan de ' . $paciente->first_name, 'status' => $estado, 'subtotal' => 8300, 'discount' => 0, 'total' => 8300,
            'public_token' => bin2hex(random_bytes(8)),
        ], $extra));

        for ($i = 1; $i <= $tratamientos; $i++) {
            TreatmentPlanItem::create(['treatment_plan_id' => $plan->id, 'description' => "Tratamiento {$i}", 'quantity' => 1,
                'unit_price' => 1000, 'subtotal' => 1000, 'sort_order' => $i, 'completed_at' => $i <= $hechos ? now()->subDays(20) : null]);
        }

        return $plan;
    }

    // ── Presupuestos sin respuesta ──────────────────────────────

    public function test_sin_respuesta_son_los_enviados_hace_mas_de_una_semana(): void
    {
        $p = $this->paciente();
        $viejo = $this->plan($p, 'sent', ['sent_at' => now()->subDays(10), 'title' => 'Corona y resinas']);
        $this->plan($this->paciente('Beto'), 'sent', ['sent_at' => now()->subDays(3)]);          // todavía es pronto
        $this->plan($this->paciente('Carla'), 'accepted', ['sent_at' => now()->subDays(20)]);    // ya dijo que sí
        $this->plan($this->paciente('Dani'), 'rejected', ['sent_at' => now()->subDays(20)]);     // ya dijo que no
        $this->plan($this->paciente('Eli'), 'draft');                                           // nunca se le mandó

        $filas = PendientesDelConsultorio::sinRespuesta($this->clinica->id);

        $this->assertSame([$viejo->id], $filas->pluck('plan.id')->all());
        $this->assertSame(10, $filas->first()['dias']);
        $this->assertTrue($filas->first()['toca'], 'Nunca se le ha recordado: toca.');
    }

    public function test_un_recordatorio_al_mes_y_no_mas(): void
    {
        $recordado = $this->plan($this->paciente('Ana'), 'sent', ['sent_at' => now()->subDays(40), 'last_reminded_at' => now()->subDays(10)]);
        $hace_mucho = $this->plan($this->paciente('Beto'), 'sent', ['sent_at' => now()->subDays(60), 'last_reminded_at' => now()->subDays(40)]);

        $filas = PendientesDelConsultorio::sinRespuesta($this->clinica->id)->keyBy('plan.id');

        $this->assertFalse($filas[$recordado->id]['toca']);
        $this->assertTrue($filas[$hace_mucho->id]['toca']);
    }

    // ── Tratamientos a medias ───────────────────────────────────

    public function test_a_medias_son_los_aceptados_con_algo_por_agendar(): void
    {
        $empezado = $this->plan($this->paciente('Rosa'), 'accepted', [], tratamientos: 3, hechos: 1);
        $sinEmpezar = $this->plan($this->paciente('Beto'), 'accepted', [], tratamientos: 2, hechos: 0);
        $this->plan($this->paciente('Carla'), 'accepted', [], tratamientos: 2, hechos: 2);        // ya terminó
        $this->plan($this->paciente('Dani'), 'sent', [], tratamientos: 2, hechos: 0);            // no lo ha aceptado

        $agendado = $this->plan($this->paciente('Eli'), 'accepted', [], tratamientos: 1, hechos: 0);
        Appointment::create(['clinic_id' => $this->clinica->id, 'doctor_id' => $this->doctor->id, 'patient_id' => $agendado->patient_id,
            'treatment_plan_item_id' => $agendado->items->first()->id, 'starts_at' => now()->addDays(3), 'ends_at' => now()->addDays(3)->addMinutes(30), 'status' => 'scheduled']);

        $filas = PendientesDelConsultorio::aMedias($this->clinica->id);

        // Los que ya empezaron primero.
        $this->assertSame([$empezado->id, $sinEmpezar->id], $filas->pluck('plan.id')->all());
        $this->assertSame(['1 de 3', '0 de 2'], $filas->pluck('avance')->all());
        $this->assertSame('Tratamiento 2', $filas->first()['siguiente']);
    }

    public function test_a_los_de_a_medias_tambien_es_un_recordatorio_al_mes(): void
    {
        $reciente = $this->plan($this->paciente('Rosa'), 'accepted', ['last_reminded_at' => now()->subDays(5)], tratamientos: 2, hechos: 1);
        $viejo = $this->plan($this->paciente('Beto'), 'accepted', ['last_reminded_at' => now()->subDays(45)], tratamientos: 2, hechos: 1);
        $nunca = $this->plan($this->paciente('Carla'), 'accepted', [], tratamientos: 2, hechos: 1);

        $filas = PendientesDelConsultorio::aMedias($this->clinica->id)->keyBy('plan.id');

        $this->assertFalse($filas[$reciente->id]['toca']);
        $this->assertTrue($filas[$viejo->id]['toca']);
        $this->assertTrue($filas[$nunca->id]['toca']);
    }

    // ── Cuánto debe ─────────────────────────────────────────────

    public function test_cada_fila_trae_cuanto_debe_el_paciente(): void
    {
        $p = $this->paciente();
        $this->plan($p, 'sent', ['sent_at' => now()->subDays(10)]);
        Payment::create(['clinic_id' => $this->clinica->id, 'patient_id' => $p->id, 'amount' => 3000, 'amount_paid' => 500, 'status' => 'partial', 'payment_method' => 'cash', 'payment_date' => today()]);
        Payment::create(['clinic_id' => $this->clinica->id, 'patient_id' => $p->id, 'amount' => 900, 'amount_paid' => 900, 'status' => 'paid', 'payment_method' => 'cash', 'payment_date' => today()]);

        $this->assertSame(2500.0, PendientesDelConsultorio::sinRespuesta($this->clinica->id)->first()['debe']);
    }

    public function test_no_mezcla_a_otro_consultorio(): void
    {
        $otra = Clinic::create(['name' => 'Otra', 'plan' => 'basico', 'plan_ends_at' => now()->addMonth(), 'onboarding_status' => 'completed']);
        $pOtra = Patient::withoutGlobalScopes()->create(['clinic_id' => $otra->id, 'first_name' => 'Ajena', 'last_name' => 'X', 'phone' => '6680000000']);
        TreatmentPlan::withoutGlobalScopes()->create(['clinic_id' => $otra->id, 'patient_id' => $pOtra->id, 'title' => 'Ajeno', 'status' => 'sent',
            'sent_at' => now()->subDays(30), 'subtotal' => 1, 'discount' => 0, 'total' => 1]);

        $this->assertCount(0, PendientesDelConsultorio::sinRespuesta($this->clinica->id));
    }

    // ── El recordatorio a 1 clic ────────────────────────────────

    public function test_el_boton_marca_el_recordatorio_y_abre_whatsapp_de_usted(): void
    {
        $plan = $this->plan($this->paciente('Rosa'), 'sent', ['sent_at' => now()->subDays(10), 'title' => 'Corona y resinas']);

        $r = $this->get(route('plan.recordar', $plan));

        $r->assertRedirect();
        $liga = $r->headers->get('Location');
        $this->assertStringStartsWith('https://wa.me/526681234567?text=', $liga);
        parse_str(parse_url($liga, PHP_URL_QUERY), $q);
        $this->assertStringContainsString('Corona y resinas', $q['text']);
        $this->assertStringContainsString('Rosa', $q['text']);
        $this->assertStringContainsString('usted', $q['text']);
        $this->assertStringNotContainsStringIgnoringCase('automátic', $q['text']);
        $this->assertTrue($plan->fresh()->last_reminded_at->isSameMinute(now()));
    }

    public function test_el_de_otro_consultorio_no_se_abre(): void
    {
        $otra = Clinic::create(['name' => 'Otra', 'plan' => 'basico', 'plan_ends_at' => now()->addMonth(), 'onboarding_status' => 'completed']);
        $pOtra = Patient::withoutGlobalScopes()->create(['clinic_id' => $otra->id, 'first_name' => 'Ajena', 'last_name' => 'X', 'phone' => '6680000000']);
        $plan = TreatmentPlan::withoutGlobalScopes()->create(['clinic_id' => $otra->id, 'patient_id' => $pOtra->id, 'title' => 'Ajeno', 'status' => 'sent',
            'sent_at' => now()->subDays(30), 'subtotal' => 1, 'discount' => 0, 'total' => 1]);

        $this->get(route('plan.recordar', $plan->id))->assertNotFound();
    }

    public function test_sin_telefono_no_hay_a_donde_mandarlo(): void
    {
        $plan = $this->plan($this->paciente('Rosa', null), 'sent', ['sent_at' => now()->subDays(10)]);

        $this->get(route('plan.recordar', $plan))->assertStatus(422);
        $this->assertNull($plan->fresh()->last_reminded_at);
    }

    // ── La pantalla y el aviso ──────────────────────────────────

    public function test_la_pantalla_los_pone_con_su_boton_y_lo_que_debe(): void
    {
        $p = $this->paciente();
        $plan = $this->plan($p, 'sent', ['sent_at' => now()->subDays(10), 'title' => 'Corona y resinas']);
        Payment::create(['clinic_id' => $this->clinica->id, 'patient_id' => $p->id, 'amount' => 3000, 'amount_paid' => 0, 'status' => 'pending', 'payment_method' => 'cash', 'payment_date' => today()]);
        $this->plan($this->paciente('Beto'), 'accepted', [], tratamientos: 3, hechos: 1);

        Livewire::test(PendientesPorPaciente::class)
            ->assertSee('Sin respuesta')
            ->assertSee('Corona y resinas')
            ->assertSee('Debe $3,000')
            ->assertSee(route('plan.recordar', $plan), false)
            ->assertSee('A medias')
            ->assertSee('1 de 3');
    }

    public function test_sin_nada_pendiente_lo_dice(): void
    {
        Livewire::test(PendientesPorPaciente::class)->assertSee('Nada pendiente');
    }

    public function test_el_aviso_del_escritorio_cuenta_los_que_tocan_y_lleva_ahi(): void
    {
        $this->plan($this->paciente('Rosa'), 'sent', ['sent_at' => now()->subDays(10)]);
        $this->plan($this->paciente('Beto'), 'sent', ['sent_at' => now()->subDays(40), 'last_reminded_at' => now()->subDays(5)]); // ya se le recordó

        $aviso = collect((new AlertsWidget())->getAlerts())->first(fn ($a) => str_contains($a['title'], 'sin respuesta'));

        $this->assertNotNull($aviso);
        $this->assertStringContainsString('1 presupuesto', $aviso['title']);
        $this->assertSame(PendientesPorPaciente::getUrl(panel: 'doctor'), $aviso['url']);
    }

    public function test_la_asistente_tambien_los_ve(): void
    {
        $a = User::forceCreate(['name' => 'Lupita', 'email' => 'l@test.com', 'password' => bcrypt('x'), 'role' => 'staff', 'email_verified_at' => now(), 'clinic_id' => $this->clinica->id]);
        $this->actingAs($a);

        $this->get(PendientesPorPaciente::getUrl())->assertOk();
    }
}
