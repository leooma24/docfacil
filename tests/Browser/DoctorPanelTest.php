<?php

namespace Tests\Browser;

use App\Models\Appointment;
use App\Models\Clinic;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class DoctorPanelTest extends DuskTestCase
{
    use DatabaseMigrations;

    private function createDoctor(): User
    {
        $clinic = Clinic::create(['name' => 'Test Clinic', 'onboarding_status' => 'completed']);
        $user = User::forceCreate([
            'name' => 'Dr. Browser Test',
            'email' => 'dusk@test.com',
            'password' => bcrypt('password'),
            'role' => 'doctor',
            'clinic_id' => $clinic->id,
        ]);
        Doctor::create([
            'user_id' => $user->id,
            'clinic_id' => $clinic->id,
            'specialty' => 'General',
        ]);

        return $user;
    }

    private function assertNoJsErrors(Browser $browser): void
    {
        $logs = $browser->driver->manage()->getLog('browser');

        $errors = collect($logs)->filter(function ($log) {
            // Filter to actual errors, ignore warnings and chrome-extension issues
            return $log['level'] === 'SEVERE'
                && ! str_contains($log['message'], 'chrome-extension')
                && ! str_contains($log['message'], 'Browsing Topics')
                && ! str_contains($log['message'], 'favicon.ico');
        });

        $this->assertEmpty(
            $errors->toArray(),
            'JS console errors found: ' . $errors->pluck('message')->implode("\n")
        );
    }

    public function test_consultation_page_no_js_errors(): void
    {
        $user = $this->createDoctor();

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                ->visit('/doctor/consulta')
                ->waitForText('Consulta', 10)
                ->pause(2000); // Let Livewire/Alpine settle

            $this->assertNoJsErrors($browser);
        });
    }

    public function test_consultation_with_appointment_no_js_errors(): void
    {
        $user = $this->createDoctor();
        $clinic = $user->clinic;
        $doctor = $user->doctor;

        $patient = Patient::create([
            'clinic_id' => $clinic->id,
            'first_name' => 'Test',
            'last_name' => 'Patient',
        ]);
        $service = Service::create([
            'clinic_id' => $clinic->id,
            'name' => 'Consulta',
            'price' => 500,
            'is_active' => true,
        ]);
        $appointment = Appointment::create([
            'clinic_id' => $clinic->id,
            'doctor_id' => $doctor->id,
            'patient_id' => $patient->id,
            'service_id' => $service->id,
            'starts_at' => now(),
            'ends_at' => now()->addMinutes(30),
            'status' => 'scheduled',
        ]);

        $this->browse(function (Browser $browser) use ($user, $appointment) {
            $browser->loginAs($user)
                ->visit('/doctor/consulta?appointment=' . $appointment->id)
                ->waitForText('Consulta', 10)
                ->pause(2000);

            $this->assertNoJsErrors($browser);
        });
    }

    public function test_patients_list_no_js_errors(): void
    {
        $user = $this->createDoctor();

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                ->visit('/doctor/pacientes')
                ->waitForText('Pacientes', 10)
                ->pause(1000);

            $this->assertNoJsErrors($browser);
        });
    }

    public function test_appointments_list_no_js_errors(): void
    {
        $user = $this->createDoctor();

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                ->visit('/doctor/citas')
                ->waitForText('Citas', 10)
                ->pause(1000);

            $this->assertNoJsErrors($browser);
        });
    }

    public function test_services_list_no_js_errors(): void
    {
        $user = $this->createDoctor();

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                ->visit('/doctor/servicios')
                ->waitForText('Servicios', 10)
                ->pause(1000);

            $this->assertNoJsErrors($browser);
        });
    }

    public function test_medical_records_list_no_js_errors(): void
    {
        $user = $this->createDoctor();

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                ->visit('/doctor/expediente-clinico')
                ->waitForText('Expediente', 10)
                ->pause(1000);

            $this->assertNoJsErrors($browser);
        });
    }

    public function test_prescriptions_list_no_js_errors(): void
    {
        $user = $this->createDoctor();

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                ->visit('/doctor/recetas')
                ->waitForText('Recetas', 10)
                ->pause(1000);

            $this->assertNoJsErrors($browser);
        });
    }

    public function test_payments_list_no_js_errors(): void
    {
        $user = $this->createDoctor();

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                ->visit('/doctor/cobros')
                ->waitForText('Cobros', 10)
                ->pause(1000);

            $this->assertNoJsErrors($browser);
        });
    }

    public function test_calendar_page_no_js_errors(): void
    {
        $user = $this->createDoctor();

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                ->visit('/doctor/calendar')
                ->pause(3000); // Calendar JS takes longer to load

            $this->assertNoJsErrors($browser);
        });
    }
}
