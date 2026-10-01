<?php

namespace Tests\Feature;

use App\Filament\Doctor\Pages\Consultation;
use App\Models\Appointment;
use App\Models\Clinic;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Service;
use App\Models\User;
use App\Support\WorkUnit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\URL;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Detalles que un dentista ve en los primeros cinco minutos y que le hacen
 * pensar "esto no es para mí": la receta que pierde la dosis, "por tooth",
 * un aviso en inglés, un botón de IA que solo da error.
 */
class DetallesDeConfianzaTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Clinic $clinic;
    private Doctor $doctor;
    private Patient $patient;

    protected function setUp(): void
    {
        parent::setUp();

        $this->clinic = Clinic::create(['name' => 'Consultorio Test', 'onboarding_status' => 'completed']);
        $this->user = User::forceCreate([
            'name' => 'Dr. Test',
            'email' => 'doctor@test.com',
            'password' => bcrypt('password'),
            'role' => 'doctor',
            'email_verified_at' => now(),
            'clinic_id' => $this->clinic->id,
        ]);
        $this->doctor = Doctor::create(['user_id' => $this->user->id, 'clinic_id' => $this->clinic->id, 'specialty' => 'Odontología']);
        $this->patient = Patient::create(['clinic_id' => $this->clinic->id, 'first_name' => 'Ana', 'last_name' => 'Ruiz']);
        $this->actingAs($this->user);
    }

    private function consulta(?Service $servicio = null)
    {
        $servicio ??= Service::create(['clinic_id' => $this->clinic->id, 'name' => 'Consulta general', 'price' => 300, 'duration_minutes' => 30, 'is_active' => true]);

        return Livewire::test(Consultation::class)
            ->set('data.walkin_patient_id', (string) $this->patient->id)
            ->set('data.walkin_service_id', (string) $servicio->id)
            ->call('startWalkIn');
    }

    private const AMOXICILINA = [
        'medication' => 'Amoxicilina', 'presentacion' => 'Cápsulas 500 mg', 'dosage' => '1 cápsula',
        'via_administracion' => 'oral', 'frequency' => 'Cada 8 horas', 'duration' => '7 días', 'instructions' => '',
    ];

    // ── Receta ──────────────────────────────────────────────────

    public function test_agregar_otro_medicamento_no_le_borra_la_dosis_al_primero(): void
    {
        $this->consulta()
            ->set('medications', [self::AMOXICILINA])
            ->call('agregarMedicamento')
            ->assertSet('medications.0', self::AMOXICILINA)
            ->assertSet('medications.1.medication', '')
            ->assertCount('medications', 2);
    }

    public function test_quitar_un_medicamento_deja_los_demas_completos(): void
    {
        $ibuprofeno = ['medication' => 'Ibuprofeno'] + self::AMOXICILINA;

        $this->consulta()
            ->set('medications', [self::AMOXICILINA, $ibuprofeno])
            ->call('quitarMedicamento', 0)
            ->assertSet('medications', [$ibuprofeno]);
    }

    public function test_la_pantalla_no_reescribe_los_medicamentos_con_una_copia_vieja(): void
    {
        // El botón mandaba al servidor la lista tal como estaba al dibujarse,
        // sin lo que el doctor acababa de escribir en frecuencia y días.
        $html = $this->consulta()->set('currentStep', 3)->set('medications', [self::AMOXICILINA])->html();

        $this->assertStringNotContainsString("\$set('medications'", $html);
        $this->assertStringContainsString('agregarMedicamento', $html);
    }

    // ── Cobro ───────────────────────────────────────────────────

    public function test_el_precio_por_diente_dice_por_diente(): void
    {
        $resina = Service::create(['clinic_id' => $this->clinic->id, 'name' => 'Resina (obturación)', 'price' => 600, 'unit' => WorkUnit::TOOTH, 'duration_minutes' => 40, 'is_active' => true]);

        $this->consulta()
            ->set('procedures', [['service_id' => (string) $resina->id, 'tooth_number' => '36', 'quantity' => 1]])
            ->set('currentStep', 4)
            ->assertSee('por diente')
            ->assertDontSee('por tooth');
    }

    // ── Confirmación del paciente ───────────────────────────────

    private function cita(string $estado): Appointment
    {
        return Appointment::create([
            'clinic_id' => $this->clinic->id,
            'doctor_id' => $this->doctor->id,
            'patient_id' => $this->patient->id,
            'starts_at' => now()->addDay(),
            'ends_at' => now()->addDay()->addHour(),
            'status' => $estado,
        ]);
    }

    private function liga(Appointment $cita, string $accion): string
    {
        return URL::temporarySignedRoute('appointment.confirm', now()->addDay(), ['appointment' => $cita->id, 'action' => $accion]);
    }

    public function test_el_paciente_que_ya_confirmo_todavia_puede_cancelar(): void
    {
        $cita = $this->cita('confirmed');

        $this->get($this->liga($cita, 'cancel'))->assertOk()->assertSee('Cita cancelada');

        $this->assertSame('cancelled', $cita->fresh()->status);
    }

    public function test_el_estado_de_la_cita_se_le_dice_al_paciente_en_espanol(): void
    {
        $cita = $this->cita('cancelled');

        $this->get($this->liga($cita, 'confirm'))
            ->assertOk()
            ->assertSee('Cancelada')
            ->assertDontSee('cancelled');
    }

    // ── IA apagada ──────────────────────────────────────────────

    public function test_sin_ia_no_se_ofrece_generar_el_consentimiento_con_ia(): void
    {
        config(['services.ai.enabled' => false]);
        $this->clinic->update(['plan' => 'profesional', 'plan_ends_at' => now()->addYear()]);
        \Filament\Facades\Filament::setCurrentPanel(\Filament\Facades\Filament::getPanel('doctor'));

        $pagina = Livewire::test(\App\Filament\Doctor\Resources\ConsentFormResource\Pages\CreateConsentForm::class)
            ->assertSee('Procedimiento');

        $this->assertStringNotContainsString('generate_with_ai', $pagina->html());
    }

    // ── Alergias ────────────────────────────────────────────────

    public function test_si_nadie_pregunto_las_alergias_no_dice_ninguna(): void
    {
        // "Ninguna" le dice al doctor que el paciente no es alérgico; si nadie
        // le preguntó, eso es falso y peligroso antes de recetar.
        \Filament\Facades\Filament::setCurrentPanel(\Filament\Facades\Filament::getPanel('doctor'));

        Livewire::withQueryParams(['patient' => $this->patient->id])
            ->test(\App\Filament\Doctor\Pages\PatientProfile::class)
            ->assertSee('No registradas')
            ->assertDontSee('Ninguna');
    }

    public function test_las_alergias_capturadas_se_ven_tal_cual(): void
    {
        $this->patient->update(['allergies' => 'Penicilina']);
        \Filament\Facades\Filament::setCurrentPanel(\Filament\Facades\Filament::getPanel('doctor'));

        Livewire::withQueryParams(['patient' => $this->patient->id])
            ->test(\App\Filament\Doctor\Pages\PatientProfile::class)
            ->assertSee('Penicilina');
    }

    public function test_el_resumen_de_la_consulta_cuenta_bien_los_medicamentos(): void
    {
        $this->consulta()
            ->set('medications', [self::AMOXICILINA])
            ->set('completed', true)
            ->set('currentStep', 6)
            ->assertSee('1 medicamento')
            ->assertDontSee('1 recetados');
    }
}
