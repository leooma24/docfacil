<?php

namespace Tests\Feature;

use App\Filament\Doctor\Widgets\AlertsWidget;
use App\Filament\Doctor\Widgets\SuMesWidget;
use App\Models\Appointment;
use App\Models\Clinic;
use App\Models\Doctor;
use App\Models\LabOrder;
use App\Models\Patient;
use App\Models\TreatmentPlan;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * "Lo que hay que atender" y "Su mes" con mejor cara (Omar, 12-oct-2026:
 * "no se ve profesional"): lo urgente primero y cada aviso con lo que hay
 * que hacer en su botón; Su mes con un resumen en palabras y cuánto cambió
 * contra el mes pasado.
 */
class EscritorioConMejorCaraTest extends TestCase
{
    use RefreshDatabase;

    private Clinic $clinica;
    private Doctor $doctor;
    private Patient $rosa;

    protected function setUp(): void
    {
        parent::setUp();
        $this->travelTo(\Carbon\Carbon::parse('2026-10-14 10:00'));
        $this->clinica = Clinic::create(['name' => 'Consultorio Sonrisas', 'plan' => 'profesional', 'plan_ends_at' => now()->addMonth(), 'onboarding_status' => 'completed']);
        $user = User::forceCreate(['name' => 'Dr. Javier', 'email' => 'd@test.com', 'password' => bcrypt('x'), 'role' => 'doctor', 'email_verified_at' => now(), 'clinic_id' => $this->clinica->id]);
        $this->doctor = Doctor::create(['user_id' => $user->id, 'clinic_id' => $this->clinica->id, 'specialty' => 'Odontología']);
        $this->rosa = Patient::create(['clinic_id' => $this->clinica->id, 'first_name' => 'Rosa', 'last_name' => 'Ruiz', 'phone' => '6681234567']);
        Filament::setCurrentPanel(Filament::getPanel('doctor'));
        $this->actingAs($user);
    }

    private function cita(string $inicio, array $extra = []): Appointment
    {
        return Appointment::create(array_merge(['clinic_id' => $this->clinica->id, 'doctor_id' => $this->doctor->id, 'patient_id' => $this->rosa->id,
            'starts_at' => $inicio, 'ends_at' => \Carbon\Carbon::parse($inicio)->addMinutes(30), 'status' => 'scheduled'], $extra));
    }

    // ── Lo que hay que atender ──────────────────────────────────

    public function test_cada_aviso_dice_que_hacer_y_lo_urgente_va_primero(): void
    {
        // Informativo: mañana sin recordatorio. Urgente: la corona no ha llegado y la cita es pasado mañana.
        $this->cita('2026-10-15 09:00');
        $cita = $this->cita('2026-10-16 11:00');
        LabOrder::create(['clinic_id' => $this->clinica->id, 'patient_id' => $this->rosa->id, 'appointment_id' => $cita->id,
            'laboratorio' => 'Lab Dental', 'trabajo' => 'Corona', 'costo' => 1500, 'enviada_at' => '2026-10-05']);

        $avisos = (new AlertsWidget())->getAlerts();
        $this->assertSame('danger', $avisos[0]['type'], 'Lo urgente va primero');

        Livewire::test(AlertsWidget::class)
            ->assertSee('1 urgente')
            ->assertSee('Ver laboratorio')
            ->assertSee('Mandar')
            ->assertSeeInOrder(['Corona de Rosa no ha llegado', 'mañana sin recordatorio']);
    }

    public function test_el_aviso_del_laboratorio_no_repite_la_palabra(): void
    {
        $cita = $this->cita('2026-10-16 11:00');
        LabOrder::create(['clinic_id' => $this->clinica->id, 'patient_id' => $this->rosa->id, 'appointment_id' => $cita->id,
            'laboratorio' => 'Laboratorio Dental Arte', 'trabajo' => 'Corona', 'costo' => 1500, 'enviada_at' => '2026-10-05']);

        $avisos = collect((new AlertsWidget())->getAlerts())->pluck('desc')->implode(' | ');

        $this->assertStringContainsString('Llámele a Laboratorio Dental Arte', $avisos);
        $this->assertStringNotContainsString('laboratorio Laboratorio', $avisos);
    }

    public function test_sin_avisos_dice_que_todo_esta_en_orden(): void
    {
        $this->cita('2026-10-07 09:00', ['status' => 'completed']); // Rosa vino la semana pasada
        Livewire::test(AlertsWidget::class)->assertSee('Todo en orden por ahora');
    }

    // ── Su mes ──────────────────────────────────────────────────

    public function test_su_mes_lo_resume_en_palabras_y_compara_con_el_mes_pasado(): void
    {
        $this->cita('2026-10-15 09:00', ['reminder_sent' => true, 'reminder_sent_at' => '2026-10-14 08:00']);
        $this->cita('2026-10-16 09:00', ['reminder_sent' => true, 'reminder_sent_at' => '2026-10-14 08:05']);
        $this->cita('2026-09-16 09:00', ['reminder_sent' => true, 'reminder_sent_at' => '2026-09-15 08:00']);
        TreatmentPlan::create(['clinic_id' => $this->clinica->id, 'patient_id' => $this->rosa->id, 'title' => 'Plan', 'status' => 'accepted',
            'accepted_at' => '2026-10-03', 'subtotal' => 5000, 'discount' => 0, 'total' => 5000]);

        Livewire::test(SuMesWidget::class)
            ->assertSee('En octubre mandó 2 recordatorios y le aceptaron 1 presupuesto por $5,000.00')
            ->assertSee('1 más que septiembre')
            ->assertSee('Agenda')
            ->assertSee('Tratamientos y dinero');
    }

    public function test_su_mes_sin_movimiento_explica_que_va_a_ver(): void
    {
        Livewire::test(SuMesWidget::class)
            ->assertSee('Todavía no hay movimiento en octubre');
    }
}
