<?php

namespace Tests\Feature;

use App\Filament\Doctor\Pages\PendientesPorPaciente;
use App\Models\Appointment;
use App\Models\Clinic;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Payment;
use App\Models\Seguimiento as SeguimientoModelo;
use App\Models\Service;
use App\Models\User;
use App\Support\Seguimiento;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Día 5 del plan (10-oct-2026): seguimiento que no se olvida.
 *
 * Las «tareas automáticas» de Dentalink, sin el plan caro: una lista que se
 * arma sola con a quién hay que llamar y por qué (canceló o no vino y no
 * tiene otra cita, debe, le toca su revisión, lleva 6 meses sin venir), y
 * cada uno se cierra con «Logrado», «Volver a llamar en X días» o «Cerrar
 * caso». Al mes, cuánto se recuperó. Nada se manda solo: el WhatsApp lo
 * manda el consultorio desde su teléfono.
 */
class SeguimientoTest extends TestCase
{
    use RefreshDatabase;

    private Clinic $clinica;
    private User $usuario;
    private Doctor $doctor;
    private Patient $ana;

    protected function setUp(): void
    {
        parent::setUp();
        $this->travelTo(\Carbon\Carbon::parse('2026-10-14 10:00'));
        $this->clinica = Clinic::create(['name' => 'Consultorio Sonrisas', 'plan' => 'profesional', 'plan_ends_at' => now()->addMonth(),
            'trial_ends_at' => now()->subDay(), 'is_active' => true, 'onboarding_status' => 'completed']);
        $this->usuario = User::forceCreate(['name' => 'Dr. Test', 'email' => 'd@test.com', 'password' => bcrypt('x'), 'role' => 'doctor',
            'email_verified_at' => now(), 'clinic_id' => $this->clinica->id]);
        $this->doctor = Doctor::create(['user_id' => $this->usuario->id, 'clinic_id' => $this->clinica->id, 'specialty' => 'Odontología']);
        $this->ana = Patient::create(['clinic_id' => $this->clinica->id, 'first_name' => 'Ana', 'last_name' => 'Ruiz', 'phone' => '6681234567']);
        Filament::setCurrentPanel(Filament::getPanel('doctor'));
        $this->actingAs($this->usuario);
    }

    private function cita(string $cuando, string $estado, array $mas = []): Appointment
    {
        return Appointment::create(array_merge(['clinic_id' => $this->clinica->id, 'doctor_id' => $this->doctor->id, 'patient_id' => $this->ana->id,
            'starts_at' => $cuando, 'ends_at' => \Carbon\Carbon::parse($cuando)->addMinutes(30), 'status' => $estado], $mas));
    }

    private function motivos(): array
    {
        return Seguimiento::lista($this->clinica->id)->where('paciente.id', $this->ana->id)->pluck('motivo')->values()->all();
    }

    // ── Quién sale ──────────────────────────────────────────────

    public function test_si_cancelo_y_no_tiene_otra_cita_hay_que_llamarle(): void
    {
        $this->cita('2026-10-10 10:00', 'cancelled');

        $this->assertSame(['cita'], $this->motivos());
        $this->assertStringContainsString('Canceló', Seguimiento::lista($this->clinica->id)->first()['texto']);
    }

    public function test_si_no_vino_tambien(): void
    {
        $this->cita('2026-10-12 10:00', 'no_show');

        $this->assertSame(['cita'], $this->motivos());
        $this->assertStringContainsString('No vino', Seguimiento::lista($this->clinica->id)->first()['texto']);
    }

    public function test_si_ya_tiene_otra_cita_no_sale(): void
    {
        $this->cita('2026-10-10 10:00', 'cancelled');
        $this->cita('2026-10-20 10:00', 'scheduled');

        $this->assertSame([], $this->motivos());
    }

    public function test_si_debe_algo_vencido_sale_con_el_monto(): void
    {
        Payment::create(['clinic_id' => $this->clinica->id, 'patient_id' => $this->ana->id, 'amount' => 1200, 'status' => 'pending',
            'payment_method' => 'cash', 'payment_date' => '2026-09-01', 'due_date' => '2026-09-15']);

        $fila = Seguimiento::lista($this->clinica->id)->firstWhere('motivo', 'deuda');
        $this->assertNotNull($fila);
        $this->assertSame(1200.0, $fila['monto']);
    }

    public function test_si_le_toca_su_revision_sale(): void
    {
        $limpieza = Service::create(['clinic_id' => $this->clinica->id, 'name' => 'Limpieza', 'price' => 600, 'recall_months' => 6]);
        $this->cita('2026-04-01 10:00', 'completed', ['service_id' => $limpieza->id]);

        $this->assertContains('revision', $this->motivos());
        $this->assertStringContainsString('Limpieza', Seguimiento::lista($this->clinica->id)->firstWhere('motivo', 'revision')['texto']);
    }

    public function test_seis_meses_sin_venir_sale_y_tres_meses_no(): void
    {
        $this->cita('2026-03-01 10:00', 'completed');
        $this->assertSame(['inactivo'], $this->motivos());

        $this->cita('2026-07-01 10:00', 'completed');
        $this->assertSame([], $this->motivos());
    }

    public function test_un_paciente_de_otro_consultorio_no_sale(): void
    {
        $otra = Clinic::create(['name' => 'Otra', 'plan' => 'basico']);
        $luis = Patient::withoutGlobalScopes()->create(['clinic_id' => $otra->id, 'first_name' => 'Luis', 'last_name' => 'P']);
        Appointment::withoutGlobalScopes()->create(['clinic_id' => $otra->id, 'doctor_id' => $this->doctor->id, 'patient_id' => $luis->id,
            'starts_at' => '2026-10-10 10:00', 'ends_at' => '2026-10-10 10:30', 'status' => 'cancelled']);

        $this->assertCount(0, Seguimiento::lista($this->clinica->id));
    }

    // ── Cómo se cierra ──────────────────────────────────────────

    public function test_logrado_lo_quita_de_la_lista(): void
    {
        $this->cita('2026-10-10 10:00', 'cancelled');

        Livewire::test(PendientesPorPaciente::class)->call('marcar', $this->ana->id, 'cita', 'logrado');

        $this->assertSame([], $this->motivos());
        $this->assertSame($this->usuario->id, SeguimientoModelo::sole()->user_id);
    }

    public function test_volver_a_llamar_lo_esconde_esos_dias_y_luego_regresa(): void
    {
        $this->cita('2026-10-10 10:00', 'cancelled');

        Livewire::test(PendientesPorPaciente::class)->call('marcar', $this->ana->id, 'cita', 'volver', 7);
        $this->assertSame([], $this->motivos());

        $this->travel(8)->days();
        $this->assertSame(['cita'], $this->motivos());
    }

    public function test_cerrar_caso_ya_no_lo_vuelve_a_sacar(): void
    {
        $this->cita('2026-03-01 10:00', 'completed');

        Livewire::test(PendientesPorPaciente::class)->call('marcar', $this->ana->id, 'inactivo', 'cerrado');
        $this->travel(4)->months();

        $this->assertSame([], $this->motivos());
    }

    public function test_un_motivo_que_no_existe_no_se_guarda(): void
    {
        Livewire::test(PendientesPorPaciente::class)->call('marcar', $this->ana->id, 'inventado', 'logrado');

        $this->assertSame(0, SeguimientoModelo::count());
    }

    // ── Lo que se recuperó ──────────────────────────────────────

    public function test_el_resumen_del_mes_cuenta_lo_logrado_y_lo_cobrado_de_quien_se_llamo(): void
    {
        $deuda = Payment::create(['clinic_id' => $this->clinica->id, 'patient_id' => $this->ana->id, 'amount' => 1200, 'status' => 'pending',
            'payment_method' => 'cash', 'payment_date' => '2026-09-01', 'due_date' => '2026-09-15']);
        Livewire::test(PendientesPorPaciente::class)->call('marcar', $this->ana->id, 'deuda', 'logrado');
        $this->travel(1)->days();
        $deuda->registrarAbono(1200, 'cash');

        $resumen = Seguimiento::resumenDelMes($this->clinica->id);

        $this->assertSame(1, $resumen['logrados']);
        $this->assertSame(1200.0, $resumen['cobrado']);
    }

    // ── La pantalla ─────────────────────────────────────────────

    public function test_la_pantalla_lo_muestra_con_su_whatsapp_y_los_tres_botones(): void
    {
        $this->cita('2026-10-10 10:00', 'cancelled');

        Livewire::test(PendientesPorPaciente::class)
            ->assertSee('A quién hay que llamar')
            ->assertSee('Ana Ruiz')
            ->assertSee('wa.me/526681234567', false)
            ->assertSee('Logrado')
            ->assertSee('Volver a llamar')
            ->assertSee('Cerrar caso');
    }
}
