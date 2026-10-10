<?php

namespace Tests\Feature;

use App\Filament\Doctor\Pages\SpeiCheckout;
use App\Filament\Doctor\Pages\Upgrade;
use App\Http\Controllers\Billing\StripeCheckoutController;
use App\Models\Clinic;
use App\Models\Doctor;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Al fundador se le cobra lo que se le prometió: Pro a su precio de
 * fundador ($499 al mes de por vida). Antes Mi plan, la tarjeta y la
 * transferencia le cobraban el Pro a $999 (auditoría del 12-oct-2026).
 */
class PrecioDeFundadorTest extends TestCase
{
    use RefreshDatabase;

    private Clinic $clinica;

    protected function setUp(): void
    {
        parent::setUp();
        $this->clinica = Clinic::create(['name' => 'Consultorio', 'plan' => 'profesional', 'plan_ends_at' => now()->subDay(), 'onboarding_status' => 'completed',
            'is_founder' => true, 'founder_price' => 499]);
        $user = User::forceCreate(['name' => 'Dr. Javier', 'email' => 'd@test.com', 'password' => bcrypt('x'), 'role' => 'doctor', 'email_verified_at' => now(), 'clinic_id' => $this->clinica->id]);
        Doctor::create(['user_id' => $user->id, 'clinic_id' => $this->clinica->id, 'specialty' => 'Odontología']);
        Filament::setCurrentPanel(Filament::getPanel('doctor'));
        $this->actingAs($user);
    }

    public function test_el_precio_del_consultorio_respeta_al_fundador(): void
    {
        $this->assertSame(499, $this->clinica->precioDelPlan('profesional', 'monthly'));
        $this->assertSame(4990, $this->clinica->precioDelPlan('profesional', 'annual'));
        $this->assertSame(499, $this->clinica->precioDelPlan('basico', 'monthly'));
        $this->assertSame(1999, $this->clinica->precioDelPlan('clinica', 'monthly'));
    }

    public function test_quien_no_es_fundador_paga_el_precio_normal(): void
    {
        $this->clinica->update(['is_founder' => false]);

        $this->assertSame(999, $this->clinica->fresh()->precioDelPlan('profesional', 'monthly'));
    }

    public function test_mi_plan_le_ensena_su_precio_de_fundador(): void
    {
        Livewire::test(Upgrade::class)
            ->assertSee('Su precio de fundador')
            ->assertSee('$499')
            ->assertDontSee('$999');
    }

    public function test_la_transferencia_le_pide_su_precio(): void
    {
        Livewire::withQueryParams(['plan' => 'profesional', 'cycle' => 'monthly'])->test(SpeiCheckout::class)
            ->assertSet('amount', 499);
    }

    public function test_la_tarjeta_le_cobra_su_precio(): void
    {
        $linea = StripeCheckoutController::lineaDeCobro($this->clinica, 'profesional', 'monthly');

        $this->assertSame(49900, $linea['price_data']['unit_amount']);
        $this->assertSame('mxn', $linea['price_data']['currency']);
        $this->assertSame('month', $linea['price_data']['recurring']['interval']);
    }

    public function test_la_tarjeta_de_quien_no_es_fundador_usa_el_precio_de_stripe(): void
    {
        config(['services.stripe.prices.profesional_monthly' => 'price_pro_m']);
        $this->clinica->update(['is_founder' => false]);

        $this->assertSame(['price' => 'price_pro_m', 'quantity' => 1], StripeCheckoutController::lineaDeCobro($this->clinica->fresh(), 'profesional', 'monthly'));
    }
}
