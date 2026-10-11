<?php

namespace Tests\Feature;

use App\Filament\Doctor\Pages\Opiniones;
use App\Filament\Paciente\Pages\FirmarConsentimiento;
use App\Filament\Paciente\Widgets\MiResumen;
use App\Models\Appointment;
use App\Models\Clinic;
use App\Models\ConsentForm;
use App\Models\Doctor;
use App\Models\Encuesta;
use App\Models\Patient;
use App\Models\Payment;
use App\Models\TreatmentPlan;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Día 9 del plan (10-oct-2026): el portal del paciente.
 *
 * Como el portal de DaleControl: el paciente ve su saldo, sus presupuestos y
 * sus recibos; firma sus consentimientos antes de la cita desde su celular,
 * y después de la visita deja estrellas y un comentario que solo ve el
 * consultorio. Antes el portal solo listaba citas, pagos y recetas.
 */
class PortalDelPacienteTest extends TestCase
{
    use RefreshDatabase;

    private const FIRMA = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNkYPhfDwAChwGA60e6kgAAAABJRU5ErkJggg==';

    private Clinic $clinica;
    private User $doctorUser;
    private Doctor $doctor;
    private User $usuarioAna;
    private Patient $ana;
    private Patient $luis;

    protected function setUp(): void
    {
        parent::setUp();
        $this->travelTo(\Carbon\Carbon::parse('2026-10-14 10:00'));
        $this->clinica = Clinic::create(['name' => 'Consultorio Sonrisas', 'plan' => 'profesional', 'plan_ends_at' => now()->addMonth(),
            'trial_ends_at' => now()->subDay(), 'is_active' => true, 'onboarding_status' => 'completed']);
        $this->doctorUser = User::forceCreate(['name' => 'Dr. Javier', 'email' => 'd@test.com', 'password' => bcrypt('x'), 'role' => 'doctor',
            'email_verified_at' => now(), 'clinic_id' => $this->clinica->id]);
        $this->doctor = Doctor::create(['user_id' => $this->doctorUser->id, 'clinic_id' => $this->clinica->id, 'specialty' => 'Odontología']);
        $this->usuarioAna = User::forceCreate(['name' => 'Ana Ruiz', 'email' => 'ana@test.com', 'password' => bcrypt('x'), 'role' => 'patient',
            'email_verified_at' => now(), 'clinic_id' => $this->clinica->id]);
        $this->ana = Patient::create(['clinic_id' => $this->clinica->id, 'first_name' => 'Ana', 'last_name' => 'Ruiz', 'phone' => '6681234567', 'user_id' => $this->usuarioAna->id]);
        $this->luis = Patient::create(['clinic_id' => $this->clinica->id, 'first_name' => 'Luis', 'last_name' => 'Mora']);
        Filament::setCurrentPanel(Filament::getPanel('paciente'));
        $this->actingAs($this->usuarioAna);
    }

    private function cita(Patient $p, string $cuando, string $estado): Appointment
    {
        return Appointment::create(['clinic_id' => $this->clinica->id, 'doctor_id' => $this->doctor->id, 'patient_id' => $p->id,
            'starts_at' => $cuando, 'ends_at' => \Carbon\Carbon::parse($cuando)->addMinutes(30), 'status' => $estado]);
    }

    private function consentimiento(Patient $p): ConsentForm
    {
        return ConsentForm::create(['clinic_id' => $this->clinica->id, 'patient_id' => $p->id, 'doctor_id' => $this->doctor->id,
            'title' => 'Consentimiento de endodoncia', 'content' => '<p>Autorizo la endodoncia en el diente 36.</p>', 'procedure_name' => 'Endodoncia']);
    }

    // ── Su resumen ──────────────────────────────────────────────

    public function test_ve_su_proxima_cita_su_saldo_sus_presupuestos_y_lo_que_falta_firmar(): void
    {
        $this->cita($this->ana, '2026-10-20 10:00', 'scheduled');
        Payment::create(['clinic_id' => $this->clinica->id, 'patient_id' => $this->ana->id, 'amount' => 1200, 'status' => 'pending',
            'payment_method' => 'cash', 'payment_date' => '2026-10-01', 'due_date' => '2026-10-01']);
        TreatmentPlan::create(['clinic_id' => $this->clinica->id, 'patient_id' => $this->ana->id, 'title' => 'Rehabilitación',
            'status' => 'sent', 'total' => 9000, 'public_token' => str_repeat('d', 64)]);
        $this->consentimiento($this->ana);

        Livewire::test(MiResumen::class)
            ->assertSee('martes 20 de octubre')
            ->assertSee('Debe $1,200')
            ->assertSee('Rehabilitación')
            ->assertSee('/p/' . str_repeat('d', 64), false)
            ->assertSee('Consentimiento de endodoncia')
            ->assertSee('Firmar');
    }

    public function test_no_ve_nada_de_otro_paciente(): void
    {
        $this->cita($this->luis, '2026-10-20 10:00', 'scheduled');
        $this->consentimiento($this->luis);

        Livewire::test(MiResumen::class)->assertDontSee('20 de octubre')->assertDontSee('Consentimiento de endodoncia');
    }

    public function test_baja_su_estado_de_cuenta_y_sus_recibos_y_no_los_de_otro(): void
    {
        $suyo = Payment::create(['clinic_id' => $this->clinica->id, 'patient_id' => $this->ana->id, 'amount' => 800, 'status' => 'paid',
            'payment_method' => 'card', 'payment_date' => '2026-10-01']);
        $ajeno = Payment::create(['clinic_id' => $this->clinica->id, 'patient_id' => $this->luis->id, 'amount' => 500, 'status' => 'paid',
            'payment_method' => 'cash', 'payment_date' => '2026-10-01']);

        $this->get(route('portal.estado-de-cuenta'))->assertOk()->assertHeader('content-type', 'application/pdf');
        $this->get(route('portal.recibo', $suyo))->assertOk()->assertHeader('content-type', 'application/pdf');
        $this->get(route('portal.recibo', $ajeno))->assertNotFound();
    }

    // ── Firmar el consentimiento ────────────────────────────────

    public function test_firma_su_consentimiento_desde_el_portal(): void
    {
        $consentimiento = $this->consentimiento($this->ana);

        Livewire::withQueryParams(['consentimiento' => $consentimiento->id])->test(FirmarConsentimiento::class)
            ->assertSee('Autorizo la endodoncia')
            ->call('firmar', self::FIRMA)
            ->assertHasNoErrors();

        $consentimiento->refresh();
        $this->assertSame(self::FIRMA, $consentimiento->signature);
        $this->assertNotNull($consentimiento->signed_at);
    }

    public function test_no_puede_firmar_el_de_otro_ni_uno_ya_firmado(): void
    {
        $ajeno = $this->consentimiento($this->luis);
        $this->get(FirmarConsentimiento::getUrl(['consentimiento' => $ajeno->id]))->assertNotFound();

        $firmado = $this->consentimiento($this->ana);
        $firmado->update(['signature' => self::FIRMA, 'signed_at' => now()->subDay()]);
        Livewire::withQueryParams(['consentimiento' => $firmado->id])->test(FirmarConsentimiento::class)
            ->call('firmar', 'data:image/png;base64,AAAA');
        $this->assertSame(self::FIRMA, $firmado->fresh()->signature);
    }

    public function test_una_firma_que_no_es_imagen_no_se_guarda(): void
    {
        $consentimiento = $this->consentimiento($this->ana);

        Livewire::withQueryParams(['consentimiento' => $consentimiento->id])->test(FirmarConsentimiento::class)
            ->call('firmar', 'javascript:alert(1)');

        $this->assertNull($consentimiento->fresh()->signed_at);
    }

    // ── ¿Cómo le fue? ───────────────────────────────────────────

    public function test_despues_de_su_visita_le_pregunta_como_le_fue_y_guarda_su_respuesta(): void
    {
        $visita = $this->cita($this->ana, '2026-10-12 10:00', 'completed');

        Livewire::test(MiResumen::class)
            ->assertSee('¿Cómo le fue en su visita')
            ->call('calificar', $visita->id, 5, 'Muy amables')
            ->assertDontSee('¿Cómo le fue en su visita');

        $encuesta = Encuesta::sole();
        $this->assertSame(5, $encuesta->estrellas);
        $this->assertSame('Muy amables', $encuesta->comentario);
        $this->assertSame($this->doctor->id, $encuesta->doctor_id);
    }

    public function test_no_califica_dos_veces_ni_la_visita_de_otro_ni_fuera_de_1_a_5(): void
    {
        $visita = $this->cita($this->ana, '2026-10-12 10:00', 'completed');
        $deLuis = $this->cita($this->luis, '2026-10-12 11:00', 'completed');

        Livewire::test(MiResumen::class)->call('calificar', $visita->id, 9, null);
        Livewire::test(MiResumen::class)->call('calificar', $deLuis->id, 5, null);
        Livewire::test(MiResumen::class)->call('calificar', $visita->id, 4, null);
        Livewire::test(MiResumen::class)->call('calificar', $visita->id, 1, null);

        $this->assertSame(1, Encuesta::count());
        $this->assertSame(4, Encuesta::sole()->estrellas);
    }

    // ── Lo ve el consultorio ────────────────────────────────────

    public function test_el_consultorio_ve_el_promedio_y_los_comentarios(): void
    {
        $visita = $this->cita($this->ana, '2026-10-12 10:00', 'completed');
        Encuesta::create(['clinic_id' => $this->clinica->id, 'patient_id' => $this->ana->id, 'appointment_id' => $visita->id,
            'doctor_id' => $this->doctor->id, 'estrellas' => 4, 'comentario' => 'Me hicieron esperar un poco']);
        Filament::setCurrentPanel(Filament::getPanel('doctor'));
        $this->actingAs($this->doctorUser);

        Livewire::test(Opiniones::class)
            ->assertSee('4.0')
            ->assertSee('Me hicieron esperar un poco')
            ->assertSee('Ana Ruiz');
    }
}
