<?php

namespace Tests\Feature;

use App\Filament\Doctor\Resources\AppointmentResource;
use App\Models\Appointment;
use App\Models\Clinic;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Los listados muestran "nombre apellido" pero se apoyan en la relación
 * patient.first_name. Antes Filament solo buscaba en el nombre de pila, así
 * que teclear el apellido o el nombre completo no devolvía nada — justo lo
 * que hace un doctor cuando busca a un paciente.
 */
class PatientNameSearchTest extends TestCase
{
    use RefreshDatabase;

    private Patient $paciente;

    protected function setUp(): void
    {
        parent::setUp();

        $clinic = Clinic::create(['name' => 'Consultorio Test', 'onboarding_status' => 'completed']);
        $user = User::forceCreate([
            'name' => 'Dr. Test',
            'email' => 'doctor@test.com',
            'password' => bcrypt('password'),
            'role' => 'doctor',
            'email_verified_at' => now(),
            'clinic_id' => $clinic->id,
        ]);
        $doctor = Doctor::create([
            'user_id' => $user->id,
            'clinic_id' => $clinic->id,
            'specialty' => 'General',
        ]);

        $this->paciente = Patient::create([
            'clinic_id' => $clinic->id,
            'first_name' => 'María Elena',
            'last_name' => 'García López',
        ]);

        $otro = Patient::create([
            'clinic_id' => $clinic->id,
            'first_name' => 'Roberto',
            'last_name' => 'Sánchez Mora',
        ]);

        foreach ([$this->paciente, $otro] as $p) {
            Appointment::create([
                'clinic_id' => $clinic->id,
                'patient_id' => $p->id,
                'doctor_id' => $doctor->id,
                'starts_at' => now()->addDay(),
                'ends_at' => now()->addDay()->addMinutes(30),
                'status' => 'scheduled',
            ]);
        }

        Filament::setCurrentPanel(Filament::getPanel('doctor'));
        $this->actingAs($user);
    }

    public static function busquedas(): array
    {
        return [
            'nombre de pila'  => ['María'],
            'apellido'        => ['García'],
            'segundo apellido' => ['López'],
            'nombre completo' => ['María Elena García López'],
            'nombre y apellido' => ['María García'],
            'desordenado'     => ['García María'],
            'minúsculas'      => ['garcía'],
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('busquedas')]
    public function test_encuentra_la_cita_del_paciente(string $termino): void
    {
        Livewire::test(AppointmentResource\Pages\ListAppointments::class)
            ->set('tableSearch', $termino)
            ->assertCanSeeTableRecords(
                Appointment::where('patient_id', $this->paciente->id)->get()
            );
    }

    public function test_no_trae_pacientes_que_no_coinciden(): void
    {
        Livewire::test(AppointmentResource\Pages\ListAppointments::class)
            ->set('tableSearch', 'García')
            ->assertCanNotSeeTableRecords(
                Appointment::where('patient_id', '!=', $this->paciente->id)->get()
            );
    }
}
