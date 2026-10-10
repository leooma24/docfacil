<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Clinic;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Payment;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * La agenda en papel, por si se va el internet.
 *
 * Del dentista (10-oct-2026): "aquí se va la luz en verano con las
 * tormentas, y si no puedo ver la agenda me quedo parado. La agenda de papel
 * nunca se cae". Se imprime la de mañana en la tarde y queda en recepción,
 * con lo importante de cada paciente: teléfono, alertas y lo que debe.
 */
class AgendaParaImprimirTest extends TestCase
{
    use RefreshDatabase;

    private Clinic $clinica;
    private User $usuario;
    private Doctor $doctor;

    protected function setUp(): void
    {
        parent::setUp();
        $this->travelTo(today()->setTime(18, 0));
        $this->clinica = Clinic::create(['name' => 'Consultorio Sonrisas', 'plan' => 'free', 'trial_ends_at' => now()->subDay(), 'onboarding_status' => 'completed']);
        $this->usuario = User::forceCreate(['name' => 'Dr. Javier', 'email' => 'javier@test.com', 'password' => bcrypt('x'), 'role' => 'doctor', 'email_verified_at' => now(), 'clinic_id' => $this->clinica->id]);
        $this->doctor = Doctor::create(['user_id' => $this->usuario->id, 'clinic_id' => $this->clinica->id, 'specialty' => 'Odontología']);
        $this->actingAs($this->usuario);
    }

    private function cita(string $nombre, int $hora, int $enDias = 1, string $estado = 'scheduled', array $paciente = []): Appointment
    {
        $p = Patient::create(array_merge(['clinic_id' => $this->clinica->id, 'first_name' => $nombre, 'last_name' => 'Prueba', 'phone' => '6681234567'], $paciente));

        return Appointment::create(['clinic_id' => $this->clinica->id, 'doctor_id' => $this->doctor->id, 'patient_id' => $p->id,
            'starts_at' => now()->addDays($enDias)->setTime($hora, 0), 'ends_at' => now()->addDays($enDias)->setTime($hora, 30), 'status' => $estado]);
    }

    public function test_por_default_es_la_agenda_de_manana_con_lo_importante(): void
    {
        $rosa = $this->cita('Rosa', 10, 1, 'scheduled', ['allergies' => 'Penicilina', 'riesgos' => ['anticoagulado']]);
        $this->cita('Camila', 9);
        $this->cita('Hoy', 12, 0);
        $this->cita('Pasado', 9, 2);
        $this->cita('Cancelada', 11, 1, 'cancelled');
        Payment::create(['clinic_id' => $this->clinica->id, 'patient_id' => $rosa->patient_id, 'amount' => 3000, 'amount_paid' => 0, 'status' => 'pending', 'payment_method' => 'cash', 'payment_date' => today()]);

        $r = $this->get(route('agenda.imprimir'));

        $r->assertOk()
            ->assertSee('Consultorio Sonrisas')
            ->assertSeeInOrder(['Camila', 'Rosa'])
            ->assertSee('09:00')
            ->assertSee('668 123 4567')
            ->assertSee('Alergia: Penicilina')
            ->assertSee('Anticoagulantes')
            ->assertSee('Debe $3,000')
            ->assertSee('window.print', false)
            ->assertDontSee('Hoy Prueba')
            ->assertDontSee('Pasado Prueba')
            ->assertDontSee('Cancelada Prueba');
    }

    public function test_se_puede_pedir_otro_dia(): void
    {
        $this->cita('Hoy', 12, 0);
        $this->cita('Manana', 9);

        $this->get(route('agenda.imprimir', ['dia' => today()->toDateString()]))
            ->assertOk()->assertSee('Hoy Prueba')->assertDontSee('Manana Prueba');
    }

    public function test_una_fecha_mala_no_truena_y_usa_manana(): void
    {
        $this->cita('Manana', 9);

        $this->get(route('agenda.imprimir', ['dia' => 'no-es-fecha']))->assertOk()->assertSee('Manana Prueba');
    }

    public function test_no_trae_citas_de_otro_consultorio(): void
    {
        $otra = Clinic::create(['name' => 'Otra', 'plan' => 'free', 'trial_ends_at' => now()->subDay(), 'onboarding_status' => 'completed']);
        $p = Patient::withoutGlobalScopes()->create(['clinic_id' => $otra->id, 'first_name' => 'Ajena', 'last_name' => 'X']);
        $u = User::forceCreate(['name' => 'Dr. Otro', 'email' => 'otro@test.com', 'password' => bcrypt('x'), 'role' => 'doctor', 'email_verified_at' => now(), 'clinic_id' => $otra->id]);
        $d = Doctor::withoutGlobalScopes()->create(['user_id' => $u->id, 'clinic_id' => $otra->id, 'specialty' => 'Odontología']);
        Appointment::withoutGlobalScopes()->create(['clinic_id' => $otra->id, 'patient_id' => $p->id, 'doctor_id' => $d->id,
            'starts_at' => now()->addDay()->setTime(9, 0), 'ends_at' => now()->addDay()->setTime(9, 30), 'status' => 'scheduled']);

        $this->get(route('agenda.imprimir'))->assertOk()->assertDontSee('Ajena');
    }

    public function test_sin_sesion_no_se_ve(): void
    {
        auth()->logout();

        $this->get(route('agenda.imprimir'))->assertForbidden();
    }

    public function test_la_liga_esta_en_la_fila_de_recordatorios(): void
    {
        $this->clinica->update(['plan' => 'basico', 'plan_ends_at' => now()->addMonth()]);
        \Filament\Facades\Filament::setCurrentPanel(\Filament\Facades\Filament::getPanel('doctor'));

        \Livewire\Livewire::test(\App\Filament\Doctor\Pages\RecordatoriosDeManana::class)
            ->assertSee(route('agenda.imprimir'), false);
    }
}
