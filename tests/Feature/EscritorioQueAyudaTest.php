<?php

namespace Tests\Feature;

use App\Filament\Doctor\Widgets;
use App\Models\Appointment;
use App\Models\Clinic;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Payment;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * El escritorio enseña lo que el doctor tiene que hacer hoy, en ese orden
 * (Omar, 12-oct-2026: "lo que más le ayude al doctor en su día a día, al
 * inicio"). Las gráficas de citas por semana, ingresos y servicios no decían
 * qué hacer y ocupaban el mejor lugar.
 */
class EscritorioQueAyudaTest extends TestCase
{
    use RefreshDatabase;

    private Clinic $clinica;
    private User $usuario;
    private Doctor $doctor;

    protected function setUp(): void
    {
        parent::setUp();
        $this->travelTo(\Carbon\Carbon::parse('2026-10-14 08:00')); // miércoles
        $this->clinica = Clinic::create(['name' => 'Consultorio Sonrisas', 'plan' => 'profesional', 'plan_ends_at' => now()->addMonth(), 'onboarding_status' => 'completed']);
        $this->usuario = User::forceCreate(['name' => 'Dr. Javier', 'email' => 'javier@test.com', 'password' => bcrypt('x'), 'role' => 'doctor', 'email_verified_at' => now(), 'clinic_id' => $this->clinica->id]);
        $this->doctor = Doctor::create(['user_id' => $this->usuario->id, 'clinic_id' => $this->clinica->id, 'specialty' => 'Odontología']);
        Filament::setCurrentPanel(Filament::getPanel('doctor'));
        $this->actingAs($this->usuario);
    }

    private function cita(string $inicio, string $fin): Appointment
    {
        $p = Patient::create(['clinic_id' => $this->clinica->id, 'first_name' => 'Rosa', 'last_name' => 'Ruiz', 'phone' => '6681234567']);

        return Appointment::create(['clinic_id' => $this->clinica->id, 'doctor_id' => $this->doctor->id, 'patient_id' => $p->id,
            'starts_at' => $inicio, 'ends_at' => $fin, 'status' => 'scheduled']);
    }

    /** @return list<class-string> los del escritorio, en el orden en que salen */
    private function enOrden(): array
    {
        return collect(Filament::getPanel('doctor')->getWidgets())
            ->sortBy(fn ($w) => $w::getSort())
            ->values()
            ->all();
    }

    public function test_las_graficas_y_los_numeros_sueltos_ya_no_estan(): void
    {
        $widgets = Filament::getPanel('doctor')->getWidgets();

        foreach (['AppointmentsChart', 'IncomeChart', 'TopServicesChart', 'StatsOverview'] as $fuera) {
            $this->assertNotContains('App\\Filament\\Doctor\\Widgets\\' . $fuera, $widgets, $fuera . ' sigue en el escritorio');
        }

        $this->get('/doctor')->assertOk()
            ->assertDontSee('Citas por semana')
            ->assertDontSee('Ingresos mensuales')
            ->assertDontSee('Servicios más solicitados');
    }

    public function test_primero_lo_de_hoy_y_hasta_abajo_su_mes(): void
    {
        $orden = $this->enOrden();
        $donde = fn ($w) => array_search($w, $orden, true);

        $this->assertLessThan($donde(Widgets\AlertsWidget::class), $donde(Widgets\TodayAppointments::class), 'Las citas de hoy van antes de los avisos');
        $this->assertLessThan($donde(Widgets\LeDebenWidget::class), $donde(Widgets\AlertsWidget::class), 'Lo que hay que atender va antes de lo que le deben');
        $this->assertLessThan($donde(Widgets\PendingRecallsWidget::class), $donde(Widgets\LeDebenWidget::class));
        $this->assertLessThan($donde(Widgets\SuMesWidget::class), $donde(Widgets\PendingRecallsWidget::class), 'Su mes va hasta abajo');
    }

    public function test_los_avisos_ocupan_todo_el_ancho_y_dicen_que_hay_que_atender(): void
    {
        $this->assertSame('full', (new Widgets\AlertsWidget())->getColumnSpan());

        \Livewire\Livewire::test(Widgets\AlertsWidget::class)->assertSee('Lo que hay que atender');
    }

    public function test_el_aviso_de_ingresos_de_hoy_ya_no_sale_porque_no_pide_nada(): void
    {
        $p = Patient::create(['clinic_id' => $this->clinica->id, 'first_name' => 'Ana', 'last_name' => 'Ruiz']);
        Payment::create(['clinic_id' => $this->clinica->id, 'patient_id' => $p->id, 'amount' => 600, 'amount_paid' => 600,
            'status' => 'paid', 'payment_method' => 'cash', 'payment_date' => today()]);

        $avisos = collect((new Widgets\AlertsWidget())->getAlerts())->pluck('title')->implode(' | ');

        $this->assertStringNotContainsString('Ingresos hoy', $avisos);
    }

    public function test_dice_los_huecos_de_los_proximos_dias_para_ofrecerlos(): void
    {
        // Jueves 15: abre 9 a 19 (horario típico). Citas 9-10 y 14-19.
        $this->cita('2026-10-15 09:00', '2026-10-15 10:00');
        $this->cita('2026-10-15 14:00', '2026-10-15 19:00');

        $aviso = collect((new Widgets\AlertsWidget())->getAlerts())->first(fn ($a) => str_contains($a['title'], 'libre'));

        $this->assertNotNull($aviso, 'No salió el aviso de huecos');
        $this->assertStringContainsString('mañana de 10:00 a 14:00', $aviso['title']);
        $this->assertStringContainsString('/doctor/calendario', $aviso['url']);
    }

    public function test_un_dia_sin_ninguna_cita_no_cuenta_como_hueco(): void
    {
        // Agenda vacía: decir "mañana libre de 9 a 19" no le dice nada nuevo.
        $aviso = collect((new Widgets\AlertsWidget())->getAlerts())->first(fn ($a) => str_contains($a['title'], 'libre'));

        $this->assertNull($aviso);
    }
}
