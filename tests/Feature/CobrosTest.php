<?php

namespace Tests\Feature;

use App\Models\Clinic;
use App\Models\Commission;
use App\Models\SpeiPayment;
use App\Models\User;
use App\Services\SpeiReviewService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * El camino del dinero: SPEI, comisiones y el webhook de Stripe.
 *
 * No tenía ninguna prueba, siendo la parte donde un error cuesta dinero de
 * verdad: un pago que no activa el plan, una comisión duplicada, o un
 * webhook que se procesa dos veces porque Stripe reintentó.
 *
 * El cobro con tarjeta en sí no se prueba aquí: eso necesita llaves de
 * modo prueba de Stripe. Lo que sí se prueba es todo lo que pasa cuando
 * Stripe nos avisa que ya cobró.
 */
class CobrosTest extends TestCase
{
    use RefreshDatabase;

    /** El mismo secreto de prueba para firmar los webhooks del test. */
    private const SECRETO_WEBHOOK = 'whsec_prueba_local';

    private Clinic $clinica;

    private User $vendedor;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        config(['services.stripe.webhook.secret' => self::SECRETO_WEBHOOK]);

        $this->vendedor = User::forceCreate([
            'name' => 'Juan Ventas',
            'email' => 'ventas@test.com',
            'password' => bcrypt('password'),
            'role' => 'sales',
            'is_active_sales_rep' => true,
        ]);

        $this->admin = User::forceCreate([
            'name' => 'Admin',
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
            'role' => 'super_admin',
        ]);

        $this->clinica = Clinic::create([
            'name' => 'Consultorio Test',
            'slug' => 'consultorio-test',
            'plan' => 'free',
            'onboarding_status' => 'completed',
        ]);

        // sold_by_user_id esta fuera de $fillable a proposito, para que nadie
        // se asigne una venta ajena por asignacion masiva. Se pone aparte.
        $this->clinica->sold_by_user_id = $this->vendedor->id;
        $this->clinica->save();
    }

    // ══════════════════════════════════════════════════════════════
    //  Comisiones
    // ══════════════════════════════════════════════════════════════

    public function test_una_venta_mensual_parte_la_comision_en_dos_pagos(): void
    {
        $comisiones = Commission::generateForSale(
            clinic: $this->clinica,
            userId: $this->vendedor->id,
            plan: 'profesional',
            billingCycle: 'monthly',
        );

        $this->assertCount(2, $comisiones, 'La venta mensual se paga en dos mitades.');

        $total = Commission::where('clinic_id', $this->clinica->id)->sum('amount');
        $this->assertEqualsWithDelta(
            Commission::monthlyPriceForPlan('profesional') * 3,
            $total,
            0.01,
            'La comisión es 3 veces el precio mensual.'
        );
    }

    public function test_una_venta_anual_se_paga_de_una_sola_vez(): void
    {
        $comisiones = Commission::generateForSale(
            clinic: $this->clinica,
            userId: $this->vendedor->id,
            plan: 'profesional',
            billingCycle: 'annual',
        );

        $this->assertCount(1, $comisiones);
        $this->assertSame('lump_sum', $comisiones[0]->payout_type);
    }

    public function test_no_se_duplican_las_comisiones_si_se_genera_dos_veces(): void
    {
        // Pasa de verdad: Stripe reintenta el webhook, o un SPEI mensual se
        // renueva. Sin la guardia, el vendedor cobraría doble.
        Commission::generateForSale(
            clinic: $this->clinica,
            userId: $this->vendedor->id,
            plan: 'profesional',
            billingCycle: 'monthly',
        );

        $segundas = Commission::generateForSale(
            clinic: $this->clinica,
            userId: $this->vendedor->id,
            plan: 'profesional',
            billingCycle: 'monthly',
        );

        $this->assertSame([], $segundas);
        $this->assertSame(2, Commission::where('clinic_id', $this->clinica->id)->count());
    }

    public function test_el_plan_free_no_genera_comision(): void
    {
        $this->assertSame([], Commission::generateForSale(
            clinic: $this->clinica,
            userId: $this->vendedor->id,
            plan: 'free',
            billingCycle: 'monthly',
        ));
    }

    // ══════════════════════════════════════════════════════════════
    //  SPEI (transferencia con aprobación manual)
    // ══════════════════════════════════════════════════════════════

    private function pagoSpei(string $plan = 'profesional', string $ciclo = 'monthly'): SpeiPayment
    {
        return SpeiPayment::create([
            'clinic_id' => $this->clinica->id,
            'plan' => $plan,
            'billing_cycle' => $ciclo,
            'amount' => Commission::monthlyPriceForPlan($plan),
            'reference_code' => 'REF-' . uniqid(),
            'status' => SpeiPayment::STATUS_PENDING,
        ]);
    }

    public function test_aprobar_un_spei_activa_el_plan(): void
    {
        $pago = $this->pagoSpei();

        app(SpeiReviewService::class)->approve($pago, $this->admin);

        $clinica = $this->clinica->fresh();
        $this->assertSame('profesional', $clinica->plan);
        $this->assertTrue($clinica->plan_ends_at?->isFuture(), 'El plan debe quedar vigente.');
        $this->assertSame(SpeiPayment::STATUS_APPROVED, $pago->fresh()->status);
    }

    public function test_aprobar_un_spei_genera_la_comision_del_vendedor(): void
    {
        app(SpeiReviewService::class)->approve($this->pagoSpei(), $this->admin);

        $this->assertSame(2, Commission::where('clinic_id', $this->clinica->id)->count());
        $this->assertSame(
            'spei',
            Commission::where('clinic_id', $this->clinica->id)->first()->payment_method
        );
    }

    public function test_aprobar_dos_veces_no_duplica_nada(): void
    {
        $pago = $this->pagoSpei();
        $servicio = app(SpeiReviewService::class);

        $servicio->approve($pago, $this->admin);
        $vencimiento = $this->clinica->fresh()->plan_ends_at;

        // Un segundo clic del admin, o un doble submit.
        $servicio->approve($pago->fresh(), $this->admin);

        $this->assertSame(2, Commission::where('clinic_id', $this->clinica->id)->count());
        $this->assertEquals(
            $vencimiento->toDateTimeString(),
            $this->clinica->fresh()->plan_ends_at->toDateTimeString(),
            'No debe extenderse el plan por aprobar dos veces.'
        );
    }

    public function test_un_consultorio_sin_vendedor_no_genera_comision(): void
    {
        // Un consultorio que llego solo por la landing. No se le puede quitar
        // el vendedor al de arriba: ClinicObserver lo hace inmutable una vez
        // asignado, para que nadie transfiera una venta ajena.
        $solo = Clinic::create([
            'name' => 'Llego Solo',
            'slug' => 'llego-solo',
            'plan' => 'free',
            'onboarding_status' => 'completed',
        ]);

        $pago = SpeiPayment::create([
            'clinic_id' => $solo->id,
            'plan' => 'profesional',
            'billing_cycle' => 'monthly',
            'amount' => Commission::monthlyPriceForPlan('profesional'),
            'reference_code' => 'REF-' . uniqid(),
            'status' => SpeiPayment::STATUS_PENDING,
        ]);

        app(SpeiReviewService::class)->approve($pago, $this->admin);

        $this->assertSame('profesional', $solo->fresh()->plan);
        $this->assertSame(0, Commission::where('clinic_id', $solo->id)->count());
    }

    // ══════════════════════════════════════════════════════════════
    //  Webhook de Stripe
    // ══════════════════════════════════════════════════════════════

    /** Arma la cabecera que manda Stripe, firmada como lo hace ella. */
    private function firmar(string $cuerpo): string
    {
        $t = time();
        $firma = hash_hmac('sha256', "{$t}.{$cuerpo}", self::SECRETO_WEBHOOK);

        return "t={$t},v1={$firma}";
    }

    private function evento(string $id, string $tipo, array $objeto): string
    {
        return json_encode([
            'id' => $id,
            'type' => $tipo,
            'data' => ['object' => $objeto],
        ]);
    }

    private function mandarWebhook(string $cuerpo, ?string $firma = null)
    {
        return $this->call(
            'POST',
            '/billing/stripe/webhook',
            server: ['HTTP_STRIPE_SIGNATURE' => $firma ?? $this->firmar($cuerpo), 'CONTENT_TYPE' => 'application/json'],
            content: $cuerpo,
        );
    }

    public function test_rechaza_un_webhook_sin_firma_valida(): void
    {
        $cuerpo = $this->evento('evt_falso', 'checkout.session.completed', []);

        $this->mandarWebhook($cuerpo, 't=1,v1=firmafalsa')->assertStatus(400);

        $this->assertSame(0, DB::table('stripe_webhook_events')->count());
    }

    public function test_un_cobro_de_stripe_activa_el_plan_y_genera_comision(): void
    {
        $cuerpo = $this->evento('evt_1', 'checkout.session.completed', [
            'metadata' => [
                'clinic_id' => $this->clinica->id,
                'plan' => 'profesional',
                'billing_cycle' => 'monthly',
                'sold_by_user_id' => $this->vendedor->id,
            ],
        ]);

        $this->mandarWebhook($cuerpo)->assertSuccessful();

        $this->assertSame('profesional', $this->clinica->fresh()->plan);
        $this->assertSame(2, Commission::where('clinic_id', $this->clinica->id)->count());
    }

    public function test_el_mismo_evento_dos_veces_no_cobra_dos_veces(): void
    {
        // Stripe reintenta si nuestra respuesta tarda. Sin la guardia de
        // idempotencia, el vendedor cobraría comisión doble.
        $cuerpo = $this->evento('evt_repetido', 'checkout.session.completed', [
            'metadata' => [
                'clinic_id' => $this->clinica->id,
                'plan' => 'profesional',
                'billing_cycle' => 'monthly',
                'sold_by_user_id' => $this->vendedor->id,
            ],
        ]);

        $this->mandarWebhook($cuerpo)->assertSuccessful();
        $this->mandarWebhook($cuerpo)->assertSuccessful();

        $this->assertSame(1, DB::table('stripe_webhook_events')->where('event_id', 'evt_repetido')->count());
        $this->assertSame(2, Commission::where('clinic_id', $this->clinica->id)->count());
    }

    public function test_un_evento_sin_metadata_no_rompe_el_webhook(): void
    {
        $cuerpo = $this->evento('evt_vacio', 'checkout.session.completed', ['metadata' => []]);

        $this->mandarWebhook($cuerpo)->assertSuccessful();

        $this->assertSame('free', $this->clinica->fresh()->plan);
    }

    public function test_un_evento_que_no_nos_interesa_se_ignora_sin_error(): void
    {
        $cuerpo = $this->evento('evt_otro', 'customer.updated', []);

        $this->mandarWebhook($cuerpo)->assertSuccessful();
    }

    public function test_sin_secreto_configurado_el_webhook_se_rechaza(): void
    {
        // Defensa por si alguien despliega sin la variable: mejor rechazar
        // que procesar eventos que no podemos verificar.
        config(['services.stripe.webhook.secret' => null]);

        $this->mandarWebhook($this->evento('evt_x', 'checkout.session.completed', []))
            ->assertStatus(501);
    }
}
