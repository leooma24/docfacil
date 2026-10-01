<?php

namespace Tests\Feature;

use App\Filament\Doctor\Pages\Consultation;
use App\Filament\Doctor\Widgets\AlertsWidget;
use App\Models\Appointment;
use App\Models\Clinic;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\PaymentPlan;
use App\Models\Service;
use App\Models\TreatmentPlan;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * El paso "Siguiente cita" estaba en blanco aunque DocFácil sabía qué sigue:
 * lo que falta del presupuesto aceptado, la limpieza que regresa en 6 meses,
 * el control de ortodoncia. Ahora lo propone con un clic.
 */
class SiguienteCitaSugeridaTest extends TestCase
{
    use RefreshDatabase;

    private Clinic $clinica;
    private Doctor $doctor;
    private Patient $paciente;

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow('2026-10-01 11:00:00');
        $this->clinica = Clinic::create(['name' => 'Consultorio Test', 'plan' => 'free', 'trial_ends_at' => now()->addDays(10), 'onboarding_status' => 'completed']);
        $user = User::forceCreate(['name' => 'Dr. Test', 'email' => 'd@test.com', 'password' => bcrypt('x'), 'role' => 'doctor', 'email_verified_at' => now(), 'clinic_id' => $this->clinica->id]);
        $this->doctor = Doctor::create(['user_id' => $user->id, 'clinic_id' => $this->clinica->id, 'specialty' => 'Odontología']);
        $this->paciente = Patient::create(['clinic_id' => $this->clinica->id, 'first_name' => 'Carlos', 'last_name' => 'Ruiz']);
        Filament::setCurrentPanel(Filament::getPanel('doctor'));
        $this->actingAs($user);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    private function servicio(string $nombre, ?int $recall = null): Service
    {
        return Service::create(['clinic_id' => $this->clinica->id, 'name' => $nombre, 'price' => 500, 'duration_minutes' => 30, 'is_active' => true, 'recall_months' => $recall]);
    }

    private function consulta(Service $servicio)
    {
        return Livewire::test(Consultation::class)
            ->set('data.walkin_patient_id', (string) $this->paciente->id)
            ->set('data.walkin_service_id', (string) $servicio->id)
            ->call('startWalkIn')
            ->set('currentStep', 5);
    }

    public function test_propone_lo_que_falta_del_presupuesto_y_la_cita_queda_ligada(): void
    {
        $resina = $this->servicio('Resina (obturación)');
        $plan = TreatmentPlan::create(['clinic_id' => $this->clinica->id, 'patient_id' => $this->paciente->id, 'doctor_id' => $this->doctor->id,
            'title' => 'Plan', 'status' => 'accepted', 'discount' => 0, 'total' => 600]);
        $item = $plan->items()->create(['service_id' => $resina->id, 'description' => 'Resina — diente 46', 'tooth_number' => '46', 'quantity' => 1, 'unit_price' => 600, 'sort_order' => 0]);

        $this->consulta($this->servicio('Consulta general'))
            ->assertSee('Resina — diente 46')
            ->call('usarSugerencia', 'item-' . $item->id)
            ->assertSet('next_appointment_service_id', (string) $resina->id)
            ->assertSet('next_appointment_date', '2026-10-08T11:00')
            ->call('saveAndComplete');

        $cita = Appointment::where('treatment_plan_item_id', $item->id)->first();
        $this->assertNotNull($cita);
        $this->assertSame('2026-10-08 11:00', $cita->starts_at->format('Y-m-d H:i'));
    }

    public function test_propone_el_regreso_segun_el_servicio(): void
    {
        $limpieza = $this->servicio('Limpieza dental', recall: 6);

        $this->consulta($limpieza)
            ->assertSee('Limpieza dental en 6 meses')
            ->call('usarSugerencia', 'regreso-' . $limpieza->id)
            ->assertSet('next_appointment_service_id', (string) $limpieza->id)
            ->assertSet('next_appointment_date', '2027-04-01T11:00');
    }

    public function test_propone_el_control_de_ortodoncia_con_la_siguiente_mensualidad(): void
    {
        $orto = $this->servicio('Ortodoncia (mensualidad)');
        PaymentPlan::crear(['clinic_id' => $this->clinica->id, 'patient_id' => $this->paciente->id, 'service_id' => $orto->id,
            'description' => 'Ortodoncia', 'total' => 10000, 'down_payment' => 0, 'installments_count' => 10, 'first_due_date' => '2026-11-03'], null);

        $this->consulta($this->servicio('Consulta general'))
            ->assertSee('Control de Ortodoncia')
            ->call('usarSugerencia', 'control-orto')
            ->assertSet('next_appointment_service_id', (string) $orto->id)
            ->assertSet('next_appointment_date', '2026-11-03T11:00');
    }

    public function test_el_escritorio_avisa_de_tratamientos_aceptados_sin_agendar(): void
    {
        $resina = $this->servicio('Resina (obturación)');
        $plan = TreatmentPlan::create(['clinic_id' => $this->clinica->id, 'patient_id' => $this->paciente->id, 'doctor_id' => $this->doctor->id,
            'title' => 'Plan', 'status' => 'accepted', 'discount' => 0, 'total' => 1200]);
        $plan->items()->create(['service_id' => $resina->id, 'description' => 'Resina 46', 'quantity' => 1, 'unit_price' => 600, 'sort_order' => 0]);
        $plan->items()->create(['service_id' => $resina->id, 'description' => 'Resina 36', 'quantity' => 1, 'unit_price' => 600, 'sort_order' => 1]);

        $titulos = collect(Livewire::test(AlertsWidget::class)->instance()->getAlerts())->pluck('title')->all();

        $this->assertContains('2 tratamientos aceptados sin agendar', $titulos);
    }
}
