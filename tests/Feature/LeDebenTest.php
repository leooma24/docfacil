<?php

namespace Tests\Feature;

use App\Filament\Doctor\Widgets\LeDebenWidget;
use App\Models\Clinic;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Payment;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * "Le deben" en el escritorio: una tarjeta por paciente con cuánto debe y
 * desde cuándo, y Cobrar / Recordarle a la mano.
 *
 * Antes eran dos cuadros a media pantalla ("Cobros pendientes" y "Adeudos
 * vencidos") que se veían mochos (Omar, 12-oct-2026), y el "Cobrar" de
 * Cobros pendientes daba todo por pagado con un clic aunque el paciente
 * trajera la mitad.
 */
class LeDebenTest extends TestCase
{
    use RefreshDatabase;

    private Clinic $clinica;
    private Patient $ana;

    protected function setUp(): void
    {
        parent::setUp();
        $this->travelTo(\Carbon\Carbon::parse('2026-10-14 10:00'));
        $this->clinica = Clinic::create(['name' => 'Consultorio Sonrisas', 'plan' => 'basico', 'plan_ends_at' => now()->addMonth(),
            'trial_ends_at' => now()->subDay(), 'onboarding_status' => 'completed']);
        $user = User::forceCreate(['name' => 'Dr. Javier', 'email' => 'd@test.com', 'password' => bcrypt('x'), 'role' => 'doctor',
            'email_verified_at' => now(), 'clinic_id' => $this->clinica->id]);
        Doctor::create(['user_id' => $user->id, 'clinic_id' => $this->clinica->id, 'specialty' => 'Odontología']);
        $this->ana = $this->paciente('Ana', '6681234567');
        Filament::setCurrentPanel(Filament::getPanel('doctor'));
        $this->actingAs($user);
    }

    private function paciente(string $nombre, ?string $telefono, array $extra = []): Patient
    {
        return Patient::create(array_merge(['clinic_id' => $this->clinica->id, 'first_name' => $nombre, 'last_name' => 'Ruiz', 'phone' => $telefono], $extra));
    }

    private function debe(Patient $p, float $monto, ?string $vence, string $desde = '2026-10-01', float $abonado = 0): Payment
    {
        return Payment::create(['clinic_id' => $this->clinica->id, 'patient_id' => $p->id, 'amount' => $monto, 'amount_paid' => $abonado,
            'status' => $abonado > 0 ? 'partial' : 'pending', 'payment_method' => 'cash', 'payment_date' => $desde, 'due_date' => $vence]);
    }

    public function test_una_tarjeta_por_paciente_con_lo_que_debe_y_si_ya_vencio(): void
    {
        $this->debe($this->ana, 800, '2026-10-04');
        $this->debe($this->ana, 500, null, '2026-10-12');

        Livewire::test(LeDebenWidget::class)
            ->assertSee('Le deben')
            ->assertSee('Ana Ruiz')
            ->assertSee('$1,300.00')
            ->assertSee('Vencido hace 10 días')
            ->assertSee('2 cobros');
    }

    public function test_arriba_dice_el_total_y_lo_vencido(): void
    {
        $this->debe($this->ana, 800, '2026-10-04');
        $this->debe($this->paciente('Beto', '6689998877'), 1200, null);

        Livewire::test(LeDebenWidget::class)
            ->assertSee('$2,000.00')
            ->assertSee('$800.00 ya vencido');
    }

    public function test_primero_lo_vencido(): void
    {
        $this->debe($this->paciente('Beto', '6689998877'), 5000, null);
        $this->debe($this->ana, 300, '2026-10-01');

        Livewire::test(LeDebenWidget::class)->assertSeeInOrder(['Ana Ruiz', 'Beto Ruiz']);
    }

    public function test_cobrar_pregunta_cuanto_trae_y_no_lo_da_todo_por_pagado(): void
    {
        $viejo = $this->debe($this->ana, 800, '2026-10-04');
        $nuevo = $this->debe($this->ana, 500, null, '2026-10-12');

        Livewire::test(LeDebenWidget::class)
            ->mountAction('cobrar', ['patient' => $this->ana->id])
            ->assertActionDataSet(['monto' => 1300.0])
            ->setActionData(['monto' => 500, 'payment_method' => 'cash'])
            ->callMountedAction()
            ->assertHasNoActionErrors();

        $this->assertEquals(300, (float) $viejo->fresh()->remaining, 'El abono se aplica primero a lo más viejo');
        $this->assertSame('pending', $nuevo->fresh()->status);
    }

    public function test_las_mensualidades_que_no_vencen_no_cuentan(): void
    {
        $plan = \App\Models\PaymentPlan::crear(['clinic_id' => $this->clinica->id, 'patient_id' => $this->ana->id, 'description' => 'Brackets',
            'total' => 12000, 'down_payment' => 0, 'installments_count' => 12, 'first_due_date' => '2026-09-14']);

        Livewire::test(LeDebenWidget::class)
            ->assertSee('$2,000.00')       // septiembre y octubre
            ->assertDontSee('$12,000.00');
    }

    public function test_al_nino_le_recuerda_a_su_mama(): void
    {
        $mateo = $this->paciente('Mateo', null, ['responsable_id' => $this->ana->id]);
        $this->debe($mateo, 600, '2026-10-10');

        Livewire::test(LeDebenWidget::class)
            ->assertSee('Mateo Ruiz')
            ->assertSee('Recordarle')
            ->assertSee('wa.me/526681234567', false);
    }

    public function test_sin_telefono_no_ofrece_whatsapp(): void
    {
        $this->debe($this->paciente('Luis', null), 600, '2026-10-10');

        Livewire::test(LeDebenWidget::class)->assertSee('Luis Ruiz')->assertDontSee('Recordarle');
    }

    public function test_sin_deudas_no_sale(): void
    {
        $this->assertFalse(LeDebenWidget::canView());

        $this->debe($this->ana, 100, null);
        $this->assertTrue(LeDebenWidget::canView());
    }

    public function test_no_trae_deudas_de_otro_consultorio(): void
    {
        $otra = Clinic::create(['name' => 'Otra', 'plan' => 'basico', 'plan_ends_at' => now()->addMonth(), 'onboarding_status' => 'completed']);
        $ajeno = Patient::withoutGlobalScopes()->create(['clinic_id' => $otra->id, 'first_name' => 'Ajeno', 'last_name' => 'Secreto']);
        Payment::withoutGlobalScopes()->create(['clinic_id' => $otra->id, 'patient_id' => $ajeno->id, 'amount' => 999, 'amount_paid' => 0,
            'status' => 'pending', 'payment_method' => 'cash', 'payment_date' => '2026-10-01']);
        $this->debe($this->ana, 100, null);

        Livewire::test(LeDebenWidget::class)->assertDontSee('Secreto');
    }
}
