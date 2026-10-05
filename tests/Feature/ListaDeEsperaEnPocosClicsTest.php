<?php

namespace Tests\Feature;

use App\Filament\Doctor\Resources\AppointmentResource\Pages\ListAppointments;
use App\Models\Appointment;
use App\Models\Clinic;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Service;
use App\Models\User;
use App\Models\WaitlistEntry;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Se cancela una cita y el hueco se ofrece desde el mismo aviso.
 *
 * Octubre 2026: eran 8 clics. Cancelar, confirmar, "Ofrecer el horario", la
 * lista de espera, "Ofrecer slot", la ventana con la fecha y, cuando el
 * paciente contestaba, "Apartar" y confirmar. Ahora el aviso de la
 * cancelación trae "Ofrecer a Diego", que abre WhatsApp con el mensaje y
 * deja anotado para qué hueco fue. Y el aviso sale igual se cancele desde
 * donde se cancele, con los mismos candidatos.
 */
class ListaDeEsperaEnPocosClicsTest extends TestCase
{
    use RefreshDatabase;

    private Clinic $clinica;
    private User $user;
    private User $recepcion;
    private Doctor $doctor;
    private Appointment $cita;
    private WaitlistEntry $diego;

    protected function setUp(): void
    {
        parent::setUp();
        // La lista de espera viene en Pro.
        $this->travelTo(today()->setTime(9, 0));
        $this->clinica = Clinic::create(['name' => 'Consultorio Sonrisas', 'plan' => 'profesional', 'plan_ends_at' => now()->addMonth(),
            'trial_ends_at' => now()->subDay(), 'is_active' => true, 'onboarding_status' => 'completed']);
        $this->user = User::forceCreate(['name' => 'Dr. Test', 'email' => 'd@test.com', 'password' => bcrypt('x'), 'role' => 'doctor',
            'email_verified_at' => now(), 'clinic_id' => $this->clinica->id]);
        $this->recepcion = User::forceCreate(['name' => 'Recepción', 'email' => 'r@test.com', 'password' => bcrypt('x'), 'role' => 'staff',
            'email_verified_at' => now(), 'clinic_id' => $this->clinica->id]);
        $this->doctor = Doctor::create(['user_id' => $this->user->id, 'clinic_id' => $this->clinica->id, 'specialty' => 'Odontología']);
        $limpieza = Service::create(['clinic_id' => $this->clinica->id, 'name' => 'Limpieza', 'price' => 500, 'duration_minutes' => 45, 'is_active' => true]);
        $paola = Patient::create(['clinic_id' => $this->clinica->id, 'first_name' => 'Paola', 'last_name' => 'Mendoza', 'phone' => '6681111111']);
        // A diez días: la cancelación de la semana que entra también deja hueco.
        $this->cita = Appointment::create(['clinic_id' => $this->clinica->id, 'doctor_id' => $this->doctor->id, 'patient_id' => $paola->id,
            'service_id' => $limpieza->id, 'starts_at' => today()->addDays(10)->setTime(10, 0), 'ends_at' => today()->addDays(10)->setTime(10, 45), 'status' => 'confirmed']);
        $diego = Patient::create(['clinic_id' => $this->clinica->id, 'first_name' => 'Diego', 'last_name' => 'Salazar', 'phone' => '6682222222']);
        $this->diego = WaitlistEntry::create(['clinic_id' => $this->clinica->id, 'patient_id' => $diego->id,
            'desired_from' => today()->addDays(7), 'desired_to' => today()->addDays(14), 'priority' => 0, 'status' => 'waiting']);
        Filament::setCurrentPanel(Filament::getPanel('doctor'));
    }

    private function ligaParaOfrecer(): string
    {
        return route('lista-espera.ofrecer', ['entrada' => $this->diego->id, 'cita' => $this->cita->id]);
    }

    // ── El aviso trae "Ofrecer a ..." ────────────────────────────

    public function test_cancelar_desde_la_lista_avisa_con_ofrecer_a_cada_candidato(): void
    {
        $this->actingAs($this->user);

        Livewire::test(ListAppointments::class)->callTableAction('cancel', $this->cita);

        $avisos = json_encode(session('filament.notifications'), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $this->assertStringContainsString('Ofrecer a Diego', $avisos);
        $this->assertStringContainsString($this->ligaParaOfrecer(), $avisos);
    }

    public function test_cancelar_desde_otra_pantalla_avisa_igual_a_los_demas_del_consultorio(): void
    {
        $this->actingAs($this->user);

        $this->cita->update(['status' => 'cancelled']);

        $aviso = $this->recepcion->fresh()->notifications()->latest()->first();
        $this->assertNotNull($aviso);
        $datos = json_encode($aviso->data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $this->assertStringContainsString('Ofrecer a Diego', $datos);
        $this->assertStringContainsString($this->ligaParaOfrecer(), $datos);
        // Al que canceló ya se le avisó en pantalla: no se le duplica en la campana.
        $this->assertSame(0, $this->user->fresh()->notifications()->count());
    }

    public function test_el_que_pidio_otro_doctor_no_es_candidato_en_ningun_aviso(): void
    {
        $otroDoctor = Doctor::create(['user_id' => $this->recepcion->id, 'clinic_id' => $this->clinica->id, 'specialty' => 'Ortodoncia']);
        $this->diego->update(['doctor_id' => $otroDoctor->id]);
        $this->actingAs($this->user);

        $this->cita->update(['status' => 'cancelled']);

        $this->assertSame(0, $this->recepcion->fresh()->notifications()->count());
    }

    // ── "Ofrecer a Diego" ────────────────────────────────────────

    public function test_ofrecer_abre_whatsapp_con_el_hueco_y_lo_deja_anotado(): void
    {
        $this->cita->update(['status' => 'cancelled']);
        $this->actingAs($this->user);

        $respuesta = $this->get($this->ligaParaOfrecer());

        $respuesta->assertRedirect();
        $whatsapp = urldecode($respuesta->headers->get('Location'));
        $this->assertStringStartsWith('https://wa.me/526682222222', $whatsapp);
        $this->assertStringContainsString('10:00', $whatsapp);
        $this->assertStringContainsString('Consultorio Sonrisas', $whatsapp);
        // De usted, como todo lo que sale a pacientes.
        $this->assertStringContainsString('Si le acomoda', $whatsapp);

        $this->diego->refresh();
        $this->assertSame('notified', $this->diego->status);
        $this->assertSame($this->cita->id, $this->diego->notified_for_appointment_id);
    }

    public function test_no_se_ofrece_una_cita_que_no_se_cancelo(): void
    {
        $this->actingAs($this->user);

        $this->get($this->ligaParaOfrecer())->assertNotFound();
        $this->assertSame('waiting', $this->diego->fresh()->status);
    }

    public function test_no_se_ofrece_el_hueco_de_otro_consultorio(): void
    {
        $this->cita->update(['status' => 'cancelled']);
        $otra = Clinic::create(['name' => 'Otro', 'onboarding_status' => 'completed']);
        $ajeno = User::forceCreate(['name' => 'Otro', 'email' => 'o@test.com', 'password' => bcrypt('x'), 'role' => 'doctor',
            'email_verified_at' => now(), 'clinic_id' => $otra->id]);
        $this->actingAs($ajeno);

        $this->get($this->ligaParaOfrecer())->assertNotFound();
    }

    public function test_sin_sesion_no_se_ofrece(): void
    {
        $this->cita->update(['status' => 'cancelled']);
        auth()->logout();

        $this->get($this->ligaParaOfrecer())->assertRedirect();
        $this->assertSame('waiting', $this->diego->fresh()->status);
    }
}
