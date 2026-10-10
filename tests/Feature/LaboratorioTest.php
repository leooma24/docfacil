<?php

namespace Tests\Feature;

use App\Filament\Doctor\Resources\AppointmentResource\Pages\ListAppointments;
use App\Filament\Doctor\Resources\LabOrderResource;
use App\Filament\Doctor\Resources\LabOrderResource\Pages\CreateLabOrder;
use App\Filament\Doctor\Resources\LabOrderResource\Pages\ListLabOrders;
use App\Filament\Doctor\Widgets\AlertsWidget;
use App\Models\Appointment;
use App\Models\Clinic;
use App\Models\Doctor;
use App\Models\Expense;
use App\Models\LabOrder;
use App\Models\Patient;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * La libreta del laboratorio, bien hecha.
 *
 * Del dentista (10-oct-2026): "La impresión la mando con el mensajero del
 * laboratorio… me la regresan en 8 o 10 días. ¿Cómo sé si ya llegó? Pues
 * cuando llega el mensajero con la cajita… Ya nos ha pasado que la paciente
 * llega a su cita y la corona no está". Y al laboratorio le paga cada quincena
 * sin saber bien cuánto le debe.
 */
class LaboratorioTest extends TestCase
{
    use RefreshDatabase;

    private Clinic $clinica;
    private User $usuario;
    private Doctor $doctor;
    private Patient $rosa;

    protected function setUp(): void
    {
        parent::setUp();
        $this->travelTo(\Carbon\Carbon::parse('2026-10-12 10:00'));   // lunes
        $this->clinica = Clinic::create(['name' => 'Consultorio Sonrisas', 'plan' => 'basico', 'plan_ends_at' => now()->addMonth(), 'onboarding_status' => 'completed']);
        $this->usuario = User::forceCreate(['name' => 'Dr. Javier', 'email' => 'javier@test.com', 'password' => bcrypt('x'), 'role' => 'doctor', 'email_verified_at' => now(), 'clinic_id' => $this->clinica->id]);
        $this->doctor = Doctor::create(['user_id' => $this->usuario->id, 'clinic_id' => $this->clinica->id, 'specialty' => 'Odontología']);
        $this->rosa = Patient::create(['clinic_id' => $this->clinica->id, 'first_name' => 'Rosa', 'last_name' => 'Valenzuela', 'phone' => '6681234567']);
        Filament::setCurrentPanel(Filament::getPanel('doctor'));
        $this->actingAs($this->usuario);
    }

    private function cita(int $enDias, int $hora = 10): Appointment
    {
        return Appointment::create(['clinic_id' => $this->clinica->id, 'doctor_id' => $this->doctor->id, 'patient_id' => $this->rosa->id,
            'starts_at' => now()->addDays($enDias)->setTime($hora, 0), 'ends_at' => now()->addDays($enDias)->setTime($hora, 30), 'status' => 'scheduled']);
    }

    private function orden(array $datos = []): LabOrder
    {
        return LabOrder::create(array_merge([
            'clinic_id' => $this->clinica->id, 'patient_id' => $this->rosa->id, 'laboratorio' => 'Lab Dental Mochis',
            'trabajo' => 'Corona de zirconia', 'diente' => '36', 'costo' => 1800, 'enviada_at' => today()->subDays(5),
        ], $datos));
    }

    // ── La orden ────────────────────────────────────────────────

    public function test_el_formulario_crea_la_orden_de_su_consultorio(): void
    {
        $cita = $this->cita(10);

        Livewire::test(CreateLabOrder::class)
            ->fillForm(['patient_id' => $this->rosa->id, 'trabajo' => 'Corona de zirconia', 'diente' => '36', 'color' => 'A2',
                'laboratorio' => 'Lab Dental Mochis', 'costo' => 1800, 'enviada_at' => today()->toDateString(),
                'prometida_para' => today()->addDays(8)->toDateString(), 'appointment_id' => $cita->id])
            ->call('create')
            ->assertHasNoFormErrors();

        $orden = LabOrder::firstOrFail();
        $this->assertSame($this->clinica->id, $orden->clinic_id);
        $this->assertSame($cita->id, $orden->appointment_id);
        $this->assertSame('enviada', $orden->estado());
    }

    public function test_no_deja_elegir_paciente_de_otro_consultorio(): void
    {
        $otra = Clinic::create(['name' => 'Otra', 'plan' => 'basico', 'plan_ends_at' => now()->addMonth(), 'onboarding_status' => 'completed']);
        $ajena = Patient::withoutGlobalScopes()->create(['clinic_id' => $otra->id, 'first_name' => 'Ajena', 'last_name' => 'X']);

        Livewire::test(CreateLabOrder::class)
            ->fillForm(['patient_id' => $ajena->id, 'trabajo' => 'Corona', 'laboratorio' => 'Lab', 'costo' => 100, 'enviada_at' => today()->toDateString()])
            ->call('create')
            ->assertHasFormErrors(['patient_id']);

        $this->assertSame(0, LabOrder::withoutGlobalScopes()->count());
    }

    // ── Llegó, entregada, pagada ────────────────────────────────

    public function test_llego_y_entregada_dejan_su_fecha(): void
    {
        $orden = $this->orden();

        Livewire::test(ListLabOrders::class)->callTableAction('llego', $orden);
        $this->assertSame('llego', $orden->fresh()->estado());

        Livewire::test(ListLabOrders::class)->callTableAction('entregada', $orden->fresh());
        $this->assertSame('entregada', $orden->fresh()->estado());
    }

    public function test_pagada_lo_anota_como_gasto_de_laboratorio_una_sola_vez(): void
    {
        $orden = $this->orden(['llego_at' => now()]);

        $orden->marcarPagada();
        $orden->fresh()->marcarPagada();

        $this->assertSame(1, Expense::count());
        $gasto = Expense::firstOrFail();
        $this->assertSame('laboratorio', $gasto->category);
        $this->assertSame(1800.0, (float) $gasto->amount);
        $this->assertSame('Lab Dental Mochis', $gasto->supplier);
        $this->assertStringContainsString('Rosa', $gasto->concept);
        $this->assertSame($gasto->id, $orden->fresh()->expense_id);
        $this->assertSame(1800.0, Expense::totalEntre($this->clinica->id, now()->startOfMonth(), now()->endOfMonth()), 'Cae en el corte del mes.');
    }

    public function test_lo_que_se_le_debe_al_laboratorio(): void
    {
        $this->orden(['costo' => 1800]);
        $this->orden(['costo' => 900, 'laboratorio' => 'Ortolab']);
        $this->orden(['costo' => 5000])->marcarPagada();

        $deuda = LabOrder::loQueSeDebe($this->clinica->id);

        $this->assertSame(2700.0, $deuda['total']);
        $this->assertSame(['Lab Dental Mochis' => 1800.0, 'Ortolab' => 900.0], $deuda['porLaboratorio']);
    }

    // ── Que no llegue la paciente y la corona no esté ───────────

    public function test_avisa_si_la_cita_es_en_pocos_dias_y_no_ha_llegado(): void
    {
        $this->orden(['appointment_id' => $this->cita(2)->id]);

        $avisos = collect((new AlertsWidget())->getAlerts())->pluck('title')->implode(' | ');

        $this->assertStringContainsString('Corona de zirconia de Rosa no ha llegado', $avisos);
    }

    public function test_si_ya_llego_o_la_cita_es_lejos_no_avisa(): void
    {
        $this->orden(['appointment_id' => $this->cita(2)->id, 'llego_at' => now()]);
        $this->orden(['appointment_id' => $this->cita(10)->id]);

        $avisos = collect((new AlertsWidget())->getAlerts())->pluck('title')->implode(' | ');

        $this->assertStringNotContainsString('no ha llegado', $avisos);
    }

    public function test_avisa_los_atrasados(): void
    {
        $orden = $this->orden(['prometida_para' => today()->subDays(2)]);
        $this->orden(['prometida_para' => today()->addDays(2)]);

        $this->assertTrue($orden->atrasada());
        $avisos = collect((new AlertsWidget())->getAlerts())->pluck('title')->implode(' | ');
        $this->assertStringContainsString('1 trabajo atrasado con el laboratorio', $avisos);
    }

    public function test_la_lista_de_citas_marca_la_cita_cuyo_trabajo_no_ha_llegado(): void
    {
        $this->orden(['appointment_id' => $this->cita(2)->id]);

        Livewire::test(ListAppointments::class)->assertSee('Lab: no ha llegado');
    }

    // ── Preguntarle al laboratorio cómo va ──────────────────────

    public function test_preguntar_abre_whatsapp_del_laboratorio_con_el_mensaje_escrito(): void
    {
        $orden = $this->orden(['telefono_laboratorio' => '668 555 1234', 'color' => 'A2', 'appointment_id' => $this->cita(3)->id]);

        $liga = LabOrderResource::ligaParaPreguntar($orden->fresh());

        $this->assertStringStartsWith('https://wa.me/526685551234?text=', $liga);
        parse_str(parse_url($liga, PHP_URL_QUERY), $q);
        $texto = $q['text'];
        $this->assertStringContainsString('Consultorio Sonrisas', $texto);
        $this->assertStringContainsString('Rosa Valenzuela', $texto);
        $this->assertStringContainsString('Corona de zirconia', $texto);
        $this->assertStringContainsString('diente 36', $texto);
        $this->assertStringContainsString('color A2', $texto);
        $this->assertStringContainsString('7 de octubre', $texto);   // cuándo se mandó
        $this->assertStringContainsString('jueves', $texto);         // para qué cita
        $this->assertSame(1, substr_count($texto, '?'), 'Una sola pregunta.');
    }

    public function test_el_boton_solo_sale_si_hay_telefono_y_no_ha_llegado(): void
    {
        $conTel = $this->orden(['telefono_laboratorio' => '6685551234']);
        $sinTel = $this->orden(['trabajo' => 'Puente']);
        $yaLlego = $this->orden(['telefono_laboratorio' => '6685551234', 'llego_at' => now()]);

        Livewire::test(ListLabOrders::class)
            ->assertTableActionVisible('preguntar', $conTel)
            ->assertTableActionHidden('preguntar', $sinTel)
            ->assertTableActionHidden('preguntar', $yaLlego);
    }

    public function test_el_mensaje_se_puede_cambiar_antes_de_abrir_whatsapp(): void
    {
        $orden = $this->orden(['telefono_laboratorio' => '6685551234']);

        Livewire::test(ListLabOrders::class)
            ->mountTableAction('preguntar', $orden)
            ->assertTableActionDataSet(fn (array $data) => str_contains($data['mensaje'] ?? '', 'Corona de zirconia'))
            ->setTableActionData(['mensaje' => 'Hola, ¿ya quedó la corona de Rosa?'])
            ->callMountedTableAction()
            ->assertRedirect('https://wa.me/526685551234?text=' . urlencode('Hola, ¿ya quedó la corona de Rosa?'));
    }

    public function test_si_borra_el_mensaje_solo_abre_el_chat(): void
    {
        $orden = $this->orden(['telefono_laboratorio' => '6685551234']);

        Livewire::test(ListLabOrders::class)
            ->callTableAction('preguntar', $orden, data: ['mensaje' => ''])
            ->assertRedirect('https://wa.me/526685551234');
    }

    public function test_el_telefono_del_laboratorio_se_llena_solo_la_segunda_vez(): void
    {
        $this->orden(['laboratorio' => 'Lab Dental Mochis', 'telefono_laboratorio' => '6685551234']);

        Livewire::test(CreateLabOrder::class)
            ->set('data.laboratorio', 'Lab Dental Mochis')
            ->assertSet('data.telefono_laboratorio', '6685551234');
    }

    // ── Quién lo ve ─────────────────────────────────────────────

    public function test_la_asistente_sin_permiso_de_dinero_no_ve_costos_ni_pagada(): void
    {
        $orden = $this->orden(['llego_at' => now()]);
        $lupita = User::forceCreate(['name' => 'Lupita', 'email' => 'l@test.com', 'password' => bcrypt('x'), 'role' => 'staff', 'email_verified_at' => now(), 'clinic_id' => $this->clinica->id, 've_dinero' => false]);
        $this->actingAs($lupita);

        $this->get(LabOrderResource::getUrl('index'))->assertOk();
        Livewire::test(ListLabOrders::class)
            ->assertSee('Corona de zirconia')
            ->assertDontSee('$1,800')
            ->assertDontSee('Se le debe al laboratorio')
            ->assertTableActionHidden('pagada', $orden);
    }

    public function test_el_doctor_ve_lo_que_se_debe(): void
    {
        $this->orden();

        Livewire::test(ListLabOrders::class)->assertSee('Se le debe al laboratorio')->assertSee('$1,800');
    }

    public function test_en_free_despues_de_la_prueba_no_entra(): void
    {
        $this->clinica->update(['plan' => 'free', 'plan_ends_at' => null, 'trial_ends_at' => now()->subDay()]);

        $this->get(LabOrderResource::getUrl('index'))->assertForbidden();
    }

    public function test_no_mezcla_otro_consultorio(): void
    {
        $otra = Clinic::create(['name' => 'Otra', 'plan' => 'basico', 'plan_ends_at' => now()->addMonth(), 'onboarding_status' => 'completed']);
        $ajena = Patient::withoutGlobalScopes()->create(['clinic_id' => $otra->id, 'first_name' => 'Ajena', 'last_name' => 'X']);
        LabOrder::withoutGlobalScopes()->create(['clinic_id' => $otra->id, 'patient_id' => $ajena->id, 'laboratorio' => 'Otro lab',
            'trabajo' => 'Puente', 'costo' => 9999, 'enviada_at' => today()]);

        $this->assertSame(0.0, LabOrder::loQueSeDebe($this->clinica->id)['total']);
        Livewire::test(ListLabOrders::class)->assertDontSee('Puente');
    }
}
