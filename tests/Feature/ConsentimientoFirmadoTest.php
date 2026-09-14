<?php

namespace Tests\Feature;

use App\Filament\Doctor\Resources\ConsentFormResource\Pages\EditConsentForm;
use App\Filament\Doctor\Resources\ConsentFormResource\Pages\ListConsentForms;
use App\Models\Clinic;
use App\Models\ConsentForm;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Un consentimiento firmado ya no se toca.
 *
 * Antes se podía cambiar el texto después de la firma, o borrarlo, y la
 * fecha de firma solo se guardaba si alguien le daba "Marcar firmado".
 * NOM-004 (10.1.1) y NOM-013 (9.6): lo que el paciente autorizó se conserva
 * tal cual, con testigo en dental.
 */
class ConsentimientoFirmadoTest extends TestCase
{
    use RefreshDatabase;

    private const FIRMA = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNkYPhfDwAChwGA60e6kgAAAABJRU5ErkJggg==';

    private Clinic $clinica;

    private User $usuario;

    private Doctor $doctor;

    private Patient $paciente;

    protected function setUp(): void
    {
        parent::setUp();

        $this->clinica = Clinic::create([
            'name' => 'Consultorio Test',
            'slug' => 'consultorio-test',
            'plan' => 'profesional',
            'plan_ends_at' => now()->addMonth(),
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
            'license_number' => '12345678',
        ]);

        $this->paciente = Patient::create([
            'clinic_id' => $this->clinica->id,
            'first_name' => 'Fernando',
            'last_name' => 'Morales',
            'phone' => '5512345678',
        ]);
    }

    private function consentimiento(array $atributos = []): ConsentForm
    {
        return ConsentForm::create(array_merge([
            'clinic_id' => $this->clinica->id,
            'patient_id' => $this->paciente->id,
            'doctor_id' => $this->doctor->id,
            'title' => 'Consentimiento Informado',
            'procedure_name' => 'Extracción de tercer molar',
            'content' => '<p>Autorizo el procedimiento.</p>',
        ], $atributos));
    }

    private function comoDoctor(): void
    {
        Filament::setCurrentPanel(Filament::getPanel('doctor'));
        $this->actingAs($this->usuario);
    }

    // ── La firma ─────────────────────────────────────────────────

    public function test_al_firmar_se_guardan_la_fecha_y_la_hora_solas(): void
    {
        $this->travelTo(now()->setTime(12, 34));

        $firmado = $this->consentimiento(['signature' => self::FIRMA]);

        $this->assertSame(now()->format('Y-m-d H:i'), $firmado->signed_at->format('Y-m-d H:i'));
    }

    public function test_sin_firma_no_hay_fecha_de_firma(): void
    {
        $this->assertNull($this->consentimiento()->signed_at);
    }

    // ── Después de firmar ────────────────────────────────────────

    public function test_antes_de_firmar_si_se_puede_corregir(): void
    {
        $borrador = $this->consentimiento();

        $borrador->update(['content' => '<p>Texto corregido.</p>']);

        $this->assertSame('<p>Texto corregido.</p>', $borrador->fresh()->content);
    }

    public function test_firmado_ya_no_se_puede_cambiar_el_texto(): void
    {
        $firmado = $this->consentimiento(['signature' => self::FIRMA]);

        $this->expectException(\LogicException::class);

        $firmado->update(['content' => '<p>Otro texto.</p>']);
    }

    public function test_firmado_no_se_puede_borrar(): void
    {
        $firmado = $this->consentimiento(['signature' => self::FIRMA]);

        $this->expectException(\LogicException::class);

        $firmado->delete();
    }

    public function test_el_testigo_puede_firmar_despues_del_paciente(): void
    {
        $firmado = $this->consentimiento(['signature' => self::FIRMA]);

        $firmado->update(['testigo_nombre' => 'Laura Méndez', 'testigo_firma' => self::FIRMA]);

        $this->assertSame('Laura Méndez', $firmado->fresh()->testigo_nombre);
    }

    public function test_la_firma_del_testigo_tampoco_se_cambia_una_vez_puesta(): void
    {
        $firmado = $this->consentimiento([
            'signature' => self::FIRMA,
            'testigo_nombre' => 'Laura Méndez',
            'testigo_firma' => self::FIRMA,
        ]);

        $this->expectException(\LogicException::class);

        $firmado->update(['testigo_nombre' => 'Otra persona']);
    }

    // ── En pantalla ──────────────────────────────────────────────

    public function test_la_tabla_no_ofrece_editar_uno_firmado(): void
    {
        $firmado = $this->consentimiento(['signature' => self::FIRMA]);

        $this->comoDoctor();

        Livewire::test(ListConsentForms::class)
            ->assertTableActionHidden('edit', $firmado)
            ->assertTableActionVisible('firma_testigo', $firmado);
    }

    public function test_entrar_por_la_liga_a_uno_firmado_regresa_a_la_lista(): void
    {
        $firmado = $this->consentimiento(['signature' => self::FIRMA]);

        $this->comoDoctor();

        Livewire::test(EditConsentForm::class, ['record' => $firmado->getRouteKey()])
            ->assertRedirect();
    }

    public function test_el_pdf_dice_cuando_firmo_y_quien_fue_testigo(): void
    {
        $this->travelTo(now()->setTime(12, 34));

        $firmado = $this->consentimiento([
            'signature' => self::FIRMA,
            'testigo_nombre' => 'Laura Méndez',
            'testigo_firma' => self::FIRMA,
        ])->load(['patient', 'doctor.user', 'doctor.clinic']);

        $html = view('pdf.consent-form', ['consent' => $firmado])->render();

        $this->assertStringContainsString('Firmó el ' . now()->format('d/m/Y') . ' a las 12:34', $html);
        $this->assertStringContainsString('Laura Méndez', $html);
        $this->assertStringContainsString('Testigo', $html);
    }
}
