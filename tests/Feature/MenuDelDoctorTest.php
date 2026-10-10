<?php

namespace Tests\Feature;

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
 * El menú del doctor: quince renglones sueltos arriba, el odontograma lejos
 * de los pacientes, el dinero repartido en tres lugares y los ajustes antes
 * que el trabajo del día. Aquí queda el orden: lo de todos los días arriba,
 * luego por tema, y "Mi cuenta" hasta abajo y cerrado.
 */
class MenuDelDoctorTest extends TestCase
{
    use RefreshDatabase;

    private Clinic $clinica;

    protected function setUp(): void
    {
        parent::setUp();
        // A media mañana: una cita "en 20 minutos" cerca de la medianoche
        // caía al día siguiente y la prueba fallaba según la hora en que corría.
        $this->travelTo(today()->setTime(10, 0));
        // En prueba: trae todo lo de Pro y los presupuestos.
        $this->clinica = Clinic::create(['name' => 'Consultorio Test', 'plan' => 'free', 'trial_ends_at' => now()->addDays(10), 'onboarding_status' => 'completed']);
        $user = User::forceCreate(['name' => 'Dr. Test', 'email' => 'd@test.com', 'password' => bcrypt('x'), 'role' => 'doctor', 'email_verified_at' => now(), 'clinic_id' => $this->clinica->id]);
        Doctor::create(['user_id' => $user->id, 'clinic_id' => $this->clinica->id, 'specialty' => 'Odontología']);
        $this->actingAs($user);
        Filament::setCurrentPanel(Filament::getPanel('doctor'));
    }

    /** [etiqueta del grupo => [renglones]] */
    private function menu(): array
    {
        $menu = [];
        foreach (Filament::getNavigation() as $grupo) {
            $menu[$grupo->getLabel() ?? ''] = collect($grupo->getItems())->map->getLabel()->values()->all();
        }

        return $menu;
    }

    public function test_los_grupos_van_en_el_orden_del_dia_de_trabajo(): void
    {
        $this->assertSame(['', 'Pacientes', 'Dinero', 'Inventario', 'Consultorio', 'Mi cuenta'], array_keys($this->menu()));
    }

    public function test_arriba_solo_lo_de_todos_los_dias(): void
    {
        // Los recordatorios de mañana se mandan cada tarde (10-oct-2026): son de todos los días.
        $this->assertSame(['Escritorio', 'Consulta', 'Calendario', 'Citas', 'Recordatorios de mañana'], $this->menu()['']);
    }

    public function test_cada_cosa_en_su_tema(): void
    {
        $menu = $this->menu();

        // Pendientes (10-oct-2026): presupuestos sin respuesta y tratamientos a medias, por paciente.
        $this->assertSame(['Pacientes', 'Expediente clínico', 'Odontograma', 'Recetas', 'Consentimientos', 'Lista de espera', 'Pendientes'], $menu['Pacientes']);
        // Caja del día (10-oct-2026): lo que entró hoy por forma de pago, para cuadrar.
        $this->assertSame(['Caja del día', 'Cobros', 'Presupuestos', 'Planes de pago', 'Gastos', 'Corte del mes'], $menu['Dinero']);
        $this->assertSame(['Insumos', 'Movimientos', 'Residuos'], $menu['Inventario']);
        $this->assertContains('Servicios y precios', $menu['Consultorio']);
        $this->assertContains('Check-in QR', $menu['Consultorio']);
        $this->assertContains('Mi perfil profesional', $menu['Mi cuenta']);
        $this->assertContains('Mi plan', $menu['Mi cuenta']);
    }

    public function test_mi_cuenta_va_cerrado(): void
    {
        $grupo = collect(Filament::getNavigation())->first(fn ($g) => $g->getLabel() === 'Mi cuenta');

        $this->assertTrue($grupo->isCollapsed());
    }

    public function test_el_menu_avisa_lo_que_espera_al_doctor(): void
    {
        $paciente = Patient::create(['clinic_id' => $this->clinica->id, 'first_name' => 'Ana', 'last_name' => 'Ruiz']);
        $doctor = Doctor::first();
        \Illuminate\Support\Carbon::setTestNow(today()->setTime(11, 0));
        // La de las 9 ya pasó; faltan la de las 12 y la de las 13.
        foreach ([9, 12, 13] as $hora) {
            Appointment::create(['clinic_id' => $this->clinica->id, 'doctor_id' => $doctor->id, 'patient_id' => $paciente->id,
                'starts_at' => today()->setHour($hora), 'ends_at' => today()->setHour($hora + 1), 'status' => 'scheduled']);
        }
        Payment::create(['clinic_id' => $this->clinica->id, 'patient_id' => $paciente->id, 'amount' => 800, 'amount_paid' => 0,
            'status' => 'pending', 'payment_date' => now()->subDays(20), 'due_date' => now()->subDays(5)]);

        $badges = [];
        foreach (Filament::getNavigation() as $grupo) {
            foreach ($grupo->getItems() as $item) {
                $badges[$item->getLabel()] = $item->getBadge();
            }
        }

        $this->assertSame('2', (string) $badges['Citas']);
        $this->assertSame('1', (string) $badges['Cobros']);
        $this->assertNull($badges['Pacientes']);
        \Illuminate\Support\Carbon::setTestNow();
    }

    public function test_el_menu_dice_en_que_consultorio_esta_y_cuanto_le_queda_de_prueba(): void
    {
        $this->get('/doctor/citas')
            ->assertOk()
            ->assertSee('Consultorio Test')
            ->assertSee('Prueba: quedan 10 días');
    }

    public function test_con_plan_pagado_dice_cual_es(): void
    {
        $this->clinica->update(['plan' => 'profesional', 'trial_ends_at' => null, 'plan_ends_at' => now()->addMonth()]);

        $this->get('/doctor/citas')->assertOk()->assertSee('Plan Pro');
    }

    // ── El botón principal sabe a quién sigue ───────────────────

    private function citaHoy(string $estado, int $minutos, string $nombre = 'Ana Sofía'): Appointment
    {
        $paciente = Patient::create(['clinic_id' => $this->clinica->id, 'first_name' => $nombre, 'last_name' => 'Martínez']);

        return Appointment::create(['clinic_id' => $this->clinica->id, 'doctor_id' => Doctor::first()->id, 'patient_id' => $paciente->id,
            'starts_at' => now()->addMinutes($minutos), 'ends_at' => now()->addMinutes($minutos + 30), 'status' => $estado]);
    }

    public function test_el_boton_principal_abre_la_consulta_del_siguiente_paciente(): void
    {
        $cita = $this->citaHoy('confirmed', 20);

        $this->get('/doctor/citas')
            ->assertSee('Atender a Ana Sofía')
            ->assertSee('consulta?appointment=' . $cita->id, false);
    }

    public function test_si_ya_hay_una_consulta_abierta_el_boton_la_continua(): void
    {
        $this->citaHoy('confirmed', 30, 'Ana Sofía');
        $this->citaHoy('in_progress', -10, 'Diego');

        $this->get('/doctor/citas')->assertSee('Continuar con Diego')->assertDontSee('Atender a Ana Sofía');
    }

    public function test_sin_citas_pendientes_hoy_el_boton_es_nueva_consulta(): void
    {
        $this->citaHoy('confirmed', 60 * 24 * 2); // pasado mañana

        $this->get('/doctor/citas')->assertSee('Nueva consulta')->assertDontSee('Atender a');
    }
}
