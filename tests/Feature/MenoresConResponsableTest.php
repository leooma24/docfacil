<?php

namespace Tests\Feature;

use App\Filament\Doctor\Pages\PatientProfile;
use App\Filament\Doctor\Pages\RecordatoriosDeManana;
use App\Filament\Doctor\Resources\PatientResource\Pages\EditPatient;
use App\Models\Appointment;
use App\Models\Clinic;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Payment;
use App\Models\TreatmentPlan;
use App\Models\User;
use App\Support\RecordatorioDeCita;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Los niños y quien responde por ellos.
 *
 * Del dentista (10-oct-2026): "Mi contacto es el WhatsApp de la mamá… tengo
 * una familia con tres niños, cada uno con su expediente, pero el que paga es
 * el mismo papá y quiere saber cuánto debe en total. Lupita lo suma a mano".
 */
class MenoresConResponsableTest extends TestCase
{
    use RefreshDatabase;

    private Clinic $clinica;
    private User $usuario;
    private Doctor $doctor;
    private Patient $ana;
    private Patient $mateo;
    private Patient $sofia;

    protected function setUp(): void
    {
        parent::setUp();
        $this->travelTo(today()->setTime(16, 0));
        $this->clinica = Clinic::create(['name' => 'Consultorio Sonrisas', 'plan' => 'basico', 'plan_ends_at' => now()->addMonth(), 'onboarding_status' => 'completed']);
        $this->usuario = User::forceCreate(['name' => 'Dr. Javier', 'email' => 'javier@test.com', 'password' => bcrypt('x'), 'role' => 'doctor', 'email_verified_at' => now(), 'clinic_id' => $this->clinica->id]);
        $this->doctor = Doctor::create(['user_id' => $this->usuario->id, 'clinic_id' => $this->clinica->id, 'specialty' => 'Odontología']);
        $this->ana = Patient::create(['clinic_id' => $this->clinica->id, 'first_name' => 'Ana', 'last_name' => 'Ruiz', 'phone' => '6687770001']);
        $this->mateo = Patient::create(['clinic_id' => $this->clinica->id, 'first_name' => 'Mateo', 'last_name' => 'Ruiz', 'responsable_id' => $this->ana->id]);
        $this->sofia = Patient::create(['clinic_id' => $this->clinica->id, 'first_name' => 'Sofía', 'last_name' => 'Ruiz', 'phone' => '6680000099', 'responsable_id' => $this->ana->id]);
        Filament::setCurrentPanel(Filament::getPanel('doctor'));
        $this->actingAs($this->usuario);
    }

    private function cita(Patient $p, int $hora = 9): Appointment
    {
        return Appointment::create(['clinic_id' => $this->clinica->id, 'doctor_id' => $this->doctor->id, 'patient_id' => $p->id,
            'starts_at' => now()->addDay()->setTime($hora, 0), 'ends_at' => now()->addDay()->setTime($hora, 30), 'status' => 'scheduled']);
    }

    private function debe(Patient $p, float $monto): void
    {
        Payment::create(['clinic_id' => $this->clinica->id, 'patient_id' => $p->id, 'amount' => $monto, 'amount_paid' => 0,
            'status' => 'pending', 'payment_method' => 'cash', 'payment_date' => today()]);
    }

    // ── A quién se le escribe ───────────────────────────────────

    public function test_el_recordatorio_del_nino_le_llega_a_su_mama_y_la_nombra(): void
    {
        $liga = RecordatorioDeCita::ligaDeWhatsapp($this->cita($this->mateo));

        $this->assertStringStartsWith('https://wa.me/526687770001?text=', $liga);
        parse_str(parse_url($liga, PHP_URL_QUERY), $q);
        $this->assertStringContainsString('Hola Ana', $q['text']);
        $this->assertStringContainsString('la cita de Mateo', $q['text']);
    }

    public function test_aunque_el_nino_tenga_celular_el_contacto_es_su_responsable(): void
    {
        $this->assertSame('6687770001', $this->sofia->telefonoDeContacto());
        $this->assertSame('6687770001', $this->mateo->telefonoDeContacto());
        $this->assertSame('6687770001', $this->ana->telefonoDeContacto());
    }

    public function test_el_nino_sin_telefono_entra_a_la_fila_de_recordatorios(): void
    {
        $this->cita($this->mateo);

        Livewire::test(RecordatoriosDeManana::class)
            ->assertSee('Van 0 de 1')
            ->assertDontSee('Sin teléfono');
    }

    public function test_el_recordatorio_del_presupuesto_tambien_va_a_la_mama(): void
    {
        $plan = TreatmentPlan::create(['clinic_id' => $this->clinica->id, 'patient_id' => $this->mateo->id, 'doctor_id' => $this->doctor->id,
            'title' => 'Selladores', 'status' => 'sent', 'sent_at' => now()->subDays(10), 'subtotal' => 900, 'discount' => 0, 'total' => 900]);

        $this->get(route('plan.recordar', $plan))->assertRedirectContains('https://wa.me/526687770001');
    }

    // ── Cuánto debe la familia ──────────────────────────────────

    public function test_la_deuda_de_la_familia_suma_a_la_mama_y_sus_hijos(): void
    {
        $this->debe($this->ana, 500);
        $this->debe($this->mateo, 1500);
        $this->debe($this->sofia, 800);

        $familia = $this->ana->deudaFamiliar();

        $this->assertSame(2800.0, $familia['total']);
        $this->assertSame(['Ana' => 500.0, 'Mateo' => 1500.0, 'Sofía' => 800.0], $familia['porPersona']);
        $this->assertSame(2800.0, $this->mateo->deudaFamiliar()['total'], 'Desde el niño se ve lo de toda su familia.');
    }

    public function test_el_perfil_de_la_mama_dice_cuanto_debe_la_familia(): void
    {
        $this->debe($this->mateo, 1500);
        $this->debe($this->sofia, 800);

        Livewire::withQueryParams(['patient' => $this->ana->id])->test(PatientProfile::class)
            ->assertSee('La familia debe $2,300')
            ->assertSee('Mateo $1,500');
    }

    public function test_el_perfil_del_nino_dice_quien_es_su_responsable(): void
    {
        Livewire::withQueryParams(['patient' => $this->mateo->id])->test(PatientProfile::class)
            ->assertSee('Responsable: Ana Ruiz');
    }

    // ── Se captura en el formulario ─────────────────────────────

    public function test_el_formulario_guarda_al_responsable(): void
    {
        $nuevo = Patient::create(['clinic_id' => $this->clinica->id, 'first_name' => 'Leo', 'last_name' => 'Ruiz']);

        Livewire::test(EditPatient::class, ['record' => $nuevo->id])
            ->fillForm(['responsable_id' => $this->ana->id])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame($this->ana->id, $nuevo->fresh()->responsable_id);
    }

    public function test_no_se_puede_poner_de_responsable_a_alguien_de_otro_consultorio(): void
    {
        $otra = Clinic::create(['name' => 'Otra', 'plan' => 'basico', 'plan_ends_at' => now()->addMonth(), 'onboarding_status' => 'completed']);
        $ajena = Patient::withoutGlobalScopes()->create(['clinic_id' => $otra->id, 'first_name' => 'Ajena', 'last_name' => 'X', 'phone' => '6680000000']);

        Livewire::test(EditPatient::class, ['record' => $this->mateo->id])
            ->fillForm(['responsable_id' => $ajena->id])
            ->call('save')
            ->assertHasFormErrors(['responsable_id']);

        $this->assertSame($this->ana->id, $this->mateo->fresh()->responsable_id);
    }
}
