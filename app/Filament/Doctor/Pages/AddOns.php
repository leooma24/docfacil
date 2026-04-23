<?php

namespace App\Filament\Doctor\Pages;

use App\Models\ClinicAddon;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

/**
 * Marketplace de add-ons. El doctor ve el catalogo en config/addons.php,
 * puede activar cualquier add-on con trial de 30 dias (beta) o cancelar
 * add-ons activos.
 *
 * Billing (Stripe multi-item subscription) vendra despues. Por ahora
 * activacion = ClinicAddon con status='trial' trial_ends_at = now()+30d.
 */
class AddOns extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-squares-plus';

    protected static ?string $navigationLabel = 'Add-ons';

    protected static ?string $title = 'Add-ons disponibles';

    protected static string $view = 'filament.doctor.pages.add-ons';

    protected static ?int $navigationSort = 99;

    protected static ?string $navigationGroup = 'Mi cuenta';

    public function getCatalog(): array
    {
        $catalog = config('addons', []);
        $clinicId = auth()->user()->clinic_id;

        $activeAddons = ClinicAddon::where('clinic_id', $clinicId)
            ->active()
            ->pluck('addon_slug')
            ->toArray();

        return collect($catalog)->filter(fn ($a) => $a['available'] ?? true)
            ->map(function ($a) use ($activeAddons) {
                $a['is_active'] = in_array($a['slug'], $activeAddons, true);
                $addon = ClinicAddon::where('clinic_id', auth()->user()->clinic_id)
                    ->where('addon_slug', $a['slug'])
                    ->latest()
                    ->first();
                $a['trial_ends_at'] = $addon?->trial_ends_at;
                $a['status'] = $addon?->status;
                return $a;
            })->values()->all();
    }

    public function activateAddon(string $slug): void
    {
        $catalog = config('addons', []);
        $addonCfg = $catalog[$slug] ?? null;
        abort_unless($addonCfg && ($addonCfg['available'] ?? false), 404);

        $clinicId = auth()->user()->clinic_id;
        $trialDays = (int) ($addonCfg['beta_trial_days'] ?? 0);

        $existing = ClinicAddon::where('clinic_id', $clinicId)
            ->where('addon_slug', $slug)
            ->first();

        if ($existing) {
            // Re-activar
            $existing->update([
                'status' => $trialDays > 0 ? 'trial' : 'active',
                'trial_ends_at' => $trialDays > 0 ? now()->addDays($trialDays) : null,
                'started_at' => $existing->started_at ?? now(),
                'cancelled_at' => null,
                'ends_at' => null,
            ]);
        } else {
            ClinicAddon::create([
                'clinic_id' => $clinicId,
                'addon_slug' => $slug,
                'status' => $trialDays > 0 ? 'trial' : 'active',
                'monthly_price' => $addonCfg['monthly_price'],
                'billing_cycle' => 'monthly',
                'trial_ends_at' => $trialDays > 0 ? now()->addDays($trialDays) : null,
                'started_at' => now(),
            ]);
        }

        Notification::make()
            ->title($addonCfg['name'] . ' activado')
            ->body($trialDays > 0
                ? "Tienes {$trialDays} días gratis como beta tester. Después son \${$addonCfg['monthly_price']}/mes."
                : "Add-on activo. Se cobrará \${$addonCfg['monthly_price']}/mes junto con tu plan.")
            ->success()
            ->send();
    }

    public function cancelAddon(string $slug): void
    {
        $clinicId = auth()->user()->clinic_id;
        $addon = ClinicAddon::where('clinic_id', $clinicId)
            ->where('addon_slug', $slug)
            ->active()
            ->first();

        if (!$addon) return;

        // Si esta en trial lo cancelamos inmediato; si esta pagado, hasta fin de periodo
        if ($addon->status === 'trial') {
            $addon->update([
                'status' => 'cancelled',
                'cancelled_at' => now(),
                'ends_at' => now(),
            ]);
        } else {
            // Paga hasta fin de periodo mensual
            $addon->update([
                'status' => 'cancelled',
                'cancelled_at' => now(),
                'ends_at' => $addon->started_at->copy()->addDays(30 * ceil(now()->diffInDays($addon->started_at) / 30)),
            ]);
        }

        $name = config("addons.{$slug}.name", $slug);
        Notification::make()
            ->title("{$name} cancelado")
            ->body('Conservarás el acceso hasta el fin de tu periodo actual.')
            ->warning()
            ->send();
    }
}
