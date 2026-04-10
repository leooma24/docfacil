<?php

namespace App\Filament\Doctor\Pages;

use App\Models\Doctor;
use App\Models\Service;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class Onboarding extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $slug = 'configuracion';

    protected static ?string $title = 'Configurar mi consultorio';

    protected static bool $shouldRegisterNavigation = false;

    protected static string $view = 'filament.doctor.pages.onboarding';

    public int $step = 1;

    // Step 1: Clinic info
    public string $clinic_name = '';
    public string $clinic_phone = '';
    public string $clinic_address = '';
    public string $clinic_city = '';

    // Step 2: Doctor profile
    public string $specialty = '';
    public string $license_number = '';
    public string $doctor_phone = '';

    // Step 3: Services (quick add)
    public array $quick_services = [];

    public function mount(): void
    {
        $user = auth()->user();
        $clinic = $user->clinic;
        $doctor = $user->doctor;

        // If already onboarded, redirect to dashboard
        if ($clinic && $clinic->onboarding_status === 'completed') {
            $this->redirect(route('filament.doctor.pages.dashboard'));
            return;
        }

        // Pre-fill existing data
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
        $this->step = min($this->step + 1, 4);
    }

    public function prevStep(): void
    {
        $this->step = max($this->step - 1, 1);
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

    public function completeOnboarding(): void
    {
        $user = auth()->user();
        $clinic = $user->clinic;
        $doctor = $user->doctor;

        // Update clinic
        if ($clinic) {
            $clinic->update([
                'name' => $this->clinic_name ?: $clinic->name,
                'phone' => $this->clinic_phone ?: null,
                'address' => $this->clinic_address ?: null,
                'city' => $this->clinic_city ?: null,
                'onboarding_status' => 'completed',
            ]);
        }

        // Update doctor
        if ($doctor) {
            $doctor->update([
                'specialty' => $this->specialty ?: null,
                'license_number' => $this->license_number ?: null,
                'phone' => $this->doctor_phone ?: null,
            ]);
        }

        // Create services
        foreach ($this->quick_services as $svc) {
            if (!empty($svc['name']) && !empty($svc['price'])) {
                Service::create([
                    'clinic_id' => $clinic->id,
                    'name' => $svc['name'],
                    'price' => $svc['price'],
                    'duration_minutes' => $svc['duration'] ?: 30,
                ]);
            }
        }

        Notification::make()
            ->title('Consultorio configurado')
            ->body('Todo listo. Ya puedes empezar a atender pacientes.')
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
