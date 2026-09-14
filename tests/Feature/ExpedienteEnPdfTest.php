<?php

namespace Tests\Feature;

use App\Filament\Doctor\Pages\PatientProfile;
use App\Models\Clinic;
use App\Models\ConsentForm;
use App\Models\Doctor;
use App\Models\MedicalRecord;
use App\Models\Patient;
use App\Models\Prescription;
use App\Models\PrescriptionItem;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Activitylog\Models\Activity;
use Tests\TestCase;

/**
 * El expediente del paciente sale completo en un PDF, y cada nota dice quién
 * la hizo y a qué hora.
 *
 * NOM-004 (5.10): fecha, hora y nombre de quien elabora la nota. Ley de datos
 * personales (art. 29) y NOM-013 (5.15): el paciente puede pedir su
 * información o un resumen clínico, y antes no había cómo sacarlo.
 */
class ExpedienteEnPdfTest extends TestCase
{
    use RefreshDatabase;

    private Clinic $clinica;

    private User $usuario;

    private Doctor $doctor;

    private Patient $paciente;

    protected function setUp(): void
    {
        parent::setUp();

        $this->travelTo(now()->setTime(12, 34));

        $this->clinica = Clinic::create([
            'name' => 'Consultorio Test',
            'slug' => 'consultorio-test',
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
            'license_number' => '12345678',
        ]);

        $this->paciente = Patient::create([
            'clinic_id' => $this->clinica->id,
            'first_name' => 'Fernando',
            'last_name' => 'Morales',
            'phone' => '5512345678',
            'allergies' => 'Penicilina',
            'blood_type' => 'O+',
        ]);
    }

    private function nota(): MedicalRecord
    {
        return MedicalRecord::create([
            'clinic_id' => $this->clinica->id,
            'patient_id' => $this->paciente->id,
            'doctor_id' => $this->doctor->id,
            'visit_date' => now()->toDateString(),
            'chief_complaint' => 'Dolor de muela',
            'diagnosis' => 'Caries profunda',
            'treatment' => 'Resina',
        ]);
    }

    private function expedienteCompleto(): void
    {
        $this->nota();

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
        ]);

        ConsentForm::create([
            'clinic_id' => $this->clinica->id,
            'patient_id' => $this->paciente->id,
            'doctor_id' => $this->doctor->id,
            'title' => 'Consentimiento Informado',
            'content' => '<p>Autorizo.</p>',
            'signature' => 'data:image/png;base64,AAAA',
            'testigo_nombre' => 'Laura Méndez',
        ]);
    }

    private function comoDoctor(): void
    {
        Filament::setCurrentPanel(Filament::getPanel('doctor'));
        $this->actingAs($this->usuario);
    }

    public function test_la_nota_dice_quien_la_hizo_con_cedula_fecha_y_hora(): void
    {
        $this->assertSame(
            'Dr. Roberto García · Céd. Prof. 12345678 · ' . now()->format('d/m/Y') . ' a las 12:34',
            $this->nota()->fresh()->autoria(),
        );
    }

    public function test_el_resumen_trae_todo_el_expediente(): void
    {
        $this->expedienteCompleto();

        $paciente = $this->paciente->load(['clinic', 'medicalRecords.doctor.user', 'prescriptions.items', 'prescriptions.doctor.user', 'consentForms', 'odontograms']);

        $html = view('pdf.expediente', ['patient' => $paciente])->render();

        foreach ([
            'Fernando Morales', 'Penicilina', 'O+',
            'Caries profunda', 'Elaboró: Dr. Roberto García · Céd. Prof. 12345678',
            'Amoxicilina', 'Cápsulas de 500 mg', 'vía oral',
            'Consentimiento Informado', 'Firmado el', 'Testigo: Laura Méndez',
            'Pendiente de aceptar',
        ] as $debeDecir) {
            $this->assertStringContainsString($debeDecir, $html);
        }
    }

    public function test_el_doctor_lo_descarga_desde_el_perfil_y_queda_registrado(): void
    {
        $this->expedienteCompleto();
        $this->comoDoctor();

        Livewire::withQueryParams(['patient' => $this->paciente->id])
            ->test(PatientProfile::class)
            ->callAction('descargar_expediente')
            ->assertFileDownloaded('expediente-fernando-morales.pdf');

        $this->assertTrue(Activity::where('description', 'Descargó el expediente en PDF')
            ->where('subject_id', $this->paciente->id)
            ->where('causer_id', $this->usuario->id)
            ->exists());
    }
}
