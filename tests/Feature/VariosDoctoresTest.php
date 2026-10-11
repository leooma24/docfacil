<?php

namespace Tests\Feature;

use App\Filament\Doctor\Pages\BitacoraDelConsultorio;
use App\Filament\Doctor\Pages\Comisiones;
use App\Models\Appointment;
use App\Models\Clinic;
use App\Models\Doctor;
use App\Models\Expense;
use App\Models\Liquidacion;
use App\Models\Patient;
use App\Models\Payment;
use App\Models\Service;
use App\Models\User;
use App\Support\LiquidacionDelDoctor;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Día 8 del plan (10-oct-2026): clínicas con varios doctores.
 *
 * - Comisión de cada doctor como en Dentalink: un porcentaje general y
 *   montos fijos para tratamientos concretos, y se paga solo lo ya cobrado.
 *   La liquidación lleva semáforo (pagado, parcial, sin pagar) y «Explicar».
 * - Exigir la verificación en dos pasos a todo el equipo.
 * - La bitácora de todo el consultorio, para el dueño.
 */
class VariosDoctoresTest extends TestCase
{
    use RefreshDatabase;

    private Clinic $clinica;
    private User $dueno;
    private User $paolaUser;
    private Doctor $javier;
    private Doctor $paola;
    private Patient $ana;
    private Service $resina;
    private Service $corona;

    protected function setUp(): void
    {
        parent::setUp();
        $this->travelTo(\Carbon\Carbon::parse('2026-10-20 10:00'));
        $this->clinica = Clinic::create(['name' => 'Clínica Sonrisas', 'plan' => 'profesional', 'plan_ends_at' => now()->addMonth(),
            'trial_ends_at' => now()->subDay(), 'is_active' => true, 'onboarding_status' => 'completed']);
        $this->dueno = User::forceCreate(['name' => 'Dr. Javier Soto', 'email' => 'j@test.com', 'password' => bcrypt('x'), 'role' => 'doctor',
            'email_verified_at' => now(), 'clinic_id' => $this->clinica->id]);
        $this->paolaUser = User::forceCreate(['name' => 'Dra. Paola Méndez', 'email' => 'p@test.com', 'password' => bcrypt('x'), 'role' => 'doctor',
            'email_verified_at' => now(), 'clinic_id' => $this->clinica->id]);
        $this->javier = Doctor::create(['user_id' => $this->dueno->id, 'clinic_id' => $this->clinica->id, 'specialty' => 'Odontología']);
        $this->paola = Doctor::create(['user_id' => $this->paolaUser->id, 'clinic_id' => $this->clinica->id, 'specialty' => 'Endodoncia']);
        $this->ana = Patient::create(['clinic_id' => $this->clinica->id, 'first_name' => 'Ana', 'last_name' => 'Ruiz', 'phone' => '6681234567']);
        $this->resina = Service::create(['clinic_id' => $this->clinica->id, 'name' => 'Resina', 'price' => 1200]);
        $this->corona = Service::create(['clinic_id' => $this->clinica->id, 'name' => 'Corona', 'price' => 6500]);
        $this->paola->update(['comision_porcentaje' => 30, 'comision_montos' => [$this->corona->id => 1500]]);
        Filament::setCurrentPanel(Filament::getPanel('doctor'));
        $this->actingAs($this->dueno);
    }

    private function cobroDe(Doctor $doctor, Service $servicio, float $monto, string $fecha = '2026-10-05'): Payment
    {
        $cita = Appointment::create(['clinic_id' => $this->clinica->id, 'doctor_id' => $doctor->id, 'patient_id' => $this->ana->id,
            'service_id' => $servicio->id, 'starts_at' => "{$fecha} 10:00", 'ends_at' => "{$fecha} 11:00", 'status' => 'completed']);

        return Payment::create(['clinic_id' => $this->clinica->id, 'patient_id' => $this->ana->id, 'appointment_id' => $cita->id,
            'service_id' => $servicio->id, 'amount' => $monto, 'status' => 'pending', 'payment_method' => 'cash',
            'payment_date' => $fecha, 'due_date' => $fecha]);
    }

    private function octubre(Doctor $doctor): array
    {
        return LiquidacionDelDoctor::de($doctor, \Carbon\Carbon::parse('2026-10-01'), \Carbon\Carbon::parse('2026-10-31'));
    }

    // ── La cuenta ───────────────────────────────────────────────

    public function test_se_le_paga_solo_lo_ya_cobrado_con_su_porcentaje_o_su_monto_fijo(): void
    {
        $this->cobroDe($this->paola, $this->resina, 1200)->registrarAbono(600, 'cash');
        $this->cobroDe($this->paola, $this->corona, 6500)->registrarAbono(6500, 'card');
        $this->cobroDe($this->paola, $this->resina, 1200);

        $liq = $this->octubre($this->paola);

        $this->assertSame(1680.0, $liq['total'], '30% de lo pagado de la resina ($180) + $1,500 fijos de la corona');
        $this->assertSame(['parcial', 'pagado', 'sin_pagar'], array_column($liq['renglones'], 'semaforo'));
        $this->assertSame(540.0, $liq['porCobrar'], 'Le tocarían $180 de la primera resina y $360 de la segunda cuando terminen de pagarlas');
    }

    public function test_explicar_dice_de_donde_sale_cada_monto(): void
    {
        $this->cobroDe($this->paola, $this->resina, 1200)->registrarAbono(600, 'cash');

        $explica = $this->octubre($this->paola)['renglones'][0]['explicacion'];

        $this->assertStringContainsString('30% de $1,200 = $360', $explica);
        $this->assertStringContainsString('ha pagado $600', $explica);
        $this->assertStringContainsString('le tocan $180', $explica);
    }

    public function test_no_se_mezclan_los_cobros_de_otro_doctor_ni_los_anticipos(): void
    {
        $this->cobroDe($this->javier, $this->resina, 1200)->registrarAbono(1200, 'cash');
        Payment::create(['clinic_id' => $this->clinica->id, 'patient_id' => $this->ana->id, 'amount' => 1000, 'status' => 'paid',
            'es_anticipo' => true, 'notes' => 'Anticipo', 'payment_method' => 'cash', 'payment_date' => '2026-10-05']);

        $this->assertSame(0.0, $this->octubre($this->paola)['total']);
    }

    public function test_un_abono_de_otro_mes_cuenta_en_el_mes_en_que_entro(): void
    {
        $cobro = $this->cobroDe($this->paola, $this->resina, 1200, '2026-09-20');
        $this->travelTo(\Carbon\Carbon::parse('2026-10-03 10:00'));
        $cobro->registrarAbono(1200, 'cash');

        $this->assertSame(360.0, $this->octubre($this->paola)['total']);
    }

    // ── La pantalla ─────────────────────────────────────────────

    public function test_el_dueno_configura_la_comision_y_ve_a_todos(): void
    {
        $this->cobroDe($this->paola, $this->resina, 1200)->registrarAbono(1200, 'cash');

        Livewire::test(Comisiones::class)
            ->assertSee('Dra. Paola Méndez')->assertSee('Dr. Javier Soto')
            ->callAction('configurar', data: ['comision_porcentaje' => 40, 'comision_montos' => []], arguments: ['doctor' => $this->paola->id])
            ->assertHasNoActionErrors();

        $this->assertSame(40.0, (float) $this->paola->fresh()->comision_porcentaje);
    }

    public function test_marcarla_pagada_la_anota_como_gasto_y_no_se_paga_dos_veces(): void
    {
        $this->cobroDe($this->paola, $this->resina, 1200)->registrarAbono(1200, 'cash');

        $pagina = Livewire::test(Comisiones::class)->set('mes', '2026-10');
        $pagina->callAction('pagar', arguments: ['doctor' => $this->paola->id]);
        $pagina->callAction('pagar', arguments: ['doctor' => $this->paola->id]);

        $this->assertSame(1, Liquidacion::count());
        $this->assertSame(360.0, (float) Liquidacion::sole()->monto);
        $gasto = Expense::sole();
        $this->assertSame('nomina', $gasto->category);
        $this->assertSame(360.0, (float) $gasto->amount);
    }

    public function test_otro_doctor_solo_ve_la_suya_y_no_configura(): void
    {
        $this->cobroDe($this->javier, $this->resina, 1200)->registrarAbono(1200, 'cash');
        $this->actingAs($this->paolaUser);

        Livewire::test(Comisiones::class)
            ->assertSee('Dra. Paola Méndez')
            ->assertDontSee('Dr. Javier Soto')
            ->assertActionHidden('configurar', ['doctor' => $this->paola->id]);
    }

    public function test_la_asistente_no_entra(): void
    {
        $lupita = User::forceCreate(['name' => 'Lupita', 'email' => 'l@test.com', 'password' => bcrypt('x'), 'role' => 'staff',
            'email_verified_at' => now(), 'clinic_id' => $this->clinica->id, 've_dinero' => true]);
        $this->actingAs($lupita);

        $this->get(Comisiones::getUrl())->assertForbidden();
    }

    public function test_con_un_solo_doctor_no_sale_en_el_menu(): void
    {
        $this->assertTrue(Comisiones::shouldRegisterNavigation());

        $this->paola->delete();
        $this->assertFalse(Comisiones::shouldRegisterNavigation());
    }

    // ── Verificación en dos pasos para todos ────────────────────

    public function test_si_el_dueno_la_exige_quien_no_la_tiene_va_a_activarla(): void
    {
        $this->clinica->update(['exigir_dos_pasos' => true]);
        $this->actingAs($this->paolaUser);

        $this->get('/doctor')->assertRedirect(\App\Filament\Doctor\Pages\Security::getUrl());
        $this->get(\App\Filament\Doctor\Pages\Security::getUrl())->assertOk()->assertSee('Su consultorio pide la verificación en dos pasos');
    }

    public function test_quien_ya_la_tiene_entra_normal(): void
    {
        $this->clinica->update(['exigir_dos_pasos' => true]);
        $this->paolaUser->forceFill(['two_factor_enabled' => true, 'two_factor_confirmed_at' => now(), 'two_factor_secret' => 'ABC'])->save();
        $this->actingAs($this->paolaUser);

        $this->get('/doctor')->assertOk();
    }

    public function test_solo_el_dueno_puede_exigirla(): void
    {
        // Primero la tiene que tener él, para no quedarse fuera.
        Livewire::test(\App\Filament\Doctor\Pages\Security::class)->call('exigirATodos', true);
        $this->assertFalse((bool) $this->clinica->fresh()->exigir_dos_pasos);

        $this->dueno->forceFill(['two_factor_enabled' => true, 'two_factor_confirmed_at' => now(), 'two_factor_secret' => 'ABC'])->save();
        Livewire::test(\App\Filament\Doctor\Pages\Security::class)->call('exigirATodos', true);
        $this->assertTrue((bool) $this->clinica->fresh()->exigir_dos_pasos);

        $this->actingAs($this->paolaUser);
        $this->paolaUser->forceFill(['two_factor_enabled' => true, 'two_factor_confirmed_at' => now(), 'two_factor_secret' => 'ABC'])->save();
        Livewire::test(\App\Filament\Doctor\Pages\Security::class)->call('exigirATodos', false);
        $this->assertTrue((bool) $this->clinica->fresh()->exigir_dos_pasos);
    }

    // ── Bitácora del consultorio ────────────────────────────────

    public function test_el_dueno_ve_la_bitacora_de_todo_el_consultorio(): void
    {
        activity()->performedOn($this->ana)->causedBy($this->paolaUser)->event('viewed')->log('Abrió el expediente');
        $otra = Clinic::create(['name' => 'Otra', 'plan' => 'basico']);
        $extrano = User::forceCreate(['name' => 'Extraño', 'email' => 'x@test.com', 'password' => bcrypt('x'), 'role' => 'doctor', 'clinic_id' => $otra->id]);
        activity()->causedBy($extrano)->log('Algo de otra clínica');

        Livewire::test(BitacoraDelConsultorio::class)
            ->assertSee('Dra. Paola Méndez')
            ->assertSee('Abrió el expediente')
            ->assertSee('Ana Ruiz')
            ->assertDontSee('Algo de otra clínica')
            ->assertDontSee('updated');
    }

    public function test_solo_el_dueno_ve_la_bitacora_del_consultorio(): void
    {
        $this->actingAs($this->paolaUser);

        $this->get(BitacoraDelConsultorio::getUrl())->assertForbidden();
    }
}
