<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Clinic;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Service;
use App\Models\User;
use App\Services\AppointmentPatternService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QuickVisitTest extends TestCase
{
    use RefreshDatabase;

    private Clinic $clinic;
    private User $user;
    private Doctor $doctor;
    private Patient $patient;
    private Service $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->clinic = Clinic::create(['name' => 'Test Clinic', 'onboarding_status' => 'completed']);
        $this->user = User::forceCreate([
            'name' => 'Dr. Test',
            'email' => 'dr@test.com',
            'password' => bcrypt('x'),
            'role' => 'doctor',
            'clinic_id' => $this->clinic->id,
        ]);
        $this->doctor = Doctor::create([
            'user_id' => $this->user->id,
            'clinic_id' => $this->clinic->id,
            'specialty' => 'ortodoncia',
        ]);
        $this->patient = Patient::create([
            'clinic_id' => $this->clinic->id,
            'first_name' => 'Juan',
            'last_name' => 'Pérez',
            'phone' => '5551234567',
        ]);
        $this->service = Service::create([
            'clinic_id' => $this->clinic->id,
            'name' => 'Ajuste ortodoncia',
            'price' => 500,
            'duration_minutes' => 30,
            'is_active' => true,
        ]);

        $this->actingAs($this->user);
    }

    private function createAppointment(string $status = 'scheduled', $startsAt = null): Appointment
    {
        return Appointment::create([
            'clinic_id' => $this->clinic->id,
            'doctor_id' => $this->doctor->id,
            'patient_id' => $this->patient->id,
            'service_id' => $this->service->id,
            'starts_at' => $startsAt ?? now(),
            'ends_at' => ($startsAt ? $startsAt->copy() : now())->addMinutes(30),
            'status' => $status,
        ]);
    }

    // ─── executeQuickVisit() ────────────────────────────────────────

    public function test_marks_appointment_completed(): void
    {
        $appt = $this->createAppointment('scheduled');

        AppointmentPatternService::executeQuickVisit($appt, ['note' => '']);

        $this->assertEquals('completed', $appt->fresh()->status);
    }

    public function test_creates_medical_record_when_note_provided(): void
    {
        $appt = $this->createAppointment();

        AppointmentPatternService::executeQuickVisit($appt, [
            'note' => 'Ajuste de ligas, sin novedad',
        ]);

        $this->assertDatabaseHas('medical_records', [
            'appointment_id' => $appt->id,
            'patient_id' => $this->patient->id,
            'notes' => 'Ajuste de ligas, sin novedad',
        ]);
    }

    public function test_skips_medical_record_when_note_empty(): void
    {
        $appt = $this->createAppointment();

        AppointmentPatternService::executeQuickVisit($appt, ['note' => '']);

        $this->assertDatabaseMissing('medical_records', [
            'appointment_id' => $appt->id,
        ]);
    }

    public function test_creates_payment_when_charge_enabled(): void
    {
        $appt = $this->createAppointment();

        AppointmentPatternService::executeQuickVisit($appt, [
            'note' => '',
            'charge' => true,
            'amount' => 500,
            'payment_method' => 'cash',
        ]);

        $this->assertDatabaseHas('payments', [
            'appointment_id' => $appt->id,
            'amount' => 500,
            'payment_method' => 'cash',
            'status' => 'paid',
        ]);
    }

    public function test_skips_payment_when_charge_disabled(): void
    {
        $appt = $this->createAppointment();

        AppointmentPatternService::executeQuickVisit($appt, [
            'note' => 'Pre-pagado',
            'charge' => false,
        ]);

        $this->assertDatabaseMissing('payments', [
            'appointment_id' => $appt->id,
        ]);
    }

    public function test_creates_next_appointment_when_date_provided(): void
    {
        $appt = $this->createAppointment();
        $nextDate = now()->addWeeks(4)->setHour(10)->setMinute(0);

        AppointmentPatternService::executeQuickVisit($appt, [
            'note' => '',
            'next_appointment_date' => $nextDate->format('Y-m-d H:i:s'),
            'next_service_id' => $this->service->id,
        ]);

        // 2 citas totales: la original (completada) + la nueva (scheduled)
        $this->assertEquals(2, Appointment::where('patient_id', $this->patient->id)->count());

        $next = Appointment::where('patient_id', $this->patient->id)
            ->where('status', 'scheduled')
            ->first();
        $this->assertNotNull($next);
        $this->assertEquals($this->service->id, $next->service_id);
    }

    public function test_does_not_create_next_appointment_when_date_empty(): void
    {
        $appt = $this->createAppointment();

        AppointmentPatternService::executeQuickVisit($appt, [
            'note' => 'Sin próxima cita',
        ]);

        // Solo la cita original existe
        $this->assertEquals(1, Appointment::where('patient_id', $this->patient->id)->count());
    }

    // ─── suggestNextDate() ──────────────────────────────────────────

    public function test_pattern_service_returns_null_for_patient_with_no_history(): void
    {
        $suggested = AppointmentPatternService::suggestNextDate(
            $this->patient->id,
            $this->clinic->id
        );

        $this->assertNull($suggested);
    }

    public function test_pattern_service_returns_null_for_only_one_completed_visit(): void
    {
        $this->createAppointment('completed', now()->subWeeks(4));

        $suggested = AppointmentPatternService::suggestNextDate(
            $this->patient->id,
            $this->clinic->id
        );

        $this->assertNull($suggested);
    }

    public function test_pattern_service_suggests_based_on_average_interval(): void
    {
        $this->createAppointment('completed', now()->subDays(84));
        $this->createAppointment('completed', now()->subDays(56));
        $this->createAppointment('completed', now()->subDays(28));

        $suggested = AppointmentPatternService::suggestNextDate(
            $this->patient->id,
            $this->clinic->id
        );

        $this->assertNotNull($suggested);
        $expectedDate = now()->addDays(28);
        $this->assertTrue(
            $suggested->between($expectedDate->copy()->subDay(), $expectedDate->copy()->addDay()),
            "Sugerencia fue {$suggested->toDateString()}, esperaba ~{$expectedDate->toDateString()}"
        );
    }

    public function test_pattern_service_clamps_to_min_7_days(): void
    {
        $this->createAppointment('completed', now()->subDays(4));
        $this->createAppointment('completed', now()->subDays(2));

        $suggested = AppointmentPatternService::suggestNextDate(
            $this->patient->id,
            $this->clinic->id
        );

        $this->assertNotNull($suggested);
        $this->assertGreaterThanOrEqual(
            now()->addDays(7)->startOfDay()->timestamp,
            $suggested->timestamp
        );
    }

    public function test_pattern_service_clamps_to_max_180_days(): void
    {
        $this->createAppointment('completed', now()->subDays(730));
        $this->createAppointment('completed', now()->subDays(365));

        $suggested = AppointmentPatternService::suggestNextDate(
            $this->patient->id,
            $this->clinic->id
        );

        $this->assertNotNull($suggested);
        $this->assertLessThanOrEqual(
            now()->addDays(181)->endOfDay()->timestamp,
            $suggested->timestamp
        );
    }
}
