<?php

namespace App\Filament\Doctor\Pages;

use Filament\Pages\Page;

class Upgrade extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-arrow-up-circle';

    protected static ?string $navigationLabel = 'Mi Plan';

    protected static ?string $title = 'Actualizar Plan';

    protected static ?string $slug = 'actualizar-plan';

    protected static bool $shouldRegisterNavigation = true;

    protected static ?string $navigationGroup = 'Consultorio';

    protected static ?int $navigationSort = 99;

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
        return [
            [
                'key' => 'basico',
                'name' => 'Básico',
                'monthly' => \App\Models\Commission::monthlyPriceForPlan('basico'),
                'annual' => \App\Models\Commission::annualPriceForPlan('basico'),
                'ideal' => 'Consultorios individuales que arrancan',
                'features' => [
                    '1 doctor',
                    '200 pacientes',
                    'Recordatorios WhatsApp',
                    'Recetas PDF',
                    'Check-in con QR',
                    'Expediente clínico completo',
                    'Cobro por WhatsApp',
                ],
            ],
            [
                'key' => 'profesional',
                'name' => 'Pro',
                'monthly' => \App\Models\Commission::monthlyPriceForPlan('profesional'),
                'annual' => \App\Models\Commission::annualPriceForPlan('profesional'),
                'ideal' => 'Consultorios establecidos',
                'popular' => true,
                'features' => [
                    'Hasta 3 doctores',
                    'Pacientes ilimitados',
                    'Odontograma interactivo',
                    'Portal del paciente',
                    'Consentimientos digitales',
                    'Reportes avanzados',
                    'Soporte prioritario',
                    'Todo lo del Básico',
                ],
            ],
            [
                'key' => 'clinica',
                'name' => 'Clínica',
                'monthly' => \App\Models\Commission::monthlyPriceForPlan('clinica'),
                'annual' => \App\Models\Commission::annualPriceForPlan('clinica'),
                'ideal' => 'Clínicas con varios doctores o sedes',
                'features' => [
                    'Doctores ilimitados',
                    'Multi-sucursal',
                    'Comisiones entre doctores',
                    'Reportes por doctor',
                    'Onboarding 1 a 1',
                    'Soporte prioritario 24/7',
                    'Todo lo del Pro',
                ],
            ],
        ];
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
