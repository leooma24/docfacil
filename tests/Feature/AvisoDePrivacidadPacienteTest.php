<?php

namespace Tests\Feature;

use App\Filament\Doctor\Resources\PatientResource\Pages\ListPatients;
use App\Models\Appointment;
use App\Models\Clinic;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\User;
use App\Support\AvisoDePrivacidad;
use Carbon\CarbonImmutable;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\URL;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * El paciente acepta el aviso de privacidad antes de dar sus datos de salud.
 *
 * La agenda pública y el check-in pedían alergias, tipo de sangre y motivo de
 * consulta sin aviso ni casilla. La ley de datos personales (DOF 20-mar-2025)
 * pide consentimiento expreso para datos de salud (art. 8) y el aviso en el
 * momento en que se piden (arts. 15 y 16).
 */
class AvisoDePrivacidadPacienteTest extends TestCase
{
    use RefreshDatabase;

    private Clinic $clinica;

    private User $usuario;

    private Doctor $doctor;

    protected function setUp(): void
    {
        parent::setUp();

        // Martes 8 de septiembre a las 11:00 queda en el futuro y en horario.
        $this->travelTo(CarbonImmutable::parse('2026-09-07 08:00'));

        $this->clinica = Clinic::create([
            'name' => 'Consultorio Sonrisas',
            'slug' => 'consultorio-sonrisas',
            'address' => 'Av. Obregón 123',
            'city' => 'Guadalajara',
            'phone' => '3312345678',
            'plan' => 'profesional',
            'plan_ends_at' => now()->addYear(),
            'onboarding_status' => 'completed',
        ]);

        $this->usuario = User::forceCreate([
            'name' => 'Dr. Roberto García',
            'email' => 'doctor@test.com',
            'password' => bcrypt('password'),
            'role' => 'doctor',
            'email_verified_at' => now(),
            'clinic_id' => $this->clinica->id,
        ]);

        $this->doctor = Doctor::create([
            'user_id' => $this->usuario->id,
            'clinic_id' => $this->clinica->id,
            'specialty' => 'Odontología',
        ]);
    }

    private function agendar(array $extra = [])
    {
        return $this->post("/clinica/{$this->clinica->slug}/agendar", array_merge([
            'first_name' => 'Lucía',
            'last_name' => 'Hernández',
            'phone' => '5599887766',
            'doctor_id' => $this->doctor->id,
            'preferred_at' => '2026-09-08 11:00',
        ], $extra));
    }

    private function checkIn(array $extra = [])
    {
        // La liga del check-in va firmada desde el QR del consultorio.
        $liga = \Illuminate\Support\Facades\URL::signedRoute('checkin.store', ['slug' => $this->clinica->slug]);

        return $this->post($liga, array_merge([
            'first_name' => 'Lucía',
            'last_name' => 'Hernández',
            'phone' => '5599887766',
            'allergies' => 'Penicilina',
            'reason_for_visit' => 'Me duele una muela',
        ], $extra));
    }

    private function paciente(): Patient
    {
        return Patient::create([
            'clinic_id' => $this->clinica->id,
            'first_name' => 'Fernando',
            'last_name' => 'Morales',
            'phone' => '5512345678',
        ]);
    }

    // ── El aviso ─────────────────────────────────────────────────

    public function test_el_aviso_del_consultorio_se_puede_leer(): void
    {
        $this->get("/clinica/{$this->clinica->slug}/aviso-de-privacidad")
            ->assertOk()
            ->assertSee('Aviso de privacidad de Consultorio Sonrisas')
            ->assertSee('Av. Obregón 123')
            ->assertSee('datos sensibles')
            ->assertSee('Estados Unidos')
            ->assertSee('Secretaría Anticorrupción y Buen Gobierno');
    }

    public function test_la_agenda_publica_enseña_el_aviso_corto_y_la_casilla(): void
    {
        $this->get("/clinica/{$this->clinica->slug}/agendar")
            ->assertOk()
            ->assertSee('Lee el aviso de privacidad completo')
            ->assertSee('name="acepta_aviso"', escape: false);
    }

    // ── Agenda pública ───────────────────────────────────────────

    public function test_sin_aceptar_el_aviso_no_se_agenda(): void
    {
        $this->agendar()->assertSessionHasErrors('acepta_aviso');

        $this->assertSame(0, Appointment::withoutGlobalScopes()->count());
        $this->assertSame(0, Patient::withoutGlobalScopes()->count());
    }

    public function test_al_agendar_queda_la_prueba_de_que_acepto(): void
    {
        $this->agendar(['acepta_aviso' => '1'])->assertSessionHasNoErrors();

        $paciente = Patient::withoutGlobalScopes()->firstOrFail();

        $this->assertNotNull($paciente->aviso_privacidad_aceptado_at);
        $this->assertSame(AvisoDePrivacidad::VERSION, $paciente->aviso_privacidad_version);
        $this->assertSame('agenda_publica', $paciente->aviso_privacidad_medio);
    }

    // ── Check-in ─────────────────────────────────────────────────

    public function test_el_check_in_no_guarda_alergias_sin_la_casilla(): void
    {
        $this->checkIn()->assertSessionHasErrors('acepta_aviso');

        $this->assertSame(0, Patient::withoutGlobalScopes()->count());
    }

    public function test_en_el_check_in_queda_la_prueba_de_que_acepto(): void
    {
        $this->checkIn(['acepta_aviso' => '1'])->assertOk();

        $this->assertSame('check_in', Patient::withoutGlobalScopes()->firstOrFail()->aviso_privacidad_medio);
    }

    public function test_el_paciente_que_regresa_al_check_in_tambien_queda_registrado(): void
    {
        Patient::create([
            'clinic_id' => $this->clinica->id,
            'first_name' => 'Lucía',
            'last_name' => 'Hernández',
            'phone' => '5599887766',
        ]);

        $this->checkIn(['acepta_aviso' => '1'])->assertOk();

        $this->assertTrue(AvisoDePrivacidad::aceptoElVigente(Patient::withoutGlobalScopes()->firstOrFail()));
    }

    // ── La liga para aceptarlo desde el celular ──────────────────

    public function test_sin_firma_la_liga_no_sirve(): void
    {
        $paciente = $this->paciente();

        $this->get("/clinica/{$this->clinica->slug}/aviso-de-privacidad/aceptar/{$paciente->id}")
            ->assertForbidden();
    }

    public function test_con_la_liga_firmada_el_paciente_acepta_desde_su_celular(): void
    {
        $paciente = $this->paciente();
        $liga = AvisoDePrivacidad::urlParaAceptar($paciente);

        $this->get($liga)->assertOk()->assertSee('Aceptar el aviso');

        $this->post($liga, ['acepta_aviso' => '1'])->assertOk()->assertSee('Listo, Fernando');

        $this->assertSame('liga', $paciente->fresh()->aviso_privacidad_medio);
    }

    public function test_sin_marcar_la_casilla_la_liga_no_registra_nada(): void
    {
        $paciente = $this->paciente();

        $this->post(AvisoDePrivacidad::urlParaAceptar($paciente), [])
            ->assertSessionHasErrors('acepta_aviso');

        $this->assertNull($paciente->fresh()->aviso_privacidad_aceptado_at);
    }

    public function test_la_liga_de_un_paciente_no_sirve_con_otro_consultorio(): void
    {
        $paciente = $this->paciente();

        $otro = Clinic::create([
            'name' => 'Otro',
            'slug' => 'otro',
            'plan' => 'profesional',
            'plan_ends_at' => now()->addYear(),
            'onboarding_status' => 'completed',
        ]);

        $liga = URL::temporarySignedRoute('aviso-privacidad.formulario', now()->addDay(), [
            'slug' => $otro->slug,
            'paciente' => $paciente->id,
        ]);

        $this->get($liga)->assertNotFound();
    }

    public function test_la_liga_vence(): void
    {
        $liga = AvisoDePrivacidad::urlParaAceptar($this->paciente());

        $this->travel(AvisoDePrivacidad::DIAS_VIGENCIA_LIGA + 1)->days();

        $this->get($liga)->assertForbidden();
    }

    // ── Lo que ve el doctor ──────────────────────────────────────

    public function test_al_paciente_sin_aviso_se_le_puede_mandar_por_whatsapp(): void
    {
        $paciente = $this->paciente();

        Filament::setCurrentPanel(Filament::getPanel('doctor'));
        $this->actingAs($this->usuario);

        Livewire::test(ListPatients::class)
            ->assertTableActionVisible('aviso_whatsapp', $paciente)
            ->assertTableActionVisible('aviso_en_papel', $paciente);

        AvisoDePrivacidad::registrarAceptacion($paciente, 'liga');

        Livewire::test(ListPatients::class)
            ->assertTableActionHidden('aviso_whatsapp', $paciente->fresh());
    }

    public function test_el_doctor_registra_que_lo_firmo_en_papel(): void
    {
        $paciente = $this->paciente();

        Filament::setCurrentPanel(Filament::getPanel('doctor'));
        $this->actingAs($this->usuario);

        Livewire::test(ListPatients::class)->callTableAction('aviso_en_papel', $paciente);

        $this->assertSame('consultorio', $paciente->fresh()->aviso_privacidad_medio);
    }

    public function test_el_mensaje_de_whatsapp_lleva_la_liga_firmada(): void
    {
        $url = urldecode(AvisoDePrivacidad::urlWhatsApp($this->paciente()));

        $this->assertStringStartsWith('https://wa.me/525512345678?text=', $url);
        $this->assertStringContainsString('Consultorio Sonrisas', $url);
        $this->assertStringContainsString('signature=', $url);
    }
}
