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

            return;
        }

        // Bitácora (NOM-024): quién abrió el expediente y cuándo. Solo al
        // entrar, no en cada pestaña.
        activity()
            ->performedOn($this->patient)
            ->causedBy(auth()->user())
            ->event('viewed')
            ->log('Abrió el expediente');
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

    /**
     * "Cobrar $300": todo lo que debe hoy, en un clic. Solo sale si debe, y
     * el monto está en el botón (como la caja de DaleControl), para que nadie
     * tenga que abrir la pestaña Cobros a sumar.
     */
    private function adeudosDeHoy(): \Illuminate\Support\Collection
    {
        return Payment::where('clinic_id', auth()->user()->clinic_id)
            ->where('patient_id', $this->patient?->id)
            ->withBalance()
            ->yaToca()
            ->get();
    }

    protected function getHeaderActions(): array
    {
        $debeHoy = fn () => round($this->adeudosDeHoy()->sum(fn (Payment $c) => $c->remaining), 2);
        // Lo que falta después de su saldo a favor, que se descuenta solo.
        $faltaHoy = fn () => round(max(0, $debeHoy() - (float) $this->patient?->saldoAFavor()), 2);

        return [
            \App\Filament\Doctor\Actions\CobrarAbono::make('cobrarSaldo', fn () => $this->adeudosDeHoy())
                ->label(fn () => $faltaHoy() > 0 ? 'Cobrar $' . number_format($faltaHoy(), 0) : 'Pagar con su saldo a favor')
                ->visible(fn () => $this->patient !== null && $debeHoy() > 0),
            // Cobrar algo nuevo sin salir de la ficha: el servicio, cuánto
            // paga hoy y cómo. Antes había que ir a Cobros → Nuevo.
            \Filament\Actions\Action::make('cobrarHoy')
                ->label(fn () => $debeHoy() > 0 ? 'Nuevo cobro' : 'Cobrar')
                ->icon('heroicon-o-plus-circle')
                ->color(fn () => $debeHoy() > 0 ? 'gray' : 'success')
                ->visible(fn () => $this->patient !== null)
                ->modalHeading(fn () => 'Cobrar a ' . trim($this->patient?->first_name . ' ' . $this->patient?->last_name))
                ->modalDescription(fn () => $debeHoy() > 0
                    ? 'Debe $' . number_format($debeHoy(), 2) . ' de antes; eso se cobra con «Cobrar $' . number_format($faltaHoy(), 0) . '».'
                    : ($this->patient->saldoAFavor() > 0 ? null : 'No debe nada.'))
                ->modalIcon('heroicon-o-banknotes')
                ->modalIconColor('success')
                ->modalWidth('2xl')
                ->modalSubmitActionLabel('Guardar cobro')
                // Ya sabe qué se cobra: lo de su cita de hoy (11-oct-2026).
                ->fillForm(fn () => array_merge(['tipo' => 'cobro', 'como' => 'todo', 'payment_method' => 'cash', 'otra_forma' => 'card',
                    'appointment_id' => null, 'service_id' => null, 'concepto' => null, 'amount' => null],
                    \Illuminate\Support\Arr::only(\App\Filament\Doctor\Actions\Cobro::sugerencia($this->patient) ?? [], ['appointment_id', 'service_id', 'concepto', 'amount'])))
                ->form(fn () => [
                    \Filament\Forms\Components\ToggleButtons::make('tipo')
                        ->hiddenLabel()
                        ->options(['cobro' => 'Cobrar un tratamiento', 'anticipo' => 'Dejar un anticipo'])
                        ->colors(['cobro' => 'success', 'anticipo' => 'info'])
                        ->inline()
                        ->live()
                        ->required(),
                    \Filament\Forms\Components\Hidden::make('appointment_id'),
                    \Filament\Forms\Components\Placeholder::make('de_su_cita')
                        ->hiddenLabel()
                        ->visible(fn (\Filament\Forms\Get $get) => $get('tipo') === 'cobro' && filled($get('appointment_id')))
                        ->content(fn () => new \Illuminate\Support\HtmlString('<div style="padding:10px 12px;border-radius:8px;background:#f0fdfa;border:1px solid #99f6e4;color:#115e59;font-weight:600;">'
                            . e(\App\Filament\Doctor\Actions\Cobro::sugerencia($this->patient)['texto'] ?? '') . ' Cámbielo si se le hizo algo más.</div>')),
                    \Filament\Forms\Components\Placeholder::make('saldo_a_favor')
                        ->label('')
                        ->content(fn () => new \Illuminate\Support\HtmlString('<div style="padding:10px 12px;border-radius:8px;background:#ecfdf5;border:1px solid #a7f3d0;color:#065f46;font-weight:600;">Tiene $' . number_format($this->patient->saldoAFavor(), 2) . ' a favor: se descuentan solos de este cobro.</div>'))
                        ->visible(fn (\Filament\Forms\Get $get) => $get('tipo') === 'cobro' && $this->patient->saldoAFavor() > 0),
                    \Filament\Forms\Components\Placeholder::make('que_es_anticipo')
                        ->label('')
                        ->content(fn () => new \Illuminate\Support\HtmlString('<div style="padding:10px 12px;border-radius:8px;background:#eff6ff;border:1px solid #bfdbfe;color:#1e40af;font-weight:600;">' . e($debeHoy() > 0
                            ? 'Primero se paga lo que debe ($' . number_format($debeHoy(), 2) . '); lo demás queda a su favor.'
                            : 'Entra hoy a la caja y queda a su favor; se descuenta solo en su siguiente cobro.') . '</div>'))
                        ->visible(fn (\Filament\Forms\Get $get) => $get('tipo') === 'anticipo'),
                    \Filament\Forms\Components\Grid::make(2)->schema([
                    \Filament\Forms\Components\Select::make('service_id')
                        ->label('Servicio')
                        ->options(fn () => \App\Models\Service::where('clinic_id', auth()->user()->clinic_id)->where('is_active', true)->orderBy('name')->pluck('name', 'id'))
                        ->searchable()
                        ->live()
                        ->visible(fn (\Filament\Forms\Get $get) => $get('tipo') !== 'anticipo')
                        ->afterStateUpdated(function ($state, \Filament\Forms\Set $set) {
                            $precio = \App\Models\Service::where('clinic_id', auth()->user()->clinic_id)->find($state)?->price;
                            if ($precio !== null) {
                                $set('amount', (float) $precio);
                            }
                        })
                        ->helperText('O escriba el concepto si no está en su lista.'),
                    \Filament\Forms\Components\TextInput::make('concepto')
                        ->label('Concepto')
                        ->placeholder('Ej. Resina en 36')
                        ->maxLength(255)
                        ->visible(fn (\Filament\Forms\Get $get) => $get('tipo') !== 'anticipo')
                        ->required(fn (\Filament\Forms\Get $get) => $get('tipo') !== 'anticipo' && blank($get('service_id'))),
                    ]),
                    \Filament\Forms\Components\TextInput::make('amount')
                        ->label(fn (\Filament\Forms\Get $get) => $get('tipo') === 'anticipo' ? 'Monto del anticipo' : 'Total')
                        ->extraInputAttributes(['style' => 'font-size:1.35rem;font-weight:800;'])
                        ->numeric()
                        ->prefix('$')
                        ->minValue(0.01)
                        ->live(onBlur: true)
                        ->required(),
                    \Filament\Forms\Components\Group::make(\App\Filament\Doctor\Actions\Cobro::camposDePago(aFavor: fn () => $this->patient->saldoAFavor()))
                        ->visible(fn (\Filament\Forms\Get $get) => $get('tipo') !== 'anticipo'),
                    // Se anota aquí, donde el paciente lo pide; sale en la lista
                    // para su contador (11-oct-2026).
                    \Filament\Forms\Components\Toggle::make('factura')
                        ->label('Pide factura')
                        ->helperText(fn () => blank($this->patient->rfc)
                            ? 'Sus datos fiscales se ponen en su ficha: Editar → Datos para factura.'
                            : 'RFC ' . $this->patient->rfc . '. Sale en la lista para su contador.')
                        ->visible(fn (\Filament\Forms\Get $get) => $get('tipo') !== 'anticipo'),
                    \Filament\Forms\Components\Group::make(\App\Filament\Doctor\Actions\Cobro::camposDeForma(fn (\Filament\Forms\Get $get) => round((float) $get('amount'), 2)))
                        ->visible(fn (\Filament\Forms\Get $get) => $get('tipo') === 'anticipo'),
                ])
                ->action(function (array $data) {
                    if (($data['tipo'] ?? 'cobro') === 'anticipo') {
                        $aFavor = \App\Filament\Doctor\Actions\Cobro::anticipo($data, $this->patient->id);
                        \Filament\Notifications\Notification::make()
                            ->title('Anticipo guardado')
                            ->body($aFavor > 0 ? 'Le quedan $' . number_format($this->patient->saldoAFavor(), 2) . ' a favor.' : 'Se usó para pagar lo que debía.')
                            ->success()
                            ->send();

                        return;
                    }
                    $cobro = \App\Filament\Doctor\Actions\Cobro::nuevo($data, $this->patient->id);
                    \Filament\Notifications\Notification::make()
                        ->title('Cobro guardado')
                        ->body($cobro->remaining > 0 ? 'Queda debiendo $' . number_format($cobro->remaining, 2) . '.' : 'Pagado completo.')
                        ->success()
                        ->send();
                }),
            // Ya existían, pero en la prueba del 12-oct nadie los encontró:
            // el plan de pagos vivía dentro de la pestaña Cobros y el
            // consentimiento solo en el menú.
            \Filament\Actions\Action::make('plan_de_pagos')
                ->label('Plan de pagos')
                ->icon('heroicon-o-calendar-days')
                ->color('gray')
                ->visible(fn () => $this->patient !== null)
                ->url(fn () => \App\Filament\Doctor\Resources\PaymentPlanResource::getUrl('create', ['patient' => $this->patient->id], panel: 'doctor')),
            \Filament\Actions\Action::make('consentimiento')
                ->label('Consentimiento')
                ->icon('heroicon-o-pencil-square')
                ->color('gray')
                ->visible(fn () => $this->patient !== null && \App\Filament\Doctor\Resources\ConsentFormResource::canAccess())
                ->url(fn () => \App\Filament\Doctor\Resources\ConsentFormResource::getUrl('create', ['patient' => $this->patient->id], panel: 'doctor')),
            // Los dos PDF juntos, para que los botones quepan en una línea.
            \Filament\Actions\ActionGroup::make([
                \Filament\Actions\Action::make('descargar_expediente')
                    ->label('Expediente (PDF)')
                    ->icon('heroicon-o-document-text')
                    ->visible(fn () => $this->patient !== null)
                    ->action(fn () => $this->descargarExpediente()),
                // Cada cobro con sus abonos y lo que debe (10-oct-2026).
                \Filament\Actions\Action::make('estado_de_cuenta')
                    ->label('Estado de cuenta (PDF)')
                    ->icon('heroicon-o-banknotes')
                    ->visible(fn () => $this->patient !== null)
                    ->url(fn () => route('paciente.estado-de-cuenta', $this->patient->id))
                    ->openUrlInNewTab(),
            ])
                ->label('Descargar')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('gray')
                ->button(),
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
