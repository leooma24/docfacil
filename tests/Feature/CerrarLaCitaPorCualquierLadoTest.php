<?php

namespace Tests\Feature;

use App\Filament\Doctor\Pages\Consultation;
use App\Filament\Doctor\Resources\AppointmentResource\Pages\EditAppointment;
use App\Filament\Doctor\Resources\AppointmentResource\Pages\ListAppointments;
use App\Models\Appointment;
use App\Models\Clinic;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Payment;
use App\Models\Service;
use App\Models\TreatmentPlan;
use App\Models\TreatmentPlanItem;
use App\Models\User;
use App\Models\WaitlistEntry;
use App\Support\OdontogramaClinico;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Lo que quedó de la revisión de la consulta (octubre 2026).
 *
 * - La cita del presupuesto que se cierra con "Visita rápida" o "Completar"
 *   no quedaba hecha en el presupuesto ni pasaba al odontograma; solo la
 *   consulta completa lo hacía.
 * - El "Cobrar" de la lista de citas cobraba aunque la cita ya tuviera cobro.
 * - "Siguiente paciente" al cerrar la consulta llevaba al escritorio.
 * - "Eliminar" en una cita por venir dejaba el hueco sin avisar a la lista
 *   de espera.
 */
class CerrarLaCitaPorCualquierLadoTest extends TestCase
{
    use RefreshDatabase;

    private Clinic $clinica;
    private User $user;
    private Doctor $doctor;
    private Patient $ana;
    private Service $resina;

    protected function setUp(): void
    {
        parent::setUp();
        $this->travelTo(today()->setTime(10, 0));
        $this->clinica = Clinic::create(['name' => 'Consultorio', 'plan' => 'profesional', 'plan_ends_at' => now()->addMonth(),
            'trial_ends_at' => now()->subDay(), 'is_active' => true, 'onboarding_status' => 'completed']);
        $this->user = User::forceCreate(['name' => 'Dr. Test', 'email' => 'd@test.com', 'password' => bcrypt('x'), 'role' => 'doctor',
            'email_verified_at' => now(), 'clinic_id' => $this->clinica->id]);
        $this->doctor = Doctor::create(['user_id' => $this->user->id, 'clinic_id' => $this->clinica->id, 'specialty' => 'Odontología']);
        $this->ana = Patient::create(['clinic_id' => $this->clinica->id, 'first_name' => 'Ana', 'last_name' => 'Ruiz', 'phone' => '6681234567']);
        $this->resina = Service::create(['clinic_id' => $this->clinica->id, 'name' => 'Resina (obturación)', 'price' => 600, 'duration_minutes' => 40, 'is_active' => true]);
        Filament::setCurrentPanel(Filament::getPanel('doctor'));
        $this->actingAs($this->user);
    }

    private function cita(array $datos = []): Appointment
    {
        return Appointment::create(array_merge(['clinic_id' => $this->clinica->id, 'doctor_id' => $this->doctor->id, 'patient_id' => $this->ana->id,
            'service_id' => $this->resina->id, 'starts_at' => now()->addMinutes(10), 'ends_at' => now()->addMinutes(50), 'status' => 'confirmed'], $datos));
    }

    private function citaDelPresupuesto(): array
    {
        $plan = TreatmentPlan::create(['clinic_id' => $this->clinica->id, 'patient_id' => $this->ana->id, 'doctor_id' => $this->doctor->id,
            'title' => 'Plan', 'status' => 'accepted', 'subtotal' => 600, 'discount' => 0, 'total' => 600]);
        $item = TreatmentPlanItem::create(['treatment_plan_id' => $plan->id, 'service_id' => $this->resina->id, 'description' => 'Resina en el 36',
            'tooth_number' => '36', 'quantity' => 1, 'unit_price' => 600, 'subtotal' => 600]);

        return [$this->cita(['treatment_plan_item_id' => $item->id]), $item];
    }

    private function condicionDel36(): ?string
    {
        return OdontogramaClinico::ultimo($this->clinica->id, $this->ana->id)?->teeth()->where('tooth_number', 36)->value('condition');
    }

    // ── 1. Cerrar la cita por cualquier lado deja hecho el tratamiento ─

    public function test_completar_desde_la_lista_deja_hecho_el_tratamiento_y_lo_pasa_al_odontograma(): void
    {
        [$cita, $item] = $this->citaDelPresupuesto();

        Livewire::test(ListAppointments::class)->callTableAction('complete', $cita);

        $this->assertNotNull($item->fresh()->completed_at);
        $this->assertSame('filling', $this->condicionDel36());
    }

    public function test_la_visita_rapida_tambien(): void
    {
        [$cita, $item] = $this->citaDelPresupuesto();

        Livewire::test(ListAppointments::class)->callTableAction('quick_visit', $cita, data: ['note' => 'Sin novedad', 'charge' => false, 'next_appointment_date' => null]);

        $this->assertNotNull($item->fresh()->completed_at);
        $this->assertSame('filling', $this->condicionDel36());
    }

    public function test_la_consulta_completa_lo_sigue_haciendo_una_sola_vez(): void
    {
        [$cita, $item] = $this->citaDelPresupuesto();

        Livewire::withQueryParams(['appointment' => $cita->id])->test(Consultation::class)
            ->call('goToStep', 4)
            ->call('saveAndComplete');

        $this->assertNotNull($item->fresh()->completed_at);
        $this->assertSame('filling', $this->condicionDel36());
        $this->assertSame(1, $cita->procedures()->count());
    }

    // ── 2. "Cobrar" no cobra dos veces ───────────────────────────

    public function test_cobrar_desde_la_lista_no_aparece_si_la_cita_ya_se_cobro(): void
    {
        $cita = $this->cita(['status' => 'completed']);
        Payment::create(['clinic_id' => $this->clinica->id, 'patient_id' => $this->ana->id, 'appointment_id' => $cita->id,
            'amount' => 600, 'status' => 'paid', 'payment_method' => 'cash', 'payment_date' => today()]);

        Livewire::test(ListAppointments::class)->assertTableActionHidden('charge', $cita);
    }

    public function test_si_quedo_por_cobrar_cobra_ese_mismo_cobro(): void
    {
        $cita = $this->cita(['status' => 'completed']);
        $porCobrar = Payment::create(['clinic_id' => $this->clinica->id, 'patient_id' => $this->ana->id, 'appointment_id' => $cita->id,
            'amount' => 600, 'status' => 'pending', 'payment_method' => 'cash', 'payment_date' => today(), 'due_date' => today()]);

        Livewire::test(ListAppointments::class)
            ->mountTableAction('charge', $cita)
            ->assertTableActionDataSet(['amount' => 600.0])
            ->callMountedTableAction();

        $this->assertSame(1, Payment::where('appointment_id', $cita->id)->count());
        $this->assertSame('paid', $porCobrar->fresh()->status);
    }

    // ── 3. "Siguiente paciente" lleva al siguiente paciente ──────

    public function test_al_cerrar_la_consulta_siguiente_paciente_abre_al_que_sigue(): void
    {
        $actual = $this->cita(['service_id' => null]);
        $luis = Patient::create(['clinic_id' => $this->clinica->id, 'first_name' => 'Luis', 'last_name' => 'Mora']);
        $siguiente = $this->cita(['patient_id' => $luis->id, 'service_id' => null, 'starts_at' => now()->addHour(), 'ends_at' => now()->addHours(2)]);

        Livewire::withQueryParams(['appointment' => $actual->id])->test(Consultation::class)
            ->call('saveAndComplete')
            ->assertSee('Atender a Luis')
            ->assertSeeHtml(e(route('filament.doctor.pages.consulta', ['appointment' => $siguiente->id])));
    }

    public function test_el_que_ya_llego_va_antes_que_el_que_tiene_cita_mas_temprano(): void
    {
        $actual = $this->cita(['service_id' => null]);
        $luis = Patient::create(['clinic_id' => $this->clinica->id, 'first_name' => 'Luis', 'last_name' => 'Mora']);
        $this->cita(['patient_id' => $luis->id, 'service_id' => null, 'starts_at' => now()->addMinutes(30), 'ends_at' => now()->addHour()]);
        $carla = Patient::create(['clinic_id' => $this->clinica->id, 'first_name' => 'Carla', 'last_name' => 'Vega']);
        $this->cita(['patient_id' => $carla->id, 'service_id' => null, 'starts_at' => now()->addHour(), 'ends_at' => now()->addHours(2), 'arrived_at' => now()]);

        Livewire::withQueryParams(['appointment' => $actual->id])->test(Consultation::class)
            ->call('saveAndComplete')
            ->assertSee('Atender a Carla');
    }

    public function test_sin_nadie_mas_hoy_regresa_al_escritorio(): void
    {
        $actual = $this->cita(['service_id' => null]);

        Livewire::withQueryParams(['appointment' => $actual->id])->test(Consultation::class)
            ->call('saveAndComplete')
            ->assertSee('Ir al escritorio')
            ->assertDontSee('Atender a');
    }

    // ── 4. Quitar una cita por venir avisa a la lista de espera ──

    public function test_la_cita_por_venir_se_cancela_en_vez_de_borrarse_y_avisa_del_hueco(): void
    {
        $cita = $this->cita(['starts_at' => now()->addDays(3), 'ends_at' => now()->addDays(3)->addHour()]);
        $diego = Patient::create(['clinic_id' => $this->clinica->id, 'first_name' => 'Diego', 'last_name' => 'Salazar', 'phone' => '6682222222']);
        WaitlistEntry::create(['clinic_id' => $this->clinica->id, 'patient_id' => $diego->id,
            'desired_from' => today(), 'desired_to' => today()->addDays(7), 'priority' => 0, 'status' => 'waiting']);

        Livewire::test(EditAppointment::class, ['record' => $cita->id])
            ->assertActionHidden('delete')
            ->callAction('cancelarCita');

        $this->assertSame('cancelled', $cita->fresh()->status);
        $this->assertStringContainsString('Ofrecer a Diego', json_encode(session('filament.notifications'), JSON_UNESCAPED_UNICODE));
    }

    public function test_la_cita_que_ya_paso_o_ya_se_cancelo_si_se_puede_borrar(): void
    {
        $vieja = $this->cita(['status' => 'cancelled', 'starts_at' => now()->subDays(3), 'ends_at' => now()->subDays(3)->addHour()]);

        Livewire::test(EditAppointment::class, ['record' => $vieja->id])
            ->assertActionVisible('delete')
            ->assertActionHidden('cancelarCita');
    }
}
