<?php

namespace App\Filament\Doctor\Pages;

use App\Models\PremiumService;
use App\Models\PremiumServicePurchase;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class ServicesMarketplace extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-sparkles';

    protected static ?string $navigationLabel = 'Servicios premium';

    protected static ?string $title = 'Servicios premium para tu consultorio';

    protected static ?string $slug = 'servicios-premium';

    protected static bool $shouldRegisterNavigation = true;

    protected static ?string $navigationGroup = 'Consultorio';

    protected static ?int $navigationSort = 95;

    protected static string $view = 'filament.doctor.pages.services-marketplace';

    public function getServices()
    {
        return PremiumService::active()
            ->orderByDesc('is_featured')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get()
            ->groupBy('category');
    }

    public function getMyPurchases()
    {
        return PremiumServicePurchase::with('service')
            ->where('clinic_id', auth()->user()->clinic_id)
            ->latest()
            ->take(10)
            ->get();
    }

    public function categoryLabel(string $category): string
    {
        return PremiumService::CATEGORIES[$category] ?? ucfirst($category);
    }

    /**
     * Inicia la compra: crea el PremiumServicePurchase en pending_payment
     * y redirige a la pasarela. Idempotente por 5 minutos contra doble click.
     */
    public function purchase(int $serviceId, string $method): void
    {
        // Validar método: solo stripe, spei o manual (custom_quote).
        if (!in_array($method, ['stripe', 'spei', 'manual'], true)) {
            Notification::make()
                ->title('Método de pago no válido')
                ->danger()
                ->send();
            return;
        }

        $service = PremiumService::active()->find($serviceId);
        if (!$service) {
            Notification::make()
                ->title('Servicio no disponible')
                ->body('Este servicio ya no está activo. Refresca la página.')
                ->warning()
                ->send();
            return;
        }

        $clinic = auth()->user()?->clinic;
        if (!$clinic) {
            Notification::make()
                ->title('No encontramos tu consultorio')
                ->body('Cierra sesión y vuelve a entrar. Si el problema persiste, contáctanos por WhatsApp.')
                ->danger()
                ->persistent()
                ->send();
            return;
        }

        // Anti doble-click: si ya hay un purchase pending del mismo servicio
        // hecho hace menos de 5 minutos, lo reutilizamos en lugar de duplicar.
        $existing = PremiumServicePurchase::where('clinic_id', $clinic->id)
            ->where('premium_service_id', $service->id)
            ->where('status', PremiumServicePurchase::STATUS_PENDING_PAYMENT)
            ->where('created_at', '>', now()->subMinutes(5))
            ->latest()
            ->first();

        $purchase = $existing ?? PremiumServicePurchase::create([
            'clinic_id' => $clinic->id,
            'user_id' => auth()->id(),
            'premium_service_id' => $service->id,
            'service_name_snapshot' => $service->name,
            'amount_mxn' => $service->price_mxn,
            'pricing_type' => $service->pricing_type,
            'status' => PremiumServicePurchase::STATUS_PENDING_PAYMENT,
            'payment_method' => $method,
        ]);

        if ($method === 'spei') {
            $this->redirect(route('premium.checkout.spei', ['purchase' => $purchase->id]));
            return;
        }

        if ($service->pricing_type === 'custom_quote') {
            // Para custom_quote no hay pasarela — solo notificamos al admin que cotice.
            $this->redirect(route('premium.checkout.quote', ['purchase' => $purchase->id]));
            return;
        }

        // Stripe (one-time o monthly)
        $this->redirect(route('premium.checkout.stripe', ['purchase' => $purchase->id]));
    }
}
