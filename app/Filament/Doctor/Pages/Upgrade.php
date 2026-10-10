<?php

namespace App\Filament\Doctor\Pages;

use Filament\Pages\Page;

class Upgrade extends Page
{
    /** Comprar o cambiar el plan es cosa del doctor, no de la asistente. */
    public static function canAccess(): bool
    {
        return auth()->check() && ! auth()->user()->esAsistente();
    }

    protected static ?string $navigationIcon = 'heroicon-o-arrow-up-circle';

    protected static ?string $navigationLabel = 'Mi plan';

    protected static ?string $title = 'Actualizar Plan';

    protected static ?string $slug = 'actualizar-plan';

    protected static bool $shouldRegisterNavigation = true;

    protected static ?string $navigationGroup = 'Mi cuenta';

    protected static ?int $navigationSort = 5;

    protected static string $view = 'filament.doctor.pages.upgrade';

    public string $billingCycle = 'monthly'; // 'monthly' | 'annual'

    public function mount(): void
    {
        // Si ya pagan anual, que se vea preseleccionado
        if ($this->getClinic()?->billing_cycle === 'annual') {
            $this->billingCycle = 'annual';
        }
    }

    public function setCycle(string $cycle): void
    {
        $this->billingCycle = in_array($cycle, ['monthly', 'annual'], true) ? $cycle : 'monthly';
    }

    public function getClinic(): ?\App\Models\Clinic
    {
        return auth()->user()?->clinic;
    }

    public function isExpired(): bool
    {
        $clinic = $this->getClinic();
        if (!$clinic) {
            return false;
        }

        if ($clinic->plan === 'free' && $clinic->trial_ends_at?->isPast()) {
            return true;
        }
        if ($clinic->is_beta && $clinic->beta_ends_at?->isPast()) {
            return true;
        }
        // Plan pagado (Básico/Pro/Clínica) que venció sin renovar.
        if (in_array($clinic->plan, ['basico', 'profesional', 'clinica'], true)
            && $clinic->plan_ends_at?->isPast()) {
            return true;
        }

        return false;
    }

    public function isFounder(): bool
    {
        return $this->getClinic()?->is_founder ?? false;
    }

    public function getFounderPrice(): string
    {
        return number_format($this->getClinic()?->founder_price ?? 499, 0);
    }

    /**
     * Planes disponibles con precio mensual/anual calculado desde el Commission model.
     */
    public function getPlans(): array
    {
        // Lo que trae cada plan sale de una sola fuente (LoQueTraeCadaPlan),
        // la misma de la página de inicio, el folleto y la propuesta.
        $plans = [];

        foreach (['basico', 'profesional', 'clinica'] as $key) {
            $plan = \App\Support\LoQueTraeCadaPlan::plan($key);

            $features = [$plan['limits']];
            if (! empty($plan['lead'])) {
                $features[] = rtrim(str_replace(', y además:', '', $plan['lead']), ':');
            }

            $plans[] = [
                'key' => $key,
                'name' => $plan['name'],
                // Con su precio de fundador si lo tiene (Clinic::precioDelPlan).
                'monthly' => $this->getClinic()?->precioDelPlan($key, 'monthly') ?? \App\Models\Commission::monthlyPriceForPlan($key),
                'annual' => $this->getClinic()?->precioDelPlan($key, 'annual') ?? \App\Models\Commission::annualPriceForPlan($key),
                'fundador' => (bool) $this->getClinic()?->tienePrecioDeFundador($key),
                'ideal' => $plan['ideal'],
                'popular' => $plan['popular'],
                'features' => array_merge($features, $plan['features']),
            ];
        }

        return $plans;
    }

    /**
     * Inicia el checkout. Por ahora redirige a una ruta nombrada que decide Stripe o SPEI.
     */
    public function checkout(string $plan, string $method): void
    {
        if (!in_array($plan, ['basico', 'profesional', 'clinica'], true)) {
            return;
        }

        if ($method === 'spei') {
            $this->redirect(route('filament.doctor.pages.pago-spei', [
                'plan' => $plan,
                'cycle' => $this->billingCycle,
            ]));
            return;
        }

        // Stripe: redirige al controller que crea la sesión de checkout
        $this->redirect(route('stripe.checkout', [
            'plan' => $plan,
            'cycle' => $this->billingCycle,
        ]));
    }
}
