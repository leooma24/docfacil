<?php

namespace App\Filament\Doctor\Pages;

use App\Models\Appointment;
use App\Models\MedicalRecord;
use App\Models\Odontogram;
use App\Models\Patient;
use App\Models\Payment;
use App\Models\Prescription;
use App\Services\PatientAISummaryService;
use Filament\Pages\Page;
use Livewire\WithFileUploads;

class PatientProfile extends Page
{
    use WithFileUploads;

    /** El archivo que se va a subir (foto de la hoja vieja, radiografía, PDF). */
    public $archivoNuevo = null;

    public string $notaDelArchivo = '';

    protected static ?string $navigationIcon = 'heroicon-o-user-circle';

    protected static ?string $title = 'Perfil del Paciente';

    protected static ?string $slug = 'perfil-paciente';

    protected static bool $shouldRegisterNavigation = false;

    protected static string $view = 'filament.doctor.pages.patient-profile';

    public ?Patient $patient = null;
    public string $activeTab = 'info';
    public ?string $aiSummary = null;
    public bool $loadingSummary = false;
    public ?string $generatedMessage = null;
    public bool $generatingMessage = false;
    public string $messageType = 'reminder';

    public function mount(): void
    {
        $patientId = request('patient');
        $this->patient = Patient::where('clinic_id', auth()->user()->clinic_id)
            ->find($patientId);

        if (!$this->patient) {
            $this->redirect(route('filament.doctor.resources.pacientes.index'));
        }
    }

    /** "Cobrar" de Lo que sigue: todo lo vencido, con el total ya puesto. */
    public function cobrarVencidoAction(): \Filament\Actions\Action
    {
        return \App\Filament\Doctor\Actions\CobrarAbono::make('cobrarVencido', fn () => Payment::where('clinic_id', auth()->user()->clinic_id)
            ->where('patient_id', $this->patient?->id)
            ->overdue()
            ->get())
            ->button()
            ->size('sm');
    }

    /** "Cobrar" de un cobro de la pestaña Cobros, solo si es de este paciente. */
    public function cobrarUnoAction(): \Filament\Actions\Action
    {
        return \App\Filament\Doctor\Actions\CobrarAbono::make('cobrarUno', fn (array $arguments) => Payment::where('clinic_id', auth()->user()->clinic_id)
            ->where('patient_id', $this->patient?->id)
            ->whereKey($arguments['payment'] ?? null)
            ->get())
            ->link()
            ->size('sm');
    }

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\Action::make('descargar_expediente')
                ->label('Descargar expediente (PDF)')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('gray')
                ->visible(fn () => $this->patient !== null)
                ->action(fn () => $this->descargarExpediente()),
        ];
    }

    /**
     * El expediente del paciente completo, en un PDF.
     *
     * Para cuando el paciente pide su información (ley de datos personales,
     * art. 29), un resumen clínico (NOM-013 5.15) o se cambia de consultorio.
     * Antes no había forma de sacarlo de la plataforma. Queda registrado quién
     * lo descargó y cuándo.
     */
    public function descargarExpediente()
    {
        $paciente = $this->patient->load([
            'clinic',
            'medicalRecords' => fn ($q) => $q->with('doctor.user')->orderByDesc('visit_date')->orderByDesc('created_at'),
            'prescriptions' => fn ($q) => $q->with(['doctor.user', 'items'])->orderByDesc('prescription_date'),
            'consentForms' => fn ($q) => $q->orderByDesc('created_at'),
            'odontograms' => fn ($q) => $q->with(['doctor.user', 'teeth'])->orderByDesc('evaluation_date'),
        ]);

        activity()
            ->performedOn($paciente)
            ->causedBy(auth()->user())
            ->log('Descargó el expediente en PDF');

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.expediente', ['patient' => $paciente]);

        return response()->streamDownload(
            fn () => print($pdf->output()),
            'expediente-' . \Illuminate\Support\Str::slug($paciente->full_name) . '.pdf'
        );
    }

    public function setTab(string $tab): void
    {
        $this->activeTab = $tab;
    }

    /**
     * Sube un archivo a su expediente: la foto de su hoja de papel, una
     * radiografía o un PDF. Al disco privado, por consultorio y paciente.
     */
    public function subirArchivo(): void
    {
        if (! $this->patient) {
            return;
        }

        $this->validate([
            'archivoNuevo' => 'required|file|max:10240|mimes:jpg,jpeg,png,webp,pdf',
            'notaDelArchivo' => 'nullable|string|max:255',
        ], [
            'archivoNuevo.mimes' => 'Solo fotos (JPG, PNG, WebP) o PDF.',
            'archivoNuevo.max' => 'El archivo pesa más de 10 MB.',
            'archivoNuevo.required' => 'Elija o tome una foto primero.',
        ]);

        $carpeta = "patient-files/{$this->patient->clinic_id}/{$this->patient->id}";
        $path = $this->archivoNuevo->store($carpeta, 'local');

        \App\Models\PatientFile::create([
            'clinic_id' => $this->patient->clinic_id,
            'patient_id' => $this->patient->id,
            'path' => $path,
            'nombre' => $this->archivoNuevo->getClientOriginalName(),
            'mime' => $this->archivoNuevo->getMimeType(),
            'size' => $this->archivoNuevo->getSize(),
            'nota' => trim($this->notaDelArchivo) ?: null,
            'subido_por' => auth()->id(),
        ]);

        $this->reset(['archivoNuevo', 'notaDelArchivo']);
        \Filament\Notifications\Notification::make()->title('Guardado en su expediente')->success()->send();
    }

    /** Los archivos del paciente, del más nuevo al más viejo. */
    public function getArchivosProperty()
    {
        return $this->patient
            ? \App\Models\PatientFile::where('patient_id', $this->patient->id)->where('clinic_id', $this->patient->clinic_id)->latest()->get()
            : collect();
    }

    public function loadAiSummary(): void
    {
        if (!$this->patient) return;
        $this->loadingSummary = true;
        $this->aiSummary = app(PatientAISummaryService::class)->summarize($this->patient);
        $this->loadingSummary = false;
    }

    public function refreshAiSummary(): void
    {
        if (!$this->patient) return;
        app(PatientAISummaryService::class)->invalidate($this->patient);
        $this->loadAiSummary();
    }

    public function generateMessage(string $type): void
    {
        if (!$this->patient) return;
        $this->messageType = $type;
        $this->generatingMessage = true;
        $this->generatedMessage = app(\App\Services\PatientMessageAIService::class)->generate($this->patient, $type);
        $this->generatingMessage = false;
    }

    public function getWhatsappUrlProperty(): ?string
    {
        if (!$this->patient?->phone || !$this->generatedMessage) return null;
        $phone = preg_replace('/\D/', '', $this->patient->phone);
        return "https://wa.me/52{$phone}?text=" . urlencode($this->generatedMessage);
    }

    public function closeMessage(): void
    {
        $this->generatedMessage = null;
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
            ->with(['doctor.user', 'prescriptions', 'appointment.payments'])
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
            ->yaToca()
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

    /** Lo que toca hacer con este paciente, cada cosa con su liga. */
    public function getLoQueSigueProperty(): array
    {
        return \App\Support\LoQueSigue::para($this->patient);
    }

    public function getStatsProperty(): array
    {
        return [
            'total_visits' => MedicalRecord::where('patient_id', $this->patient->id)->count(),
            'total_appointments' => Appointment::where('patient_id', $this->patient->id)->count(),
            // Lo pagado cuenta los abonos, y lo pendiente es lo que falta, no
            // el monto completo: con $1,000 abonados de $2,500 debe $1,500.
            'total_paid' => (float) Payment::where('patient_id', $this->patient->id)
                ->selectRaw('SUM(CASE WHEN status = ? THEN amount ELSE amount_paid END) as pagado', ['paid'])
                ->value('pagado'),
            'pending' => (float) Payment::where('patient_id', $this->patient->id)
                ->withBalance()
                ->yaToca()
                ->selectRaw('SUM(amount - amount_paid) as saldo')
                ->value('saldo'),
            'last_visit' => MedicalRecord::where('patient_id', $this->patient->id)->max('visit_date'),
        ];
    }
}
