<?php

namespace Tests\Feature;

use App\Filament\Doctor\Pages\Consultation;
use App\Filament\Doctor\Resources\AppointmentResource;
use App\Filament\Doctor\Resources\AppointmentResource\Pages\ListAppointments;
use App\Filament\Doctor\Resources\PatientResource\Pages\EditPatient;
use App\Filament\Doctor\Widgets\CalendarWidget;
use App\Models\Appointment;
use App\Models\Clinic;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\PatientFile;
use App\Models\Payment;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Que nada se marque solo ni se pierda (auditoría del 12-oct-2026):
 *
 * - tocar en el calendario una cita de otro día ya no la pone "En curso";
 *   la consulta solo marca "En curso" la cita de hoy y se puede salir sin
 *   atender;
 * - re-agendar borra el "ya se le recordó" y la confirmación de la fecha vieja;
 * - las citas no se borran en bloque, y un paciente con cobros o con fotos de
 *   su hoja vieja no se borra.
 */
class AgendaQueNoSeMarcaSolaTest extends TestCase
{
    use RefreshDatabase;

    private Clinic $clinica;
    private Doctor $doctor;
    private Patient $ana;

    protected function setUp(): void
    {
        parent::setUp();
        $this->travelTo(\Carbon\Carbon::parse('2026-10-14 10:00'));
        $this->clinica = Clinic::create(['name' => 'Consultorio Sonrisas', 'plan' => 'profesional', 'plan_ends_at' => now()->addMonth(), 'onboarding_status' => 'completed']);
        $user = User::forceCreate(['name' => 'Dr. Javier', 'email' => 'd@test.com', 'password' => bcrypt('x'), 'role' => 'doctor', 'email_verified_at' => now(), 'clinic_id' => $this->clinica->id]);
        $this->doctor = Doctor::create(['user_id' => $user->id, 'clinic_id' => $this->clinica->id, 'specialty' => 'Odontología']);
        $this->ana = Patient::create(['clinic_id' => $this->clinica->id, 'first_name' => 'Ana', 'last_name' => 'Ruiz', 'phone' => '6681234567']);
        Filament::setCurrentPanel(Filament::getPanel('doctor'));
        $this->actingAs($user);
    }

    private function cita(string $inicio, array $extra = []): Appointment
    {
        return Appointment::create(array_merge(['clinic_id' => $this->clinica->id, 'doctor_id' => $this->doctor->id, 'patient_id' => $this->ana->id,
            'starts_at' => $inicio, 'ends_at' => \Carbon\Carbon::parse($inicio)->addMinutes(30), 'status' => 'scheduled'], $extra));
    }

    // ── Calendario y consulta ───────────────────────────────────

    public function test_tocar_una_cita_de_otro_dia_la_abre_para_verla_y_no_la_pone_en_curso(): void
    {
        $jueves = $this->cita('2026-10-16 11:00');

        Livewire::test(CalendarWidget::class)
            ->call('onEventClick', ['id' => $jueves->id])
            ->assertRedirect(AppointmentResource::getUrl('edit', ['record' => $jueves]));

        $this->assertSame('scheduled', $jueves->fresh()->status);
    }

    public function test_tocar_la_cita_de_hoy_abre_la_consulta(): void
    {
        $hoy = $this->cita('2026-10-14 11:00');

        Livewire::test(CalendarWidget::class)
            ->call('onEventClick', ['id' => $hoy->id])
            ->assertRedirect(route('filament.doctor.pages.consulta', ['appointment' => $hoy->id]));
    }

    public function test_la_consulta_de_otro_dia_no_la_marca_en_curso(): void
    {
        $jueves = $this->cita('2026-10-16 11:00');

        Livewire::withQueryParams(['appointment' => $jueves->id])->test(Consultation::class);

        $this->assertSame('scheduled', $jueves->fresh()->status);
    }

    public function test_salir_sin_atender_la_regresa_como_estaba(): void
    {
        $confirmada = $this->cita('2026-10-14 11:00', ['status' => 'confirmed', 'confirmed_at' => '2026-10-13 18:00']);

        $consulta = Livewire::withQueryParams(['appointment' => $confirmada->id])->test(Consultation::class);
        $this->assertSame('in_progress', $confirmada->fresh()->status);

        $consulta->assertSee('Salir sin atender')->call('salirSinAtender');

        $this->assertSame('confirmed', $confirmada->fresh()->status);
    }

    // ── Re-agendar ──────────────────────────────────────────────

    public function test_reagendar_borra_el_recordatorio_y_la_confirmacion_de_la_fecha_vieja(): void
    {
        $cita = $this->cita('2026-10-15 09:00', ['status' => 'confirmed', 'confirmed_at' => '2026-10-14 08:00',
            'reminder_sent' => true, 'reminder_sent_at' => '2026-10-14 08:00']);

        $cita->update(['starts_at' => '2026-10-20 09:00', 'ends_at' => '2026-10-20 09:30']);

        $cita->refresh();
        $this->assertFalse($cita->reminder_sent);
        $this->assertNull($cita->reminder_sent_at);
        $this->assertNull($cita->confirmed_at);
        $this->assertSame('scheduled', $cita->status);
    }

    public function test_cambiar_solo_las_notas_no_borra_el_recordatorio(): void
    {
        $cita = $this->cita('2026-10-15 09:00', ['reminder_sent' => true, 'reminder_sent_at' => '2026-10-14 08:00']);

        $cita->update(['notes' => 'Trae sus radiografías']);

        $this->assertTrue($cita->fresh()->reminder_sent);
    }

    // ── Borrar ──────────────────────────────────────────────────

    public function test_las_citas_no_se_borran_en_bloque(): void
    {
        $cita = $this->cita('2026-10-15 09:00');

        Livewire::test(ListAppointments::class)->assertTableBulkActionDoesNotExist('delete');
        $this->assertNotNull($cita->fresh());
    }

    public function test_un_paciente_con_cobros_no_se_borra(): void
    {
        Payment::create(['clinic_id' => $this->clinica->id, 'patient_id' => $this->ana->id, 'amount' => 500, 'amount_paid' => 500,
            'status' => 'paid', 'payment_method' => 'cash', 'payment_date' => today()]);

        Livewire::test(EditPatient::class, ['record' => $this->ana->getRouteKey()])
            ->assertActionHidden('delete')
            ->assertActionVisible('no_se_borra');
    }

    public function test_un_paciente_con_fotos_de_su_hoja_vieja_no_se_borra(): void
    {
        PatientFile::create(['clinic_id' => $this->clinica->id, 'patient_id' => $this->ana->id, 'path' => 'patient-files/x.jpg',
            'nombre' => 'hoja.jpg', 'mime' => 'image/jpeg', 'size' => 10]);

        $this->assertTrue($this->ana->fresh()->tieneExpediente());
    }
}
