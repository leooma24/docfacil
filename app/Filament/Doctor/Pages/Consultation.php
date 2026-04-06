<?php

namespace App\Filament\Doctor\Pages;

use App\Models\Appointment;
use App\Models\MedicalRecord;
use App\Models\Payment;
use App\Models\Prescription;
use App\Models\PrescriptionItem;
use App\Models\Service;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class Consultation extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-play-circle';

    protected static ?string $navigationLabel = 'Consulta';

    protected static ?string $title = 'Flujo de Consulta';

    protected static ?string $slug = 'consultation';

    protected static bool $shouldRegisterNavigation = false;

    protected static string $view = 'filament.doctor.pages.consultation';

    public ?Appointment $appointment = null;
    public int $currentStep = 1;
    public bool $completed = false;
    public ?int $savedPrescriptionId = null;

    // Step 1: Vital signs
    public ?string $blood_pressure = '';
    public ?string $heart_rate = '';
    public ?string $temperature = '';
    public ?string $weight = '';

    // Step 2: Diagnosis
    public ?string $chief_complaint = '';
    public ?string $diagnosis = '';
    public ?string $treatment = '';
    public ?string $medical_notes = '';

    // Step 3: Prescription
    public array $medications = [];
    public ?string $prescription_notes = '';

    // Step 4: Payment
    public ?string $payment_service_id = null;
    public ?string $payment_amount = '';
    public string $payment_method = 'cash';

    // Step 5: Next appointment
    public ?string $next_appointment_date = null;
    public ?string $next_appointment_service_id = null;

    public function mount(): void
    {
        $appointmentId = request('appointment');

        if ($appointmentId) {
            $this->appointment = Appointment::with(['patient', 'doctor.user', 'service', 'clinic'])
                ->where('clinic_id', auth()->user()->clinic_id)
                ->find($appointmentId);
        }

        if (!$this->appointment) {
            $this->redirect(route('filament.doctor.pages.dashboard'));
            return;
        }

        // Mark as in progress
        if (in_array($this->appointment->status, ['scheduled', 'confirmed'])) {
            $this->appointment->update(['status' => 'in_progress']);
        }

        // Pre-fill payment amount from service
        if ($this->appointment->service) {
            $this->payment_service_id = (string) $this->appointment->service_id;
            $this->payment_amount = (string) $this->appointment->service->price;
        }
    }

    public function nextStep(): void
    {
        $this->currentStep = min($this->currentStep + 1, 5);
    }

    public function prevStep(): void
    {
        $this->currentStep = max($this->currentStep - 1, 1);
    }

    public function goToStep(int $step): void
    {
        $this->currentStep = $step;
    }

    public function saveAndComplete(): void
    {
        $clinicId = auth()->user()->clinic_id;

        // Save medical record
        $vitalSigns = array_filter([
            'blood_pressure' => $this->blood_pressure,
            'heart_rate' => $this->heart_rate,
            'temperature' => $this->temperature,
            'weight' => $this->weight,
        ]);

        $medicalRecord = MedicalRecord::create([
            'clinic_id' => $clinicId,
            'patient_id' => $this->appointment->patient_id,
            'doctor_id' => $this->appointment->doctor_id,
            'appointment_id' => $this->appointment->id,
            'visit_date' => now()->toDateString(),
            'chief_complaint' => $this->chief_complaint ?: null,
            'diagnosis' => $this->diagnosis ?: null,
            'treatment' => $this->treatment ?: null,
            'notes' => $this->medical_notes ?: null,
            'vital_signs' => !empty($vitalSigns) ? $vitalSigns : null,
        ]);

        // Save prescription if medications exist
        if (!empty($this->medications)) {
            $prescription = Prescription::create([
                'clinic_id' => $clinicId,
                'patient_id' => $this->appointment->patient_id,
                'doctor_id' => $this->appointment->doctor_id,
                'medical_record_id' => $medicalRecord->id,
                'prescription_date' => now()->toDateString(),
                'diagnosis' => $this->diagnosis ?: null,
                'notes' => $this->prescription_notes ?: null,
            ]);

            foreach ($this->medications as $med) {
                if (!empty($med['medication'])) {
                    PrescriptionItem::create([
                        'prescription_id' => $prescription->id,
                        'medication' => $med['medication'],
                        'dosage' => $med['dosage'] ?? null,
                        'frequency' => $med['frequency'] ?? null,
                        'duration' => $med['duration'] ?? null,
                        'instructions' => $med['instructions'] ?? null,
                    ]);
                }
            }
        }

        // Save payment if amount > 0
        if (!empty($this->payment_amount) && $this->payment_amount > 0) {
            Payment::create([
                'clinic_id' => $clinicId,
                'patient_id' => $this->appointment->patient_id,
                'appointment_id' => $this->appointment->id,
                'service_id' => $this->payment_service_id ?: null,
                'amount' => $this->payment_amount,
                'payment_method' => $this->payment_method,
                'status' => 'paid',
                'payment_date' => now()->toDateString(),
            ]);
        }

        // Create next appointment if date set
        if (!empty($this->next_appointment_date)) {
            Appointment::create([
                'clinic_id' => $clinicId,
                'doctor_id' => $this->appointment->doctor_id,
                'patient_id' => $this->appointment->patient_id,
                'service_id' => $this->next_appointment_service_id ?: null,
                'starts_at' => $this->next_appointment_date,
                'ends_at' => \Carbon\Carbon::parse($this->next_appointment_date)->addMinutes(30),
                'status' => 'scheduled',
            ]);
        }

        // Mark appointment as completed
        $this->appointment->update(['status' => 'completed']);

        // Store prescription ID for PDF download
        if (isset($prescription)) {
            $this->savedPrescriptionId = $prescription->id;
        }

        $this->completed = true;
        $this->currentStep = 6;
    }

    public function getServicesProperty(): array
    {
        return Service::where('clinic_id', auth()->user()->clinic_id)
            ->where('is_active', true)
            ->pluck('name', 'id')
            ->toArray();
    }

    public function updatedPaymentServiceId($value): void
    {
        if ($value) {
            $service = Service::find($value);
            if ($service) {
                $this->payment_amount = (string) $service->price;
            }
        }
    }
}
