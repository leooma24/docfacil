<?php

namespace Tests\Feature;

use App\Filament\Doctor\Widgets\TodayAppointments;
use App\Models\Appointment;
use App\Models\Clinic;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\URL;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * El paciente se registraba con el QR de la sala de espera y ahí se quedaba:
 * la cita no se enteraba de que ya había llegado y el doctor tampoco. Ahora
 * el registro marca la llegada en su cita de hoy y le avisa al consultorio.
 */
class LlegadaPorQrTest extends TestCase
{
    use RefreshDatabase;

    private Clinic $clinica;
    private User $user;
    private Patient $ana;
    private Appointment $cita;

    protected function setUp(): void
    {
        parent::setUp();
        $this->clinica = Clinic::create(['name' => 'Consultorio Sonrisas', 'slug' => 'consultorio-sonrisas', 'plan' => 'basico',
            'plan_ends_at' => now()->addMonth(), 'trial_ends_at' => now()->subDay(), 'is_active' => true, 'onboarding_status' => 'completed']);
        $this->user = User::forceCreate(['name' => 'Dr. Test', 'email' => 'd@test.com', 'password' => bcrypt('x'), 'role' => 'doctor', 'email_verified_at' => now(), 'clinic_id' => $this->clinica->id]);
        $doctor = Doctor::create(['user_id' => $this->user->id, 'clinic_id' => $this->clinica->id, 'specialty' => 'Odontología']);
        $this->ana = Patient::create(['clinic_id' => $this->clinica->id, 'first_name' => 'Ana', 'last_name' => 'Ruiz', 'phone' => '6681234567']);
        $this->cita = Appointment::create(['clinic_id' => $this->clinica->id, 'doctor_id' => $doctor->id, 'patient_id' => $this->ana->id,
            'starts_at' => now()->addMinutes(20), 'ends_at' => now()->addMinutes(50), 'status' => 'confirmed']);
        Filament::setCurrentPanel(Filament::getPanel('doctor'));
    }

    private function registrarse(string $telefono, string $nombre = 'Ana', string $apellido = 'Ruiz')
    {
        return $this->post(URL::signedRoute('checkin.store', ['slug' => $this->clinica->slug]), [
            'first_name' => $nombre, 'last_name' => $apellido, 'phone' => $telefono, 'acepta_aviso' => '1',
        ]);
    }

    public function test_al_registrarse_con_el_qr_su_cita_de_hoy_queda_como_llegada(): void
    {
        $this->registrarse('6681234567')->assertOk();

        $this->assertNotNull($this->cita->fresh()->arrived_at);
    }

    public function test_el_doctor_recibe_el_aviso_con_liga_a_la_consulta(): void
    {
        $this->registrarse('6681234567');

        $aviso = $this->user->fresh()->notifications()->latest()->first();
        $this->assertNotNull($aviso);
        $this->assertStringContainsString('Ana Ruiz', json_encode($aviso->data, JSON_UNESCAPED_UNICODE));
        $this->assertStringContainsString('appointment=' . $this->cita->id, json_encode($aviso->data, JSON_UNESCAPED_SLASHES));
    }

    public function test_el_telefono_se_reconoce_aunque_lo_escriban_con_espacios(): void
    {
        $this->registrarse('668 123 4567');

        $this->assertNotNull($this->cita->fresh()->arrived_at);
    }

    public function test_no_marca_citas_de_otro_dia_ni_canceladas(): void
    {
        $this->cita->update(['starts_at' => now()->addDay(), 'ends_at' => now()->addDay()->addMinutes(30)]);
        $this->registrarse('6681234567');
        $this->assertNull($this->cita->fresh()->arrived_at);

        $this->cita->update(['starts_at' => now()->addMinutes(20), 'ends_at' => now()->addMinutes(50), 'status' => 'cancelled']);
        $this->registrarse('6681234567');
        $this->assertNull($this->cita->fresh()->arrived_at);
    }

    public function test_la_pantalla_del_paciente_no_dice_si_tenia_cita(): void
    {
        // Quien está frente al QR puede escribir cualquier teléfono: la
        // respuesta debe ser la misma, tenga cita o no.
        $conCita = $this->registrarse('6681234567')->getContent();
        $sinCita = $this->registrarse('6689999999', 'Luis', 'Mora')->getContent();

        $visible = fn (string $html) => trim(preg_replace('/\s+/', ' ', strip_tags(preg_replace('#<(style|script)\b.*?</\1>#si', '', $html))));
        $this->assertSame($visible($conCita), $visible($sinCita));
    }

    public function test_paciente_nuevo_avisa_que_esta_en_sala_de_espera(): void
    {
        $this->registrarse('6689999999', 'Luis', 'Mora');

        $aviso = $this->user->fresh()->notifications()->latest()->first();
        $this->assertNotNull($aviso);
        $this->assertStringContainsString('Luis Mora', json_encode($aviso->data, JSON_UNESCAPED_UNICODE));
    }

    public function test_recepcion_puede_marcar_la_llegada_sin_qr_y_la_agenda_lo_muestra(): void
    {
        $this->actingAs($this->user);

        Livewire::test(TodayAppointments::class)
            ->callTableAction('llego', $this->cita)
            ->assertSee('Llegó');

        $this->assertNotNull($this->cita->fresh()->arrived_at);
    }

    public function test_el_boton_principal_dice_que_ya_llego(): void
    {
        $this->cita->update(['arrived_at' => now()->subMinutes(5)]);
        $this->actingAs($this->user);

        $this->get('/doctor')->assertSee('Atender a Ana')->assertSee('Ya llegó');
    }
}
