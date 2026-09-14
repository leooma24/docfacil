<?php

namespace Tests\Feature;

use App\Filament\Doctor\Pages\PerfilProfesional;
use App\Filament\Doctor\Resources\PrescriptionResource\Pages\ListPrescriptions;
use App\Models\Clinic;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Prescription;
use App\Models\PrescriptionItem;
use App\Models\User;
use App\Support\Receta;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * La receta lleva lo que pide la ley.
 *
 * Reglamento de Insumos para la Salud, arts. 29 a 31, y reglamento de
 * atención médica, arts. 64 y 65: cédula, institución que expidió el título,
 * cédula de especialidad, domicilio completo, firma autógrafa, genérico,
 * presentación, dosis, vía, frecuencia y duración.
 */
class RecetaCompletaTest extends TestCase
{
    use RefreshDatabase;

    private Clinic $clinica;

    private User $usuario;

    private Doctor $doctor;

    private Patient $paciente;

    protected function setUp(): void
    {
        parent::setUp();

        $this->clinica = Clinic::create([
            'name' => 'Consultorio Dental Norte',
            'slug' => 'dental-norte',
            'address' => 'Blvd. Rosales 123',
            'city' => 'Los Mochis',
            'state' => 'Sinaloa',
            'zip_code' => '81200',
            'phone' => '6681234567',
            'plan' => 'profesional',
            'plan_ends_at' => now()->addYear(),
            'onboarding_status' => 'completed',
        ]);

        $this->usuario = User::forceCreate([
            'name' => 'Dra. Laura Méndez',
            'email' => 'laura@test.com',
            'password' => bcrypt('password'),
            'role' => 'doctor',
            'email_verified_at' => now(),
            'clinic_id' => $this->clinica->id,
        ]);

        $this->doctor = Doctor::create([
            'user_id' => $this->usuario->id,
            'clinic_id' => $this->clinica->id,
            'specialty' => 'Endodoncia',
            'license_number' => '12345678',
            'institucion_titulo' => 'Universidad Autónoma de Sinaloa',
            'cedula_especialidad' => '87654321',
        ]);

        $this->paciente = Patient::create([
            'clinic_id' => $this->clinica->id,
            'first_name' => 'Fernando',
            'last_name' => 'Morales',
            'phone' => '6689876543',
        ]);
    }

    private function receta(): Prescription
    {
        $receta = Prescription::create([
            'clinic_id' => $this->clinica->id,
            'patient_id' => $this->paciente->id,
            'doctor_id' => $this->doctor->id,
            'prescription_date' => now()->toDateString(),
        ]);

        PrescriptionItem::create([
            'prescription_id' => $receta->id,
            'medication' => 'Amoxicilina',
            'presentacion' => 'Cápsulas de 500 mg',
            'dosage' => '1 cápsula',
            'via_administracion' => 'Oral',
            'frequency' => 'Cada 8 horas',
            'duration' => '7 días',
        ]);

        return $receta;
    }

    private function comoDoctor(): void
    {
        Filament::setCurrentPanel(Filament::getPanel('doctor'));
        $this->actingAs($this->usuario);
    }

    // ── El PDF ───────────────────────────────────────────────────

    public function test_el_pdf_lleva_lo_que_pide_la_ley(): void
    {
        $receta = $this->receta()->load(['patient', 'doctor.user', 'doctor.clinic', 'items']);

        $html = view('pdf.prescription', ['prescription' => $receta])->render();

        foreach ([
            'Céd. Prof. 12345678',
            'Título expedido por Universidad Autónoma de Sinaloa',
            'Céd. de Especialidad 87654321',
            'Blvd. Rosales 123, Los Mochis, Sinaloa, C.P. 81200',
            'Cápsulas de 500 mg',
            'Vía:</strong> Oral',
            'Firma autógrafa',
        ] as $debeDecir) {
            $this->assertStringContainsString($debeDecir, $html);
        }
    }

    public function test_presentacion_y_via_se_guardan(): void
    {
        $item = $this->receta()->items()->first();

        $this->assertSame('Cápsulas de 500 mg', $item->presentacion);
        $this->assertSame('Oral', $item->via_administracion);
    }

    // ── Sin los datos del doctor no se imprime ───────────────────

    public function test_sin_cedula_la_tabla_no_descarga_y_dice_que_falta(): void
    {
        $this->doctor->update(['license_number' => null]);
        $receta = $this->receta();

        $this->comoDoctor();

        Livewire::test(ListPrescriptions::class)
            ->callTableAction('download_pdf', $receta)
            ->assertNotified('A la receta le falta tu cédula profesional');
    }

    public function test_sin_institucion_la_liga_del_pdf_manda_a_completar_el_perfil(): void
    {
        $this->doctor->update(['institucion_titulo' => null]);
        $receta = $this->receta();

        $this->actingAs($this->usuario)
            ->get("/doctor/receta/{$receta->id}/pdf")
            ->assertRedirect(PerfilProfesional::getUrl(panel: 'doctor'))
            ->assertSessionHas('falta_para_receta');
    }

    public function test_con_todo_completo_la_liga_entrega_el_pdf(): void
    {
        $receta = $this->receta();

        $this->actingAs($this->usuario)
            ->get("/doctor/receta/{$receta->id}/pdf")
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');
    }

    // ── Mi perfil profesional ────────────────────────────────────

    public function test_el_doctor_completa_su_perfil(): void
    {
        $this->doctor->update(['license_number' => null, 'institucion_titulo' => null]);

        $this->comoDoctor();

        Livewire::test(PerfilProfesional::class)
            ->fillForm([
                'license_number' => '55556666',
                'institucion_titulo' => 'Universidad de Guadalajara',
                'specialty' => 'Ortodoncia',
                'cedula_especialidad' => '',
            ])
            ->call('guardar')
            ->assertHasNoFormErrors();

        $doctor = $this->doctor->fresh();

        $this->assertSame('55556666', $doctor->license_number);
        $this->assertSame('Universidad de Guadalajara', $doctor->institucion_titulo);
        $this->assertSame([], Receta::datosQueFaltan($doctor));
    }

    public function test_la_cedula_es_obligatoria_en_el_perfil(): void
    {
        $this->comoDoctor();

        Livewire::test(PerfilProfesional::class)
            ->fillForm(['license_number' => ''])
            ->call('guardar')
            ->assertHasFormErrors(['license_number' => 'required']);
    }

    // ── Medicamentos que no se recetan como cualquiera ───────────

    public function test_avisa_al_recetar_tramadol(): void
    {
        $this->assertStringContainsString('controlado', Receta::avisoDeControl('Tramadol 50 mg'));
    }

    public function test_avisa_que_la_morfina_no_va_en_receta_ordinaria(): void
    {
        $this->assertStringContainsString('recetario especial', Receta::avisoDeControl('Sulfato de morfina'));
    }

    public function test_avisa_de_los_antibioticos_con_acentos_y_mayusculas(): void
    {
        $this->assertStringContainsString('antibiótico', Receta::avisoDeControl('AMOXICILINA con ácido clavulánico'));
    }

    public function test_el_paracetamol_no_lleva_aviso(): void
    {
        $this->assertNull(Receta::avisoDeControl('Paracetamol'));
        $this->assertNull(Receta::avisoDeControl(''));
    }

    // ── La página pública ────────────────────────────────────────

    public function test_la_pagina_para_agendar_dice_la_cedula_y_la_institucion(): void
    {
        $this->get("/clinica/{$this->clinica->slug}/agendar")
            ->assertOk()
            ->assertSee('Céd. Prof. 12345678')
            ->assertSee('Universidad Autónoma de Sinaloa');
    }
}
