<?php

namespace App\Filament\Doctor\Pages;

use App\Models\ClinicAddon;
use App\Models\Doctor;
use App\Models\Service;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Livewire\WithFileUploads;

class Onboarding extends Page implements HasForms
{
    use InteractsWithForms;
    use WithFileUploads;

    protected static ?string $slug = 'configuracion';

    protected static ?string $title = 'Configurar mi consultorio';

    protected static bool $shouldRegisterNavigation = false;

    protected static string $view = 'filament.doctor.pages.onboarding';

    public int $step = 1;

    public string $clinic_name = '';
    public string $clinic_phone = '';
    public string $clinic_address = '';
    public string $clinic_city = '';

    /** @var \Livewire\TemporaryUploadedFile|null Logo subido en step 1 (opcional) */
    public $logo = null;

    public string $specialty = '';
    public string $license_number = '';
    public string $doctor_phone = '';

    public array $quick_services = [];
    public bool $suggestions_loaded = false;

    /** @var array<string> Slugs de add-ons que el doctor decidio activar */
    public array $addons_activate = [];

    public const TOTAL_STEPS = 5;

    /**
     * Catalogo de servicios sugeridos por especialidad.
     * Se carga al entrar a step 3 si el dentista aun no agrego servicios.
     * El doctor puede editar/eliminar antes de continuar.
     */
    private const SERVICE_SUGGESTIONS = [
        'general' => [
            ['name' => 'Limpieza dental', 'price' => 400, 'duration' => 30, 'recall_months' => 6],
            ['name' => 'Resina (obturación)', 'price' => 600, 'duration' => 45],
            ['name' => 'Extracción simple', 'price' => 500, 'duration' => 30],
            ['name' => 'Revisión y diagnóstico', 'price' => 300, 'duration' => 30, 'recall_months' => 12],
            ['name' => 'Radiografía periapical', 'price' => 200, 'duration' => 15],
        ],
        'ortodoncia' => [
            ['name' => 'Estudio inicial ortodoncia', 'price' => 1500, 'duration' => 60],
            ['name' => 'Brackets metálicos (colocación)', 'price' => 8000, 'duration' => 90],
            ['name' => 'Brackets estéticos (colocación)', 'price' => 12000, 'duration' => 90],
            ['name' => 'Ajuste mensual', 'price' => 500, 'duration' => 30, 'recall_months' => 1],
            ['name' => 'Retenedor', 'price' => 2500, 'duration' => 45],
        ],
        'odontopediatria' => [
            ['name' => 'Limpieza dental pediátrica', 'price' => 350, 'duration' => 30, 'recall_months' => 6],
            ['name' => 'Sellador de fosetas', 'price' => 400, 'duration' => 30],
            ['name' => 'Pulpotomía', 'price' => 1500, 'duration' => 60],
            ['name' => 'Extracción dental temporal', 'price' => 400, 'duration' => 30],
            ['name' => 'Aplicación de flúor', 'price' => 300, 'duration' => 20, 'recall_months' => 6],
        ],
        'estetica' => [
            ['name' => 'Blanqueamiento dental', 'price' => 3500, 'duration' => 90],
            ['name' => 'Carillas de porcelana (por pieza)', 'price' => 6000, 'duration' => 60],
            ['name' => 'Carillas de resina (por pieza)', 'price' => 2500, 'duration' => 60],
            ['name' => 'Diseño de sonrisa', 'price' => 25000, 'duration' => 120],
            ['name' => 'Resina estética anterior', 'price' => 1200, 'duration' => 60],
        ],
        'implantologia' => [
            ['name' => 'Implante unitario', 'price' => 18000, 'duration' => 90],
            ['name' => 'Pilar de cicatrización', 'price' => 3500, 'duration' => 30],
            ['name' => 'Corona sobre implante', 'price' => 8500, 'duration' => 60],
            ['name' => 'Injerto óseo', 'price' => 6000, 'duration' => 90],
        ],
        'endodoncia' => [
            ['name' => 'Endodoncia unirradicular', 'price' => 2500, 'duration' => 90],
            ['name' => 'Endodoncia birradicular', 'price' => 3000, 'duration' => 90],
            ['name' => 'Endodoncia multirradicular', 'price' => 3500, 'duration' => 120],
            ['name' => 'Retratamiento endodóntico', 'price' => 4000, 'duration' => 120],
        ],
        'periodoncia' => [
            ['name' => 'Curetaje (por cuadrante)', 'price' => 800, 'duration' => 45, 'recall_months' => 6],
            ['name' => 'Cirugía periodontal', 'price' => 3500, 'duration' => 90],
            ['name' => 'Mantenimiento periodontal', 'price' => 600, 'duration' => 45, 'recall_months' => 3],
        ],
        'maxilofacial' => [
            ['name' => 'Cirugía 3er molar (por pieza)', 'price' => 3500, 'duration' => 60],
            ['name' => 'Apicectomía', 'price' => 4500, 'duration' => 90],
            ['name' => 'Frenectomía', 'price' => 2500, 'duration' => 60],
        ],
    ];

    public function mount(): void
    {
        $user = auth()->user();
        $clinic = $user->clinic;
        $doctor = $user->doctor;

        if ($clinic && $clinic->onboarding_status === 'completed') {
            $this->redirect(route('filament.doctor.pages.dashboard'));
            return;
        }

        if ($clinic) {
            $this->clinic_name = $clinic->name ?? '';
            $this->clinic_phone = $clinic->phone ?? '';
            $this->clinic_address = $clinic->address ?? '';
            $this->clinic_city = $clinic->city ?? '';
        }

        if ($doctor) {
            $this->specialty = $doctor->specialty ?? '';
            $this->license_number = $doctor->license_number ?? '';
            $this->doctor_phone = $doctor->phone ?? '';
        }
    }

    public function nextStep(): void
    {
        // Al avanzar a step 3 (servicios), si esta vacio sugerir por especialidad
        if ($this->step === 2 && empty($this->quick_services) && !$this->suggestions_loaded) {
            $this->loadServiceSuggestions();
        }
        $previousStep = $this->step;
        $this->step = min($this->step + 1, self::TOTAL_STEPS);

        // Analytics: flash event para el siguiente render
        session()->push('analytics_events', [
            'name' => 'onboarding_step_completed',
            'params' => ['step' => $previousStep],
        ]);
    }

    public function prevStep(): void
    {
        $this->step = max($this->step - 1, 1);
    }

    /**
     * Pre-cargar quick_services con sugerencias basadas en specialty.
     * Match por keyword (case-insensitive). Default: general.
     */
    protected function loadServiceSuggestions(): void
    {
        $spec = strtolower($this->specialty ?? '');
        $key = match (true) {
            str_contains($spec, 'orto')                      => 'ortodoncia',
            str_contains($spec, 'pediat') || str_contains($spec, 'niños') => 'odontopediatria',
            str_contains($spec, 'estét') || str_contains($spec, 'estet') => 'estetica',
            str_contains($spec, 'implant')                   => 'implantologia',
            str_contains($spec, 'endod')                     => 'endodoncia',
            str_contains($spec, 'period')                    => 'periodoncia',
            str_contains($spec, 'maxil') || str_contains($spec, 'cirug') => 'maxilofacial',
            default                                          => 'general',
        };

        $this->quick_services = array_values(self::SERVICE_SUGGESTIONS[$key]);
        $this->suggestions_loaded = true;
    }

    public function addService(): void
    {
        $this->quick_services[] = ['name' => '', 'price' => '', 'duration' => '30'];
    }

    public function removeService(int $index): void
    {
        unset($this->quick_services[$index]);
        $this->quick_services = array_values($this->quick_services);
    }

    public function toggleAddon(string $slug): void
    {
        if (in_array($slug, $this->addons_activate, true)) {
            $this->addons_activate = array_values(array_diff($this->addons_activate, [$slug]));
        } else {
            $this->addons_activate[] = $slug;
        }
    }

    public function getAvailableAddonsProperty(): array
    {
        return array_values(array_filter(config('addons', []), fn ($a) => $a['available'] ?? false));
    }

    /**
     * URL pública del portal de agendamiento si la clínica tiene el feature.
     * Solo planes Pro+ tienen 'public_booking'. En step 5 lo mostramos como
     * preview para que el doctor pueda compartirlo de inmediato.
     */
    public function getPortalUrlProperty(): ?string
    {
        $clinic = auth()->user()->clinic ?? null;
        if (!$clinic || !$clinic->hasFeature('public_booking')) {
            return null;
        }
        return route('public.booking.show', ['slug' => $clinic->slug]);
    }

    public function completeOnboarding(): void
    {
        $user = auth()->user();
        $clinic = $user->clinic;
        $doctor = $user->doctor;

        if ($clinic) {
            $update = [
                'name' => $this->clinic_name ?: $clinic->name,
                'phone' => $this->clinic_phone ?: null,
                'address' => $this->clinic_address ?: null,
                'city' => $this->clinic_city ?: null,
                'onboarding_status' => 'completed',
            ];

            // Solo escribimos 'logo' si el doctor subio uno nuevo. Si lo deja vacio
            // preservamos cualquier logo existente (importante en re-runs del wizard).
            if ($this->logo) {
                try {
                    $update['logo'] = $this->logo->store('clinic-logos', 'public');
                } catch (\Throwable $e) {
                    \Illuminate\Support\Facades\Log::warning('Onboarding logo upload failed', [
                        'clinic_id' => $clinic->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            $clinic->update($update);
        }

        if ($doctor) {
            $doctor->update([
                'specialty' => $this->specialty ?: null,
                'license_number' => $this->license_number ?: null,
                'phone' => $this->doctor_phone ?: null,
            ]);
        }

        foreach ($this->quick_services as $svc) {
            if (!empty($svc['name']) && !empty($svc['price'])) {
                Service::create([
                    'clinic_id' => $clinic->id,
                    'name' => $svc['name'],
                    'price' => (float) $svc['price'],
                    'duration_minutes' => (int) ($svc['duration'] ?: 30),
                    'recall_months' => isset($svc['recall_months']) ? (int) $svc['recall_months'] : null,
                    'is_active' => true,
                ]);
            }
        }

        // Activar add-ons elegidos como trial 30 dias
        foreach ($this->addons_activate as $slug) {
            $cfg = config("addons.{$slug}");
            if (!$cfg || !($cfg['available'] ?? false)) continue;

            ClinicAddon::firstOrCreate(
                ['clinic_id' => $clinic->id, 'addon_slug' => $slug],
                [
                    'status' => 'trial',
                    'monthly_price' => $cfg['monthly_price'],
                    'billing_cycle' => 'monthly',
                    'trial_ends_at' => now()->addDays($cfg['beta_trial_days'] ?? 30),
                    'started_at' => now(),
                ]
            );
        }

        $servicesCount = collect($this->quick_services)->filter(fn ($s) => !empty($s['name']) && !empty($s['price']))->count();
        $addonCount = count($this->addons_activate);

        // Analytics: onboarding_completed dispara en el dashboard (proximo render)
        session()->push('analytics_events', [
            'name' => 'onboarding_completed',
            'params' => [
                'services_count' => $servicesCount,
                'addons_activated' => $addonCount,
            ],
        ]);

        Notification::make()
            ->title('¡Consultorio listo!')
            ->body("Configuraste {$servicesCount} servicios" . ($addonCount ? " + {$addonCount} add-ons activos" : '') . '. Ya puedes empezar a atender pacientes.')
            ->success()
            ->send();

        $this->redirect(route('filament.doctor.pages.dashboard'));
    }

    public function skipOnboarding(): void
    {
        $clinic = auth()->user()->clinic;
        if ($clinic) {
            $clinic->update(['onboarding_status' => 'completed']);
        }
        $this->redirect(route('filament.doctor.pages.dashboard'));
    }
}
