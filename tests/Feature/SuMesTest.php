<?php

namespace Tests\Feature;

use App\Filament\Doctor\Widgets\SuMesWidget;
use App\Models\Appointment;
use App\Models\Clinic;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Payment;
use App\Models\TreatmentPlan;
use App\Models\User;
use App\Support\SuMes;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Lo que pasó este mes en el consultorio, con números de verdad.
 *
 * Del dentista (10-oct-2026): "si al tercer mes veo que me entraron 5 mil que
 * antes se me iban, pago más con gusto. Pero tendría que verlo en números, no
 * que me lo platiquen". Aquí solo se cuenta lo que pasó: nada de "le ahorró
 * $X" estimado.
 */
class SuMesTest extends TestCase
{
    use RefreshDatabase;

    private Clinic $clinica;
    private User $usuario;
    private Doctor $doctor;
    private Patient $rosa;

    protected function setUp(): void
    {
        parent::setUp();
        $this->travelTo(\Carbon\Carbon::parse('2026-10-20 12:00'));
        $this->clinica = Clinic::create(['name' => 'Consultorio Sonrisas', 'plan' => 'basico', 'plan_ends_at' => now()->addMonth(), 'onboarding_status' => 'completed']);
        $this->usuario = User::forceCreate(['name' => 'Dr. Javier', 'email' => 'javier@test.com', 'password' => bcrypt('x'), 'role' => 'doctor', 'email_verified_at' => now(), 'clinic_id' => $this->clinica->id]);
        $this->doctor = Doctor::create(['user_id' => $this->usuario->id, 'clinic_id' => $this->clinica->id, 'specialty' => 'Odontología']);
        $this->rosa = Patient::create(['clinic_id' => $this->clinica->id, 'first_name' => 'Rosa', 'last_name' => 'Valenzuela', 'phone' => '6681234567']);
        Filament::setCurrentPanel(Filament::getPanel('doctor'));
        $this->actingAs($this->usuario);
    }

    private function cita(string $cuando, array $extra = []): Appointment
    {
        return Appointment::create(array_merge(['clinic_id' => $this->clinica->id, 'doctor_id' => $this->doctor->id, 'patient_id' => $this->rosa->id,
            'starts_at' => $cuando, 'ends_at' => \Carbon\Carbon::parse($cuando)->addMinutes(30), 'status' => 'completed'], $extra));
    }

    public function test_cuenta_lo_del_mes_y_lo_compara_con_el_pasado(): void
    {
        // Recordatorios mandados y confirmaciones por la liga.
        $this->cita('2026-10-15 09:00', ['reminder_sent' => true, 'reminder_sent_at' => '2026-10-14 17:00', 'confirmed_at' => '2026-10-14 19:00', 'status' => 'confirmed']);
        $this->cita('2026-10-16 09:00', ['reminder_sent' => true, 'reminder_sent_at' => '2026-10-15 17:00']);
        $this->cita('2026-09-20 09:00', ['reminder_sent' => true, 'reminder_sent_at' => '2026-09-19 17:00']);

        // Inasistencias: 1 este mes, 3 el pasado.
        $this->cita('2026-10-05 10:00', ['status' => 'no_show']);
        foreach (['2026-09-02', '2026-09-10', '2026-09-25'] as $d) {
            $this->cita("{$d} 10:00", ['status' => 'no_show']);
        }

        // Presupuestos aceptados.
        foreach ([[5000, '2026-10-03'], [3300, '2026-10-12'], [9000, '2026-09-28']] as [$total, $fecha]) {
            TreatmentPlan::create(['clinic_id' => $this->clinica->id, 'patient_id' => $this->rosa->id, 'title' => 'Plan', 'status' => 'accepted',
                'accepted_at' => $fecha, 'subtotal' => $total, 'discount' => 0, 'total' => $total]);
        }

        // Cobrado de saldos de antes: un cobro de agosto que se abona hoy.
        $viejo = Payment::create(['clinic_id' => $this->clinica->id, 'patient_id' => $this->rosa->id, 'amount' => 4500, 'amount_paid' => 0,
            'status' => 'pending', 'payment_method' => 'cash', 'payment_date' => '2026-08-10']);
        $viejo->registrarAbono(1500, 'cash');
        // Lo de este mes no cuenta como "de antes".
        Payment::create(['clinic_id' => $this->clinica->id, 'patient_id' => $this->rosa->id, 'amount' => 800, 'amount_paid' => 800,
            'status' => 'paid', 'payment_method' => 'cash', 'payment_date' => '2026-10-18']);

        $mes = SuMes::de($this->clinica->id, now());

        $this->assertSame(2, $mes['recordatorios']['este']);
        $this->assertSame(1, $mes['recordatorios']['pasado']);
        $this->assertSame(1, $mes['confirmaron']['este']);
        $this->assertSame(1, $mes['inasistencias']['este']);
        $this->assertSame(3, $mes['inasistencias']['pasado']);
        $this->assertSame(2, $mes['presupuestos']['este']);
        $this->assertSame(8300.0, $mes['presupuestosMonto']['este']);
        $this->assertSame(9000.0, $mes['presupuestosMonto']['pasado']);
        $this->assertSame(1500.0, $mes['cobradoDeAntes']['este']);
    }

    public function test_no_mezcla_otro_consultorio(): void
    {
        $otra = Clinic::create(['name' => 'Otra', 'plan' => 'basico', 'plan_ends_at' => now()->addMonth(), 'onboarding_status' => 'completed']);
        $p = Patient::withoutGlobalScopes()->create(['clinic_id' => $otra->id, 'first_name' => 'Ajena', 'last_name' => 'X']);
        $u = User::forceCreate(['name' => 'Dr. Otro', 'email' => 'otro@test.com', 'password' => bcrypt('x'), 'role' => 'doctor', 'email_verified_at' => now(), 'clinic_id' => $otra->id]);
        $d = Doctor::withoutGlobalScopes()->create(['user_id' => $u->id, 'clinic_id' => $otra->id, 'specialty' => 'Odontología']);
        Appointment::withoutGlobalScopes()->create(['clinic_id' => $otra->id, 'doctor_id' => $d->id, 'patient_id' => $p->id,
            'starts_at' => '2026-10-15 09:00', 'ends_at' => '2026-10-15 09:30', 'status' => 'no_show', 'reminder_sent_at' => '2026-10-14 17:00']);

        $mes = SuMes::de($this->clinica->id, now());

        $this->assertSame(0, $mes['recordatorios']['este']);
        $this->assertSame(0, $mes['inasistencias']['este']);
    }

    public function test_la_tarjeta_lo_dice_sin_inventar(): void
    {
        $this->cita('2026-10-15 09:00', ['reminder_sent' => true, 'reminder_sent_at' => '2026-10-14 17:00']);
        TreatmentPlan::create(['clinic_id' => $this->clinica->id, 'patient_id' => $this->rosa->id, 'title' => 'Plan', 'status' => 'accepted',
            'accepted_at' => '2026-10-03', 'subtotal' => 5000, 'discount' => 0, 'total' => 5000]);

        Livewire::test(SuMesWidget::class)
            ->assertSee('Su mes en DocFácil')
            ->assertSee('Recordatorios mandados')
            ->assertSee('Presupuestos aceptados')
            ->assertSee('$5,000')
            ->assertDontSee('ahorr');
    }

    public function test_la_asistente_sin_permiso_no_ve_montos(): void
    {
        TreatmentPlan::create(['clinic_id' => $this->clinica->id, 'patient_id' => $this->rosa->id, 'title' => 'Plan', 'status' => 'accepted',
            'accepted_at' => '2026-10-03', 'subtotal' => 5000, 'discount' => 0, 'total' => 5000]);
        $lupita = User::forceCreate(['name' => 'Lupita', 'email' => 'l@test.com', 'password' => bcrypt('x'), 'role' => 'staff', 'email_verified_at' => now(), 'clinic_id' => $this->clinica->id, 've_dinero' => false]);
        $this->actingAs($lupita);

        Livewire::test(SuMesWidget::class)
            ->assertSee('Presupuestos aceptados')
            ->assertDontSee('$5,000');
    }
}
