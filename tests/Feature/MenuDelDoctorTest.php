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
        $this->assertSame(['Escritorio', 'Consulta', 'Calendario', 'Citas'], $this->menu()['']);
    }

    public function test_cada_cosa_en_su_tema(): void
    {
        $menu = $this->menu();

        $this->assertSame(['Pacientes', 'Expediente clínico', 'Odontograma', 'Recetas', 'Consentimientos', 'Lista de espera'], $menu['Pacientes']);
        $this->assertSame(['Cobros', 'Presupuestos', 'Planes de pago', 'Gastos', 'Corte del mes'], $menu['Dinero']);
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
        foreach ([10, 12] as $hora) {
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
}
