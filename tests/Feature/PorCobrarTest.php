<?php

namespace Tests\Feature;

use App\Filament\Doctor\Resources\PaymentResource\Pages\ListPayments;
use App\Filament\Doctor\Widgets\AlertsWidget;
use App\Filament\Doctor\Widgets\DashboardHeroWidget;
use App\Models\Clinic;
use App\Models\Patient;
use App\Models\Payment;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Lo que le deben al consultorio.
 *
 * El escritorio sumaba el monto de los cobros 'pending' y se saltaba los
 * abonos: un tratamiento de $2,500 con $1,000 dados no salía en "Por
 * cobrar". El doctor veía $0 con pacientes debiéndole.
 */
class PorCobrarTest extends TestCase
{
    use RefreshDatabase;

    private Clinic $clinica;

    private User $doctor;

    private Patient $paciente;

    protected function setUp(): void
    {
        parent::setUp();

        $this->clinica = Clinic::create([
            'name' => 'Consultorio Test',
            'slug' => 'consultorio-test',
            'plan' => 'profesional',
            'plan_ends_at' => now()->addMonth(),
            'onboarding_status' => 'completed',
        ]);

        $this->doctor = User::forceCreate([
            'name' => 'Dr. Roberto García',
            'email' => 'doctor@test.com',
            'password' => bcrypt('password'),
            'role' => 'doctor',
            'email_verified_at' => now(),
            'clinic_id' => $this->clinica->id,
        ]);

        $this->paciente = Patient::create([
            'clinic_id' => $this->clinica->id,
            'first_name' => 'Fernando',
            'last_name' => 'Morales',
            'phone' => '5512345678',
        ]);
    }

    private function cobro(float $monto, string $estado, float $abonado = 0, ?string $fecha = null): Payment
    {
        return Payment::create([
            'clinic_id' => $this->clinica->id,
            'patient_id' => $this->paciente->id,
            'amount' => $monto,
            'amount_paid' => $abonado,
            'status' => $estado,
            'payment_date' => $fecha ?? now()->toDateString(),
        ]);
    }

    private function comoDoctor(): void
    {
        Filament::setCurrentPanel(Filament::getPanel('doctor'));
        $this->actingAs($this->doctor);
    }

    // ── La cuenta ────────────────────────────────────────────────

    public function test_de_un_abono_se_debe_lo_que_falta(): void
    {
        $this->cobro(2500, 'partial', abonado: 1000);

        $this->assertSame(1500.0, Payment::saldoPorCobrar($this->clinica->id));
    }

    public function test_un_cobro_sin_abonar_se_debe_completo(): void
    {
        $this->cobro(800, 'pending');

        $this->assertSame(800.0, Payment::saldoPorCobrar($this->clinica->id));
    }

    public function test_lo_liquidado_ya_no_se_debe(): void
    {
        $this->cobro(800, 'paid', abonado: 800);

        $this->assertSame(0.0, Payment::saldoPorCobrar($this->clinica->id));
    }

    public function test_cuenta_lo_que_deben_de_meses_pasados(): void
    {
        // "Por cobrar" es lo que le deben hoy, no solo lo de este mes.
        $this->cobro(1250, 'partial', abonado: 250, fecha: now()->subMonths(2)->toDateString());

        $this->assertSame(1000.0, Payment::saldoPorCobrar($this->clinica->id));
    }

    public function test_lo_que_le_deben_a_otro_consultorio_no_se_mezcla(): void
    {
        $otra = Clinic::create([
            'name' => 'Otro',
            'slug' => 'otro',
            'plan' => 'basico',
            'plan_ends_at' => now()->addMonth(),
            'onboarding_status' => 'completed',
        ]);

        Payment::withoutGlobalScopes()->create([
            'clinic_id' => $otra->id,
            'patient_id' => $this->paciente->id,
            'amount' => 99999,
            'amount_paid' => 0,
            'status' => 'pending',
            'payment_date' => now()->toDateString(),
        ]);

        $this->cobro(800, 'pending');

        $this->assertSame(800.0, Payment::saldoPorCobrar($this->clinica->id));
    }

    // ── Donde se ve ──────────────────────────────────────────────

    public function test_el_escritorio_ya_no_dice_cero_cuando_hay_abonos(): void
    {
        $this->cobro(2500, 'partial', abonado: 1000);
        $this->cobro(800, 'pending');

        $this->comoDoctor();

        $datos = Livewire::test(DashboardHeroWidget::class)->instance()->getData();

        $this->assertEquals(2300.0, $datos['pending_payments']);
    }

    public function test_la_lista_de_cobros_cuenta_los_abonos_de_hoy(): void
    {
        $this->cobro(2500, 'partial', abonado: 1000);
        $this->cobro(800, 'paid', abonado: 800);

        $this->comoDoctor();

        $cifras = collect(Livewire::test(ListPayments::class)->instance()->getHeroConfig()['stats'])
            ->pluck('value')
            ->all();

        // Cobrado hoy, este mes, pendiente y pagos del mes, en ese orden.
        $this->assertSame(['$1,800', '$1,800', '$1,500', '2'], $cifras);
    }

    public function test_un_abono_atrasado_tambien_es_pago_vencido(): void
    {
        $this->cobro(2500, 'partial', abonado: 1000, fecha: now()->subDays(10)->toDateString());

        $this->comoDoctor();

        $alertas = collect(Livewire::test(AlertsWidget::class)->instance()->getAlerts())->pluck('title')->all();

        $this->assertContains('1 pagos vencidos', $alertas);
    }
}
