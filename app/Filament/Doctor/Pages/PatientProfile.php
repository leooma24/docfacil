<?php

namespace App\Filament\Doctor\Pages;

use App\Models\Appointment;
use App\Models\MedicalRecord;
use App\Models\Odontogram;
use App\Models\Patient;
use App\Models\Payment;
use App\Models\Prescription;
use Filament\Pages\Page;

class PatientProfile extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-user-circle';

    protected static ?string $title = 'Perfil del Paciente';

    protected static ?string $slug = 'patient-profile';

    protected static bool $shouldRegisterNavigation = false;

    protected static string $view = 'filament.doctor.pages.patient-profile';

    public ?Patient $patient = null;
    public string $activeTab = 'info';

    public function mount(): void
    {
        $patientId = request('patient');
        $this->patient = Patient::where('clinic_id', auth()->user()->clinic_id)
            ->find($patientId);

        if (!$this->patient) {
            $this->redirect(route('filament.doctor.resources.patients.index'));
        }
    }

    public function setTab(string $tab): void
    {
        $this->activeTab = $tab;
    }

    public function getAppointmentsProperty()
    {
        return Appointment::where('patient_id', $this->patient->id)
            ->with(['doctor.user', 'service'])
            ->orderBy('starts_at', 'desc')
            ->limit(20)
            ->get();
    }

    public function getMedicalRecordsProperty()
    {
        return MedicalRecord::where('patient_id', $this->patient->id)
            ->with(['doctor.user'])
            ->orderBy('visit_date', 'desc')
            ->limit(20)
            ->get();
    }

    public function getPrescriptionsProperty()
    {
        return Prescription::where('patient_id', $this->patient->id)
            ->with(['doctor.user', 'items'])
            ->orderBy('prescription_date', 'desc')
            ->limit(20)
            ->get();
    }

    public function getPaymentsProperty()
    {
        return Payment::where('patient_id', $this->patient->id)
            ->with(['service'])
            ->orderBy('payment_date', 'desc')
            ->limit(20)
            ->get();
    }

    public function getOdontogramsProperty()
    {
        return Odontogram::where('patient_id', $this->patient->id)
            ->with(['teeth', 'doctor.user'])
            ->orderBy('evaluation_date', 'desc')
            ->get();
    }

    public function getStatsProperty(): array
    {
        return [
            'total_visits' => MedicalRecord::where('patient_id', $this->patient->id)->count(),
            'total_appointments' => Appointment::where('patient_id', $this->patient->id)->count(),
            'total_paid' => Payment::where('patient_id', $this->patient->id)->where('status', 'paid')->sum('amount'),
            'pending' => Payment::where('patient_id', $this->patient->id)->whereIn('status', ['pending', 'partial'])->sum('amount'),
            'last_visit' => MedicalRecord::where('patient_id', $this->patient->id)->max('visit_date'),
        ];
    }
}
