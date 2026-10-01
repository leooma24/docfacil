<?php

namespace Tests\Feature;

use App\Filament\Doctor\Resources\WaitlistEntryResource\Pages\ListWaitlistEntries;
use App\Filament\Doctor\Widgets\AlertsWidget;
use App\Models\Appointment;
use App\Models\Clinic;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Service;
use App\Models\User;
use App\Models\WaitlistEntry;
use App\Support\RecordatorioDeCita;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Cuando alguien cancela, el aviso decía quién de la lista de espera cabía,
 * pero llevaba a la lista completa: al ofrecer había que volver a escribir
 * la fecha y la hora, y cuando el paciente decía que sí no había cómo
 * apartarle el lugar. Ahora el hueco viaja de punta a punta.
 */
class HuecoParaLaListaDeEsperaTest extends TestCase
{
    use RefreshDatabase;

    private Clinic $clinica;
    private User $user;
    private Doctor $doctor;
    private Appointment $cancelada;
    private WaitlistEntry $espera;

    protected function setUp(): void
    {
        parent::setUp();
        $this->clinica = Clinic::create(['name' => 'Consultorio Test', 'plan' => 'free', 'trial_ends_at' => now()->addDays(10), 'onboarding_status' => 'completed']);
        $this->user = User::forceCreate(['name' => 'Dr. Test', 'email' => 'd@test.com', 'password' => bcrypt('x'), 'role' => 'doctor', 'email_verified_at' => now(), 'clinic_id' => $this->clinica->id]);
        $this->doctor = Doctor::create(['user_id' => $this->user->id, 'clinic_id' => $this->clinica->id, 'specialty' => 'Odontología']);
        $limpieza = Service::create(['clinic_id' => $this->clinica->id, 'name' => 'Limpieza dental', 'price' => 500, 'duration_minutes' => 45, 'is_active' => true]);
        $quien = Patient::create(['clinic_id' => $this->clinica->id, 'first_name' => 'Paola', 'last_name' => 'Mendoza', 'phone' => '6681111111']);
        $this->cancelada = Appointment::create(['clinic_id' => $this->clinica->id, 'doctor_id' => $this->doctor->id, 'patient_id' => $quien->id, 'service_id' => $limpieza->id,
            'starts_at' => now()->addDay()->setTime(10, 0), 'ends_at' => now()->addDay()->setTime(10, 45), 'status' => 'confirmed']);
        $diego = Patient::create(['clinic_id' => $this->clinica->id, 'first_name' => 'Diego', 'last_name' => 'Salazar', 'phone' => '6682222222']);
        $this->espera = WaitlistEntry::create(['clinic_id' => $this->clinica->id, 'patient_id' => $diego->id, 'service_id' => $limpieza->id,
            'desired_from' => today(), 'desired_to' => today()->addDays(5), 'priority' => 0, 'status' => 'waiting']);
        // Alguien que no cabe ese día.
        WaitlistEntry::create(['clinic_id' => $this->clinica->id, 'patient_id' => Patient::create(['clinic_id' => $this->clinica->id, 'first_name' => 'Otro', 'last_name' => 'Fuera'])->id,
            'desired_from' => today()->addDays(10), 'desired_to' => today()->addDays(20), 'priority' => 0, 'status' => 'waiting']);
        Filament::setCurrentPanel(Filament::getPanel('doctor'));
    }

    public function test_si_el_paciente_cancela_desde_su_liga_el_aviso_lleva_al_hueco(): void
    {
        $this->get(RecordatorioDeCita::ligaDirecta($this->cancelada, 'cancel'))->assertOk();

        $aviso = $this->user->fresh()->notifications()->latest()->first();
        $this->assertNotNull($aviso);
        $this->assertStringContainsString('hueco=' . $this->cancelada->id, json_encode($aviso->data));
    }

    public function test_la_lista_abre_filtrada_para_el_hueco_y_dice_cual_es(): void
    {
        $this->cancelada->update(['status' => 'cancelled']);
        $this->actingAs($this->user);

        Livewire::withQueryParams(['hueco' => $this->cancelada->id])
            ->test(ListWaitlistEntries::class)
            ->assertSee('Se liberó')
            ->assertSee('10:00')
            ->assertCanSeeTableRecords([$this->espera])
            ->assertCountTableRecords(1);
    }

    public function test_ofrecer_trae_la_hora_del_hueco_y_apartar_crea_la_cita(): void
    {
        $this->cancelada->update(['status' => 'cancelled']);
        $this->actingAs($this->user);

        $lista = Livewire::withQueryParams(['hueco' => $this->cancelada->id])->test(ListWaitlistEntries::class);
        $lista->mountTableAction('whatsapp', $this->espera)
            ->assertTableActionDataSet(['slot_start' => $this->cancelada->starts_at->format('Y-m-d H:i:s')])
            ->callMountedTableAction();

        $this->assertSame($this->cancelada->id, $this->espera->fresh()->notified_for_appointment_id);

        Livewire::test(ListWaitlistEntries::class)->callTableAction('apartar', $this->espera->fresh());

        $nueva = Appointment::where('patient_id', $this->espera->patient_id)->first();
        $this->assertNotNull($nueva);
        $this->assertSame($this->cancelada->starts_at->format('Y-m-d H:i'), $nueva->starts_at->format('Y-m-d H:i'));
        $this->assertSame($this->cancelada->ends_at->format('Y-m-d H:i'), $nueva->ends_at->format('Y-m-d H:i'));
        $this->assertSame('booked', $this->espera->fresh()->status);
    }

    public function test_el_escritorio_avisa_del_hueco_con_candidatos(): void
    {
        $this->cancelada->update(['status' => 'cancelled']);
        $this->actingAs($this->user);

        $alerta = collect(Livewire::test(AlertsWidget::class)->instance()->getAlerts())->first(fn ($a) => str_starts_with($a['title'], 'Se liberó'));

        $this->assertNotNull($alerta);
        $this->assertStringContainsString('1 en lista de espera', $alerta['title']);
        $this->assertStringContainsString('hueco=' . $this->cancelada->id, urldecode($alerta['url']));
    }

    public function test_si_el_hueco_ya_se_lleno_no_avisa(): void
    {
        $this->cancelada->update(['status' => 'cancelled']);
        Appointment::create(['clinic_id' => $this->clinica->id, 'doctor_id' => $this->doctor->id, 'patient_id' => $this->espera->patient_id,
            'starts_at' => $this->cancelada->starts_at, 'ends_at' => $this->cancelada->ends_at, 'status' => 'scheduled']);
        $this->actingAs($this->user);

        $alerta = collect(Livewire::test(AlertsWidget::class)->instance()->getAlerts())->first(fn ($a) => str_starts_with($a['title'], 'Se liberó'));

        $this->assertNull($alerta);
    }
}
