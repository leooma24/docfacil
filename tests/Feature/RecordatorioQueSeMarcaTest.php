<?php

namespace Tests\Feature;

use App\Filament\Doctor\Resources\AppointmentResource\Pages\ListAppointments;
use App\Filament\Doctor\Widgets\AlertsWidget;
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
 * El botón de WhatsApp abría wa.me directo: DocFácil nunca se enteraba de que
 * el recordatorio ya salió. El aviso de "citas mañana sin recordatorio" no se
 * quitaba nunca, el doctor no sabía a quién ya le escribió, y la paciente con
 * tres citas mañana recibía tres mensajes.
 *
 * Ahora el botón pasa por DocFácil: marca como recordadas todas las citas de
 * ese paciente ese día, arma un solo mensaje con todas y abre WhatsApp.
 */
class RecordatorioQueSeMarcaTest extends TestCase
{
    use RefreshDatabase;

    private Clinic $clinica;
    private Doctor $doctor;
    private Patient $camila;

    protected function setUp(): void
    {
        parent::setUp();
        $this->clinica = Clinic::create(['name' => 'Consultorio Sonrisas', 'plan' => 'profesional', 'plan_ends_at' => now()->addYear(), 'onboarding_status' => 'completed']);
        $user = User::forceCreate(['name' => 'Dr. Test', 'email' => 'd@test.com', 'password' => bcrypt('x'), 'role' => 'doctor', 'email_verified_at' => now(), 'clinic_id' => $this->clinica->id]);
        $this->doctor = Doctor::create(['user_id' => $user->id, 'clinic_id' => $this->clinica->id, 'specialty' => 'Odontología']);
        $this->camila = Patient::create(['clinic_id' => $this->clinica->id, 'first_name' => 'Camila', 'last_name' => 'Ortega', 'phone' => '6681234567']);
        Filament::setCurrentPanel(Filament::getPanel('doctor'));
        $this->actingAs($user);
    }

    private function cita(Patient $paciente, int $hora, string $estado = 'scheduled', int $enDias = 1): Appointment
    {
        return Appointment::create([
            'clinic_id' => $this->clinica->id, 'doctor_id' => $this->doctor->id, 'patient_id' => $paciente->id,
            'starts_at' => now()->addDays($enDias)->setTime($hora, 0), 'ends_at' => now()->addDays($enDias)->setTime($hora, 30),
            'status' => $estado,
        ]);
    }

    // ── El botón ────────────────────────────────────────────────

    public function test_mandar_el_recordatorio_lo_deja_marcado_y_abre_whatsapp(): void
    {
        $cita = $this->cita($this->camila, 9);

        $respuesta = $this->get(route('cita.recordar', $cita));

        $respuesta->assertRedirect();
        $this->assertStringStartsWith('https://wa.me/526681234567?text=', $respuesta->headers->get('Location'));
        $this->assertTrue($cita->fresh()->reminder_sent);
    }

    public function test_un_solo_mensaje_para_todas_las_citas_del_dia(): void
    {
        $primera = $this->cita($this->camila, 9);
        $segunda = $this->cita($this->camila, 11);
        $otroDia = $this->cita($this->camila, 10, enDias: 3);

        $location = $this->get(route('cita.recordar', $primera))->headers->get('Location');
        $mensaje = urldecode(substr($location, strpos($location, 'text=') + 5));

        $this->assertStringContainsString('09:00', $mensaje);
        $this->assertStringContainsString('11:00', $mensaje);
        $this->assertSame(1, substr_count($mensaje, 'http'), 'una sola liga');
        $this->assertTrue($segunda->fresh()->reminder_sent, 'la otra cita del mismo día también queda recordada');
        $this->assertFalse($otroDia->fresh()->reminder_sent);
    }

    public function test_no_se_recuerda_la_cita_de_otro_consultorio(): void
    {
        $otra = Clinic::create(['name' => 'Otro', 'onboarding_status' => 'completed']);
        $ajeno = Patient::create(['clinic_id' => $otra->id, 'first_name' => 'X', 'last_name' => 'Y', 'phone' => '6680000000']);
        $cita = Appointment::create(['clinic_id' => $otra->id, 'doctor_id' => $this->doctor->id, 'patient_id' => $ajeno->id,
            'starts_at' => now()->addDay(), 'ends_at' => now()->addDay()->addHour(), 'status' => 'scheduled']);

        $this->get(route('cita.recordar', $cita->id))->assertNotFound();
        $this->assertFalse((bool) $cita->fresh()->reminder_sent);
    }

    public function test_la_lista_pasa_por_docfacil_y_dice_cuando_ya_se_recordo(): void
    {
        $cita = $this->cita($this->camila, 9);
        $lista = Livewire::test(ListAppointments::class);

        $lista->assertTableActionHasUrl('whatsapp', route('cita.recordar', $cita), $cita);

        $cita->update(['reminder_sent' => true]);
        Livewire::test(ListAppointments::class)->assertTableActionHasLabel('whatsapp', 'Recordado', $cita->fresh());
    }

    // ── El aviso del escritorio ─────────────────────────────────

    private function alertas(): array
    {
        return collect(Livewire::test(AlertsWidget::class)->instance()->getAlerts())->keyBy('title')->all();
    }

    public function test_el_aviso_cuenta_pacientes_y_no_a_los_que_ya_confirmaron_o_ya_se_recordaron(): void
    {
        $this->cita($this->camila, 9);
        $this->cita($this->camila, 11);
        $this->cita(Patient::create(['clinic_id' => $this->clinica->id, 'first_name' => 'Ya', 'last_name' => 'Confirmó', 'phone' => '6681111111']), 10, 'confirmed');
        $recordada = $this->cita(Patient::create(['clinic_id' => $this->clinica->id, 'first_name' => 'Ya', 'last_name' => 'Recordado', 'phone' => '6682222222']), 12);
        $recordada->update(['reminder_sent' => true]);

        $alertas = $this->alertas();

        $this->assertArrayHasKey('1 paciente mañana sin recordatorio', $alertas);
        $this->assertStringContainsString('sin_recordatorio', $alertas['1 paciente mañana sin recordatorio']['url']);
    }

    public function test_el_aviso_se_quita_al_mandar_el_recordatorio(): void
    {
        $cita = $this->cita($this->camila, 9);
        $this->get(route('cita.recordar', $cita));

        $this->assertEmpty(array_filter(array_keys($this->alertas()), fn ($t) => str_contains($t, 'sin recordatorio')));
    }

    public function test_el_filtro_del_aviso_deja_solo_a_quien_falta_recordarle(): void
    {
        $falta = $this->cita($this->camila, 9);
        $confirmada = $this->cita(Patient::create(['clinic_id' => $this->clinica->id, 'first_name' => 'Ana', 'last_name' => 'R', 'phone' => '6683333333']), 10, 'confirmed');

        Livewire::test(ListAppointments::class)
            ->removeTableFilter('upcoming')
            ->filterTable('sin_recordatorio', true)
            ->assertCanSeeTableRecords([$falta])
            ->assertCanNotSeeTableRecords([$confirmada]);
    }

    // ── Una liga para todo el día ───────────────────────────────

    public function test_confirmar_desde_la_liga_confirma_todas_las_citas_del_dia(): void
    {
        $primera = $this->cita($this->camila, 9);
        $segunda = $this->cita($this->camila, 11);
        auth()->logout();

        $this->get(\App\Support\RecordatorioDeCita::ligaDirecta($primera))
            ->assertOk()->assertSee('09:00')->assertSee('11:00');
        $this->get(\App\Support\RecordatorioDeCita::ligaDirecta($primera, 'confirm'))->assertOk();

        $this->assertSame('confirmed', $primera->fresh()->status);
        $this->assertSame('confirmed', $segunda->fresh()->status);
    }

    public function test_cancelar_desde_la_liga_cancela_todas_las_citas_del_dia(): void
    {
        $primera = $this->cita($this->camila, 9);
        $segunda = $this->cita($this->camila, 11);
        $otroDia = $this->cita($this->camila, 10, enDias: 3);
        auth()->logout();

        $this->get(\App\Support\RecordatorioDeCita::ligaDirecta($primera, 'cancel'))->assertOk();

        $this->assertSame('cancelled', $primera->fresh()->status);
        $this->assertSame('cancelled', $segunda->fresh()->status);
        $this->assertSame('scheduled', $otroDia->fresh()->status);
    }
}
