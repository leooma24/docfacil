<?php

namespace App\Filament\Doctor\Pages;

use App\Models\Appointment;
use App\Models\ConsultationProcedure;
use App\Models\MedicalRecord;
use App\Models\Payment;
use App\Models\Prescription;
use App\Models\PrescriptionItem;
use App\Models\Service;
use App\Models\Supply;
use App\Models\SupplyMovement;
use App\Services\ConsultationAIService;
use App\Services\SpecialtyService;
use App\Support\AnesthesiaDose;
use App\Support\SupplyProposal;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\DB;

class Consultation extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-play-circle';

    protected static ?string $navigationLabel = 'Consulta';

    protected static ?string $title = 'Flujo de Consulta';

    protected static ?string $slug = 'consulta';

    protected static bool $shouldRegisterNavigation = true;

    protected static ?int $navigationSort = 1;

    protected static string $view = 'filament.doctor.pages.consultation';

    public ?Appointment $appointment = null;
    public int $currentStep = 1;
    public bool $completed = false;
    public ?int $savedPrescriptionId = null;
    public bool $isWalkIn = false;
    public bool $showNewPatientForm = false;

    // Walk-in patient selection
    public ?string $walkin_patient_id = null;
    public ?string $walkin_service_id = null;

    // New patient quick form
    public ?string $new_first_name = '';
    public ?string $new_last_name = '';
    public ?string $new_phone = '';
    public ?string $new_email = '';

    // Search
    public string $patientSearch = '';

    // Step 1: Vital signs + somatometry (visibilidad controlada por $enabledFields)
    public ?string $blood_pressure = '';
    public ?string $heart_rate = '';
    public ?string $temperature = '';
    public ?string $respiratory_rate = '';
    public ?string $oxygen_saturation = '';
    public ?string $weight = '';
    public ?string $height = '';
    public ?string $head_circumference = '';

    // Step 2: Diagnosis
    public ?string $chief_complaint = '';
    public ?string $diagnosis = '';
    public ?string $treatment = '';
    public ?string $medical_notes = '';
    public array $cie10_codes = [];

    /**
     * Lista de campos habilitados para ESTE doctor en SU pantalla de consulta.
     * Se resuelve en mount() con cascada doctor → clinic → defaults por especialidad.
     * Se usa en la vista con isFieldEnabled('campo') para condicionar el render.
     */
    public array $enabledFields = [];

    // Step 3: Prescription
    public array $medications = [];
    public ?string $prescription_notes = '';

    // Step 4: Payment
    public ?string $payment_service_id = null;
    public ?string $payment_amount = '';
    public string $payment_method = 'cash';

    /**
     * "¿Ya pagó?": 'todo', 'abono' o 'pendiente'. Si nadie contesta queda por
     * cobrar. Antes, con solo pasar por el paso del cobro se daba por pagado
     * en efectivo, y la caja del día no cuadraba (auditoría del 12-oct-2026).
     */
    public ?string $ya_pago = null;

    /** Lo que dejó, si dejó un abono. */
    public $abono = '';

    /** El tratamiento ya se paga en mensualidades: la consulta no lo cobra aparte. */
    public bool $cubiertoPorPlan = false;

    /**
     * Los procedimientos realizados: servicio, diente y cantidad.
     *
     * Mientras la consulta está en curso viven aquí, dentro del borrador que
     * ya se guarda en `consultation_data`. Se vuelven filas de verdad al
     * cerrar, igual que el resto del borrador se vuelve MedicalRecord.
     */
    public array $procedures = [];

    /**
     * Los insumos que se van a descontar, con lo que el doctor haya ajustado.
     *
     * Se recalcula cuando cambian los procedimientos, pero respeta lo que él
     * tocó: la cuenta propone, el doctor dispone.
     */
    public array $supplies = [];

    // Step 5: Next appointment
    public ?string $next_appointment_date = null;
    public ?string $next_appointment_service_id = null;
    /** Si la siguiente cita es un tratamiento del presupuesto, cuál. */
    public ?int $next_appointment_item_id = null;

    /**
     * A quién le toca después de esta consulta: de las citas de hoy del mismo
     * doctor, primero el que ya está en la sala y luego por hora.
     */
    public function getSiguienteCitaProperty(): ?Appointment
    {
        if (! $this->appointment) {
            return null;
        }

        return Appointment::with('patient')
            ->where('clinic_id', $this->appointment->clinic_id)
            ->where('doctor_id', $this->appointment->doctor_id)
            ->whereKeyNot($this->appointment->id)
            ->whereDate('starts_at', today())
            ->whereIn('status', ['scheduled', 'confirmed'])
            ->orderByRaw('arrived_at is null')
            ->orderBy('starts_at')
            ->first();
    }

    /** La consulta de este paciente: su cita de hoy, o una sin cita. */
    public static function urlParaPaciente(\App\Models\Patient|int $paciente): string
    {
        return route('filament.doctor.pages.consulta', ['patient' => $paciente instanceof \App\Models\Patient ? $paciente->id : $paciente]);
    }

    public function mount(): void
    {
        $appointmentId = request('appointment');
        $clinicId = auth()->user()->clinic_id;
        $doctor = auth()->user()->doctor;
        $doctorId = $doctor?->id;

        // Resolver qué campos mostrar (cascada doctor → clínica → defaults por especialidad)
        $this->enabledFields = SpecialtyService::resolveEnabledFields($doctor);

        if ($appointmentId) {
            $this->appointment = Appointment::with(['patient', 'doctor.user', 'service', 'clinic'])
                ->where('clinic_id', $clinicId)
                ->find($appointmentId);
        }

        // "Iniciar consulta" desde el perfil o desde el aviso de que llegó:
        // su cita de hoy si la tiene; si no, una consulta sin cita con él.
        if (! $this->appointment && request('patient')) {
            $paciente = \App\Models\Patient::where('clinic_id', $clinicId)->find(request('patient'));

            if ($paciente) {
                $this->appointment = Appointment::with(['patient', 'doctor.user', 'service', 'clinic'])
                    ->where('clinic_id', $clinicId)
                    ->where('patient_id', $paciente->id)
                    ->whereDate('starts_at', today())
                    ->whereIn('status', ['scheduled', 'confirmed', 'in_progress'])
                    ->orderBy('starts_at')
                    ->first();

                if (! $this->appointment) {
                    $this->walkin_patient_id = (string) $paciente->id;
                    $this->startWalkIn();

                    return;
                }
            }
        }

        // Auto-resume: if no appointment specified, check for in_progress appointment
        if (!$this->appointment && $doctorId) {
            $this->appointment = Appointment::with(['patient', 'doctor.user', 'service', 'clinic'])
                ->where('clinic_id', $clinicId)
                ->where('doctor_id', $doctorId)
                ->where('status', 'in_progress')
                ->latest('starts_at')
                ->first();
        }

        if (!$this->appointment) {
            $this->isWalkIn = true;
            $this->currentStep = 0;
            return;
        }

        // Una consulta cerrada no se vuelve a abrir: cerrarla otra vez dejaba
        // un segundo expediente y un segundo cobro. La cita terminada o
        // cancelada lleva al perfil, donde está todo lo que pasó.
        if (in_array($this->appointment->status, ['completed', 'cancelled', 'no_show'], true)) {
            \Filament\Notifications\Notification::make()
                ->title($this->appointment->status === 'completed' ? 'Esa consulta ya se cerró' : 'Esa cita no se atendió')
                ->body('Aquí está el perfil del paciente, con su historial y sus cobros.')
                ->info()
                ->send();
            $this->redirect(route('filament.doctor.pages.perfil-paciente', ['patient' => $this->appointment->patient_id]));

            return;
        }

        // "En curso" solo la cita de hoy: abrir la de otro día para revisarla
        // no la saca de los recordatorios ni la anuncia en la sala.
        if (in_array($this->appointment->status, ['scheduled', 'confirmed']) && $this->appointment->starts_at->isToday()) {
            $this->appointment->update(['status' => 'in_progress']);
        }

        // Restore saved consultation state if available
        $saved = $this->appointment->consultation_data;
        if ($saved) {
            $this->currentStep = $saved['currentStep'] ?? 1;
            $this->blood_pressure = $saved['blood_pressure'] ?? '';
            $this->heart_rate = $saved['heart_rate'] ?? '';
            $this->temperature = $saved['temperature'] ?? '';
            $this->respiratory_rate = $saved['respiratory_rate'] ?? '';
            $this->oxygen_saturation = $saved['oxygen_saturation'] ?? '';
            $this->weight = $saved['weight'] ?? '';
            $this->height = $saved['height'] ?? '';
            $this->head_circumference = $saved['head_circumference'] ?? '';
            $this->chief_complaint = $saved['chief_complaint'] ?? '';
            $this->diagnosis = $saved['diagnosis'] ?? '';
            $this->treatment = $saved['treatment'] ?? '';
            $this->medical_notes = $saved['medical_notes'] ?? '';
            $this->cie10_codes = $saved['cie10_codes'] ?? [];
            $this->medications = $saved['medications'] ?? [];
            $this->prescription_notes = $saved['prescription_notes'] ?? '';
            $this->payment_service_id = $saved['payment_service_id'] ?? null;
            $this->payment_amount = $saved['payment_amount'] ?? '';
            $this->payment_method = $saved['payment_method'] ?? 'cash';
            $this->procedures = $saved['procedures'] ?? [];
            $this->supplies = $saved['supplies'] ?? [];
            $this->next_appointment_date = $saved['next_appointment_date'] ?? null;
            $this->next_appointment_service_id = $saved['next_appointment_service_id'] ?? null;
            $this->next_appointment_item_id = $saved['next_appointment_item_id'] ?? null;
            $this->ya_pago = $saved['ya_pago'] ?? null;
            $this->abono = $saved['abono'] ?? '';
            $this->cubiertoPorPlan = (bool) ($saved['cubiertoPorPlan'] ?? false);
        } else {
            // Pre-fill payment amount from service
            if ($this->appointment->service) {
                $this->payment_service_id = (string) $this->appointment->service_id;
                $this->payment_amount = (string) $this->appointment->service->price;

                // Y el motivo, con lo que ya se sabe. Si la cita se agendó
                // para una limpieza, volver a escribir "limpieza" es trabajo
                // de gratis. Queda editable: es un punto de partida, no un
                // dato cerrado, y el doctor casi siempre agrega lo suyo.
                $this->chief_complaint = $this->appointment->service->name;
            }
            $this->proponerProcedimientos();
            $this->noCobrarDobleLaMensualidad();
        }
    }

    /**
     * La cita del paciente de ortodoncia suele tener el servicio de la
     * mensualidad. Si ese servicio es el de su plan, lo que se cobra es la
     * mensualidad del plan, no el precio del servicio otra vez.
     */
    private function noCobrarDobleLaMensualidad(): void
    {
        $servicio = $this->appointment?->service_id;
        // El plan de pagos que se armó desde el presupuesto no lleva servicio:
        // lo liga el presupuesto. Ese tratamiento ya se paga en mensualidades.
        $presupuesto = $this->appointment?->treatmentPlanItem?->treatment_plan_id;

        if (! $servicio && ! $presupuesto) {
            return;
        }

        $this->cubiertoPorPlan = \App\Models\PaymentPlan::where('clinic_id', $this->appointment->clinic_id)
            ->where('patient_id', $this->appointment->patient_id)
            ->where('status', 'active')
            ->where(fn ($q) => $q
                ->when($servicio, fn ($q) => $q->orWhere('service_id', $servicio))
                ->when($presupuesto, fn ($q) => $q->orWhere('treatment_plan_id', $presupuesto)))
            ->exists();

        if ($this->cubiertoPorPlan) {
            $this->payment_amount = '0';
        }
    }

    /**
     * Si la cita es de un tratamiento dental, la consulta ya abre con ese
     * procedimiento, en los dientes que el odontograma tiene por tratar
     * (la cita de extracción de tercer molar trae el 48). Si el odontograma
     * no dice cuál, queda un renglón para que el doctor ponga el diente.
     */
    private function proponerProcedimientos(): void
    {
        $servicio = $this->appointment?->service;

        // La cita viene de un tratamiento del presupuesto: ese diente, ese servicio.
        // Y a su precio: lo que se le prometió al paciente en el presupuesto,
        // con el descuento repartido, no el del catálogo.
        $item = $this->appointment?->treatmentPlanItem;
        if ($item && $item->service_id) {
            $this->procedures = [[
                'service_id' => (string) $item->service_id,
                'tooth_number' => (string) ($item->tooth_number ?? ''),
                'quantity' => max(1, (int) $item->quantity),
                'precio' => self::precioDelPresupuesto($item),
                'precio_de' => (string) $item->service_id,
            ]];
            $this->updatedProcedures();

            return;
        }

        if (! $servicio || ! \App\Support\OdontogramaClinico::condicionDeServicio($servicio->name)) {
            return;
        }

        $odontograma = \App\Support\OdontogramaClinico::ultimo($this->appointment->clinic_id, $this->appointment->patient_id);
        $dientes = \App\Support\OdontogramaClinico::dientesPara($servicio, $odontograma) ?: [''];

        $this->procedures = array_map(fn ($diente) => [
            'service_id' => (string) $servicio->id,
            'tooth_number' => (string) $diente,
            'quantity' => 1,
        ], $dientes);

        $this->updatedProcedures();
    }

    protected function getForms(): array
    {
        return [
            'walkinForm' => $this->makeForm()
                ->schema($this->getWalkinFormSchema())
                ->statePath('data'),
        ];
    }

    protected function getWalkinFormSchema(): array
    {
        $clinicId = auth()->user()->clinic_id;

        return [
            Forms\Components\Select::make('walkin_patient_id')
                ->label('Paciente')
                ->searchable()
                ->preload()
                ->getSearchResultsUsing(function (string $search) use ($clinicId): array {
                    return \App\Models\Patient::where('clinic_id', $clinicId)
                        ->where(fn ($q) => $q
                            ->where('first_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%")
                            ->orWhere('phone', 'like', "%{$search}%")
                        )
                        ->limit(50)
                        ->get()
                        ->mapWithKeys(fn ($p) => [$p->id => "{$p->first_name} {$p->last_name}" . ($p->phone ? " — {$p->phone}" : '')])
                        ->toArray();
                })
                ->getOptionLabelUsing(fn ($value): ?string =>
                    ($p = \App\Models\Patient::find($value)) ? "{$p->first_name} {$p->last_name}" . ($p->phone ? " — {$p->phone}" : '') : null
                )
                ->required()
                ->createOptionForm([
                    Forms\Components\TextInput::make('first_name')
                        ->label('Nombre')
                        ->required(),
                    Forms\Components\TextInput::make('last_name')
                        ->label('Apellidos')
                        ->required(),
                    Forms\Components\TextInput::make('phone')
                        ->label('Teléfono')
                        ->tel(),
                    Forms\Components\TextInput::make('email')
                        ->label('Email')
                        ->email(),
                ])
                ->createOptionUsing(function (array $data) use ($clinicId): int {
                    $data['clinic_id'] = $clinicId;
                    return \App\Models\Patient::create($data)->id;
                }),
            Forms\Components\Select::make('walkin_service_id')
                ->label('Servicio')
                ->options(
                    Service::where('clinic_id', $clinicId)
                        ->where('is_active', true)
                        ->get()
                        ->mapWithKeys(fn ($s) => [$s->id => "{$s->name} — \${$s->price}"])
                )
                ->searchable()
                ->createOptionForm([
                    Forms\Components\TextInput::make('name')
                        ->label('Nombre del servicio')
                        ->required(),
                    Forms\Components\TextInput::make('price')
                        ->label('Precio')
                        ->numeric()
                        ->prefix('$')
                        ->required(),
                    Forms\Components\TextInput::make('duration_minutes')
                        ->label('Duración (minutos)')
                        ->numeric()
                        ->default(30),
                    Forms\Components\TextInput::make('category')
                        ->label('Categoría'),
                ])
                ->createOptionUsing(function (array $data) use ($clinicId): int {
                    $data['clinic_id'] = $clinicId;
                    return Service::create($data)->id;
                }),
        ];
    }

    public ?array $data = [
        'walkin_patient_id' => null,
        'walkin_service_id' => null,
    ];

    public function startWalkIn(): void
    {
        $patientId = $this->data['walkin_patient_id'] ?? $this->walkin_patient_id ?? null;
        $serviceId = $this->data['walkin_service_id'] ?? $this->walkin_service_id ?? null;

        if (empty($patientId)) {
            return;
        }

        $clinicId = auth()->user()->clinic_id;
        $doctor = auth()->user()->doctor;

        // Antes esto era "$doctor?->id ?? 1": si el usuario no tenia ficha de
        // doctor, la consulta se le asignaba al doctor con ID 1, que puede ser
        // de otro consultorio. Mejor no crear nada y decirlo.
        if (! $doctor) {
            Notification::make()
                ->title('Su cuenta no tiene ficha de doctor')
                ->body('Pídale al administrador del consultorio que se la cree para poder atender consultas.')
                ->danger()
                ->send();

            return;
        }

        // Free con sus 10 citas del mes ya usadas: se le dice, sin pantalla de error.
        $clinica = auth()->user()->clinic;
        if ($clinica && ! $clinica->puedeAgendar()) {
            $clinica->avisarTopeDeCitas();

            return;
        }

        $this->appointment = Appointment::create([
            'clinic_id' => $clinicId,
            'doctor_id' => $doctor->id,
            'patient_id' => $patientId,
            'service_id' => $serviceId ?: null,
            'starts_at' => now(),
            'ends_at' => now()->addMinutes(30),
            'status' => 'in_progress',
        ]);

        $this->appointment->load(['patient', 'doctor.user', 'service', 'clinic']);

        if ($this->appointment->service) {
            $this->payment_service_id = (string) $this->appointment->service_id;
            $this->payment_amount = (string) $this->appointment->service->price;
        }
        $this->proponerProcedimientos();
        $this->noCobrarDobleLaMensualidad();

        $this->isWalkIn = false;
        $this->currentStep = 1;
    }

    public function createQuickPatient(): void
    {
        if (empty($this->new_first_name) || empty($this->new_last_name)) {
            return;
        }

        $patient = \App\Models\Patient::create([
            'clinic_id' => auth()->user()->clinic_id,
            'first_name' => $this->new_first_name,
            'last_name' => $this->new_last_name,
            'phone' => $this->new_phone ?: null,
            'email' => $this->new_email ?: null,
        ]);

        $this->walkin_patient_id = (string) $patient->id;
        $this->showNewPatientForm = false;

        // Auto-start the walk-in
        $this->startWalkIn();
    }

    public function getPatientsListProperty(): array
    {
        $query = \App\Models\Patient::where('clinic_id', auth()->user()->clinic_id);

        if (!empty($this->patientSearch)) {
            $search = $this->patientSearch;
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        return $query->orderBy('first_name')
            ->limit(50)
            ->get()
            ->mapWithKeys(fn ($p) => [$p->id => "{$p->first_name} {$p->last_name}" . ($p->phone ? " — {$p->phone}" : '')])
            ->toArray();
    }

    /** Si el doctor movió el cobro, lo vio: cuenta igual que llegar a su pantalla. */
    public function updated(string $propiedad): void
    {
        // Lo que contestó del pago se guarda luego luego: si se recarga la
        // página, no se pierde.
        if (in_array($propiedad, ['ya_pago', 'abono'], true)) {
            $this->saveConsultationState();
        }
    }

    public function nextStep(): void
    {
        $this->currentStep = min($this->currentStep + 1, 5);
        $this->saveConsultationState();
    }

    public function prevStep(): void
    {
        $this->currentStep = max($this->currentStep - 1, 1);
        $this->saveConsultationState();
    }

    public function goToStep(int $step): void
    {
        $this->currentStep = $step;
        $this->saveConsultationState();
    }

    public ?string $fullDictation = '';
    public bool $processingDictation = false;
    public array $dxSuggestions = [];
    public bool $loadingSuggestions = false;

    public function fetchDxSuggestions(): void
    {
        if (strlen(trim($this->chief_complaint)) < 10) {
            $this->dxSuggestions = [];
            return;
        }
        $this->loadingSuggestions = true;
        $suggestions = app(ConsultationAIService::class)->suggestDiagnoses($this->chief_complaint);
        $this->dxSuggestions = $suggestions ?? [];
        $this->loadingSuggestions = false;
    }

    public function applySuggestion(int $index): void
    {
        if (!isset($this->dxSuggestions[$index])) return;

        $s = $this->dxSuggestions[$index];
        $this->diagnosis = $s['diagnosis'] ?? '';
        $this->treatment = $s['treatment'] ?? '';

        if (!empty($s['medication']['medication'])) {
            $this->medications = array_merge($this->medications ?? [], [$s['medication']]);
        }

        $this->dxSuggestions = [];
        $this->saveConsultationState();

        Notification::make()
            ->title('Sugerencia aplicada')
            ->body('Revise y ajuste si es necesario.')
            ->success()
            ->send();
    }

    public function dismissSuggestions(): void
    {
        $this->dxSuggestions = [];
    }

    public function processFullDictation(): void
    {
        if (empty(trim($this->fullDictation))) {
            Notification::make()
                ->title('Primero dicta algo')
                ->danger()
                ->send();
            return;
        }

        $this->processingDictation = true;

        try {
            $parsed = app(ConsultationAIService::class)->structureDictation($this->fullDictation);

            if (!$parsed) {
                Notification::make()
                    ->title('No se pudo procesar el dictado')
                    ->body('Verifica la configuración de IA o intenta de nuevo.')
                    ->danger()
                    ->send();
                return;
            }

            // Only fill fields that came back non-empty, don't overwrite good data with ''
            if ($parsed['chief_complaint']) $this->chief_complaint = $parsed['chief_complaint'];
            if ($parsed['diagnosis']) $this->diagnosis = $parsed['diagnosis'];
            if ($parsed['treatment']) $this->treatment = $parsed['treatment'];
            if ($parsed['medical_notes']) $this->medical_notes = $parsed['medical_notes'];

            if (!empty($parsed['medications'])) {
                // Merge with existing medications (append)
                $this->medications = array_merge($this->medications ?? [], $parsed['medications']);
            }

            $this->fullDictation = '';
            $this->saveConsultationState();

            Notification::make()
                ->title('Dictado procesado')
                ->body('Se llenaron los campos automáticamente. Revise antes de guardar.')
                ->success()
                ->send();
        } finally {
            $this->processingDictation = false;
        }
    }

    /**
     * Helper para Blade: ¿este campo está habilitado para mostrarse?
     * Se llama como $this->isFieldEnabled('temperature') desde la vista.
     */
    public function isFieldEnabled(string $field): bool
    {
        return in_array($field, $this->enabledFields, true);
    }

    protected function saveConsultationState(): void
    {
        if (!$this->appointment || $this->completed) {
            return;
        }

        // Persistimos TODOS los campos en consultation_data (no filtramos por
        // enabledFields) para preservar datos si un campo se desactiva con
        // una consulta en progreso.
        $this->appointment->update([
            'consultation_data' => [
                'currentStep' => $this->currentStep,
                'blood_pressure' => $this->blood_pressure,
                'heart_rate' => $this->heart_rate,
                'temperature' => $this->temperature,
                'respiratory_rate' => $this->respiratory_rate,
                'oxygen_saturation' => $this->oxygen_saturation,
                'weight' => $this->weight,
                'height' => $this->height,
                'head_circumference' => $this->head_circumference,
                'chief_complaint' => $this->chief_complaint,
                'diagnosis' => $this->diagnosis,
                'treatment' => $this->treatment,
                'medical_notes' => $this->medical_notes,
                'cie10_codes' => $this->cie10_codes,
                'medications' => $this->medications,
                'prescription_notes' => $this->prescription_notes,
                'payment_service_id' => $this->payment_service_id,
                'payment_amount' => $this->payment_amount,
                'payment_method' => $this->payment_method,
                'procedures' => array_values(array_filter(
                    $this->procedures,
                    fn ($p) => ! empty($p['service_id']),
                )),
                'supplies' => $this->supplies,
                'next_appointment_date' => $this->next_appointment_date,
                'next_appointment_service_id' => $this->next_appointment_service_id,
                'next_appointment_item_id' => $this->next_appointment_item_id,
                'ya_pago' => $this->ya_pago,
                'abono' => $this->abono,
                'cubiertoPorPlan' => $this->cubiertoPorPlan,
            ],
        ]);
    }

    /**
     * El cierre de la consulta, completo o nada.
     *
     * Sin la transacción, el orden de escritura era una trampa: el expediente
     * y el cobro se crean primero, y los procedimientos y el kardex después.
     * Si algo fallaba en la segunda mitad —un diente capturado como "16, 15,
     * 14" que no cabía en la columna—, el doctor veía el error, corregía y
     * volvía a darle Finalizar: segundo expediente y segundo cobro por la
     * misma consulta, y el corte del mes con el doble.
     */
    public function saveAndComplete(): void
    {
        DB::transaction(fn () => $this->cerrarLaConsulta());
    }

    private function cerrarLaConsulta(): void
    {
        // Ya se cerró (doble clic, otra pestaña): no se vuelve a cerrar.
        if ($this->appointment->fresh()?->status === 'completed') {
            return;
        }

        $clinicId = auth()->user()->clinic_id;

        // Solo guardamos los signos vitales que están habilitados Y tienen valor.
        // Si un campo no está enabled, lo ignoramos al persistir (no contaminamos BD).
        $vitalSigns = [];
        foreach (['blood_pressure', 'heart_rate', 'temperature', 'weight'] as $vital) {
            if ($this->isFieldEnabled($vital) && !empty($this->{$vital})) {
                $vitalSigns[$vital] = $this->{$vital};
            }
        }

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
            // Campos extendidos: solo persistimos los habilitados con valor
            'respiratory_rate' => $this->isFieldEnabled('respiratory_rate') && $this->respiratory_rate !== '' ? (int) $this->respiratory_rate : null,
            'oxygen_saturation' => $this->isFieldEnabled('oxygen_saturation') && $this->oxygen_saturation !== '' ? (int) $this->oxygen_saturation : null,
            'height' => $this->isFieldEnabled('height') && $this->height !== '' ? (float) $this->height : null,
            'head_circumference' => $this->isFieldEnabled('head_circumference') && $this->head_circumference !== '' ? (float) $this->head_circumference : null,
            'cie10_codes' => $this->isFieldEnabled('cie10_codes') && !empty($this->cie10_codes) ? $this->cie10_codes : null,
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
                        'presentacion' => $med['presentacion'] ?? null,
                        'dosage' => $med['dosage'] ?? null,
                        'via_administracion' => $med['via_administracion'] ?? null,
                        'frequency' => $med['frequency'] ?? null,
                        'duration' => $med['duration'] ?? null,
                        'instructions' => $med['instructions'] ?? null,
                    ]);
                }
            }
        }

        // Save payment if amount > 0
        if (!empty($this->payment_amount) && $this->payment_amount > 0) {
            [$estado, $pagado] = $this->loQuePago((float) $this->payment_amount);
            Payment::create([
                'clinic_id' => $clinicId,
                'patient_id' => $this->appointment->patient_id,
                'appointment_id' => $this->appointment->id,
                'service_id' => $this->payment_service_id ?: null,
                'amount' => $this->payment_amount,
                'payment_method' => $this->payment_method,
                'status' => $estado,
                'amount_paid' => $pagado,
                'payment_date' => now()->toDateString(),
                'due_date' => now()->toDateString(),
            ]);
        }

        // Los procedimientos capturados se vuelven filas al cerrar. Se borran
        // primero y se reescriben: si la consulta se completa dos veces, queda
        // una sola copia en vez de duplicarse.
        $this->appointment->procedures()->delete();

        foreach ($this->procedures as $capturado) {
            $servicio = $this->serviceById($capturado['service_id'] ?? null);

            if (! $servicio) {
                continue;
            }

            $this->appointment->procedures()->create([
                'clinic_id' => $clinicId,
                'service_id' => $servicio->id,
                'tooth_number' => ($capturado['tooth_number'] ?? '') !== '' ? $capturado['tooth_number'] : null,
                'quantity' => max(1, (int) ($capturado['quantity'] ?? 1)),
                'unit' => $servicio->unit,
                'unit_price' => $this->precioDeLinea($capturado),
            ]);
        }

        // Si la cita era un tratamiento del presupuesto, queda hecho.
        $this->appointment->treatmentPlanItem?->update(['completed_at' => now()]);

        // Lo hecho pasa al odontograma: la resina en el 36 le quita la caries.
        \App\Support\OdontogramaClinico::registrarConsulta($this->appointment);

        // Y los insumos que el doctor confirmó. Se borran los movimientos de
        // esta consulta y se reescriben: si algo reintenta el cierre, queda una
        // sola copia en vez de descontar dos veces del inventario.
        SupplyMovement::where('clinic_id', $clinicId)
            ->where('reference_type', Appointment::class)
            ->where('reference_id', $this->appointment->id)
            ->delete();

        foreach ($this->supplies as $linea) {
            $cantidad = (float) ($linea['quantity'] ?? 0);

            if (empty($linea['include']) || $cantidad <= 0) {
                continue;
            }

            $insumo = Supply::where('clinic_id', $clinicId)->find($linea['supply_id'] ?? null);

            if (! $insumo) {
                continue;
            }

            $insumo->register('out', $cantidad, [
                'reason' => 'Consulta del ' . now()->format('d/m/Y'),
                'reference_type' => Appointment::class,
                'reference_id' => $this->appointment->id,
            ]);
        }

        // Create next appointment if date set
        if (!empty($this->next_appointment_date)) {
            $inicio = \Carbon\Carbon::parse($this->next_appointment_date);

            // La duracion sale del servicio, no 30 minutos fijos: agendar 30
            // para una endodoncia de 90 encimaba las dos citas siguientes.
            $minutos = Service::find($this->next_appointment_service_id)?->duration_minutes ?? 30;
            $fin = $inicio->copy()->addMinutes((int) $minutos ?: 30);

            // Y por aqui tampoco pasaba el formulario, asi que el traslape se
            // guardaba sin decir nada.
            $choque = Appointment::mensajeDeTraslape(
                $clinicId,
                $this->appointment->doctor_id,
                $inicio,
                $fin,
            );

            if ($choque) {
                Notification::make()
                    ->title('No se agendó la siguiente cita')
                    ->body($choque . ' Todo lo demás sí se guardó.')
                    ->warning()
                    ->persistent()
                    ->send();
            } else {
                Appointment::create([
                    'clinic_id' => $clinicId,
                    'doctor_id' => $this->appointment->doctor_id,
                    'patient_id' => $this->appointment->patient_id,
                    'service_id' => $this->next_appointment_service_id ?: null,
                    'treatment_plan_item_id' => $this->next_appointment_item_id,
                    'starts_at' => $inicio,
                    'ends_at' => $fin,
                    'status' => 'scheduled',
                ]);
            }
        }

        // Mark appointment as completed and clear saved state
        $this->appointment->update(['status' => 'completed', 'consultation_data' => null]);

        // Store prescription ID for PDF download
        if (isset($prescription)) {
            $this->savedPrescriptionId = $prescription->id;
        }

        $this->completed = true;
        $this->currentStep = 6;
    }

    /** Un botón de la nota rápida: agrega la frase a "Tratamiento realizado". */
    public function agregarFrase(string $frase): void
    {
        if (in_array($frase, \App\Support\NotaRapida::FRASES, true)) {
            $this->treatment = \App\Support\NotaRapida::agregar($this->treatment, $frase);
        }
    }

    /** Copia lo que se le hizo la vez pasada (ortodoncia: el mismo ajuste cada mes). */
    public function igualQueLaVezPasada(): void
    {
        $previa = $this->appointment ? \App\Support\NotaRapida::laVezPasada($this->appointment) : null;

        if ($previa) {
            $this->treatment = trim((string) $this->treatment) === '' ? $previa : rtrim($this->treatment) . "\n" . $previa;
        }
    }

    /**
     * Lo que contestó en "¿Ya pagó?", en estado y monto pagado. Un abono por
     * todo es pagado; un abono vacío es pendiente.
     *
     * @return array{0: string, 1: float}
     */
    private function loQuePago(float $total): array
    {
        $abono = round((float) $this->abono, 2);

        return match (true) {
            $this->ya_pago === 'todo', $this->ya_pago === 'abono' && $abono >= $total => ['paid', $total],
            $this->ya_pago === 'abono' && $abono > 0 => ['partial', $abono],
            default => ['pending', 0.0],
        };
    }

    /**
     * Se abrió la consulta por error o el paciente se fue: la cita regresa
     * como estaba (confirmada si había confirmado) y lo capturado se queda
     * guardado para cuando sí se atienda.
     */
    public function salirSinAtender(): void
    {
        if ($this->appointment?->status === 'in_progress') {
            $this->saveConsultationState();
            $this->appointment->update(['status' => $this->appointment->confirmed_at ? 'confirmed' : 'scheduled']);
        }

        $this->redirect(\App\Filament\Doctor\Pages\CalendarPage::getUrl());
    }

    public bool $showHistory = false;

    public function toggleHistory(): void
    {
        $this->showHistory = !$this->showHistory;
    }

    public function getPatientHistoryProperty(): array
    {
        if (!$this->appointment) return [];

        return MedicalRecord::where('patient_id', $this->appointment->patient_id)
            ->where('clinic_id', auth()->user()->clinic_id)
            ->with('doctor.user')
            ->orderBy('visit_date', 'desc')
            ->limit(10)
            ->get()
            ->map(fn ($r) => [
                'date' => $r->visit_date->format('d/m/Y'),
                'doctor' => $r->doctor?->user?->name ?? '',
                'complaint' => $r->chief_complaint ?? '',
                'diagnosis' => $r->diagnosis ?? '',
                'treatment' => $r->treatment ?? '',
            ])
            ->toArray();
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
            $service = Service::where('clinic_id', auth()->user()->clinic_id)->find($value);
            if ($service) {
                $this->payment_amount = (string) $service->price;
            }
        }
    }

    // ── Procedimientos realizados ────────────────────────────────

    /**
     * Lo que DocFácil sabe que sigue para este paciente: lo que falta del
     * presupuesto aceptado, el regreso de los servicios que se repiten
     * (limpieza en 6 meses) y el control de su plan de pagos. Cada uno con
     * su fecha, para agendarlo con un clic antes de que se vaya.
     */
    public function sugerenciasSiguienteCita(): array
    {
        $cita = $this->appointment;
        if (! $cita) {
            return [];
        }

        $hora = now()->startOfHour();
        $sugerencias = [];

        $pendientes = \App\Models\TreatmentPlanItem::whereNull('completed_at')
            ->whereHas('treatmentPlan', fn ($q) => $q->where('clinic_id', $cita->clinic_id)->where('patient_id', $cita->patient_id)->where('status', 'accepted'))
            ->with('service')->orderBy('sort_order')->get()
            ->reject(fn ($item) => $item->citaPendiente() || $item->id === $cita->treatment_plan_item_id);
        foreach ($pendientes as $item) {
            $sugerencias['item-' . $item->id] = [
                'titulo' => $item->description,
                'detalle' => 'Del presupuesto',
                'service_id' => $item->service_id,
                'item_id' => $item->id,
                'fecha' => $hora->copy()->addWeek(),
            ];
        }

        $hechos = collect($this->procedures)->pluck('service_id')->push($cita->service_id)->filter()->unique();
        foreach (Service::whereIn('id', $hechos)->where('recall_months', '>', 0)->get() as $servicio) {
            $sugerencias['regreso-' . $servicio->id] = [
                'titulo' => "{$servicio->name} en {$servicio->recall_months} " . ($servicio->recall_months === 1 ? 'mes' : 'meses'),
                'detalle' => 'Regreso',
                'service_id' => $servicio->id,
                'item_id' => null,
                'fecha' => $hora->copy()->addMonthsNoOverflow($servicio->recall_months),
            ];
        }

        $plan = \App\Models\PaymentPlan::where('clinic_id', $cita->clinic_id)->where('patient_id', $cita->patient_id)
            ->where('status', 'active')->whereNotNull('service_id')->latest()->first();
        $proxima = $plan?->payments()->where('installment_number', '>', 0)->whereDate('due_date', '>', today())->orderBy('due_date')->first();
        if ($plan && $proxima) {
            $sugerencias['control-orto'] = [
                'titulo' => 'Control de ' . $plan->description,
                'detalle' => 'Con su siguiente mensualidad',
                'service_id' => $plan->service_id,
                'item_id' => null,
                'fecha' => $proxima->due_date->copy()->setTimeFrom($hora),
            ];
        }

        return $sugerencias;
    }

    public function usarSugerencia(string $clave): void
    {
        $s = $this->sugerenciasSiguienteCita()[$clave] ?? null;
        if (! $s) {
            return;
        }

        $this->next_appointment_date = $s['fecha']->format('Y-m-d\TH:i');
        $this->next_appointment_service_id = (string) $s['service_id'];
        $this->next_appointment_item_id = $s['item_id'];
    }

    /** Alergias que el doctor anota en la consulta cuando no estaban registradas. */
    public string $alergiasNuevas = '';

    public function guardarAlergias(): void
    {
        $alergias = trim($this->alergiasNuevas);
        if ($alergias === '' || ! $this->appointment?->patient) {
            return;
        }

        $this->appointment->patient->update(['allergies' => $alergias]);
        $this->appointment->load('patient');
        $this->alergiasNuevas = '';
        Notification::make()->title('Alergias guardadas en su expediente')->success()->send();
    }

    /**
     * Marca o quita una casilla de lo importante (diabetes, anticoagulantes...)
     * con un toque, sin salir de la consulta. Solo claves conocidas: lo demás
     * se ignora, porque llega del navegador.
     */
    public function alternarRiesgo(string $clave): void
    {
        $paciente = $this->appointment?->patient;
        if (! $paciente || ! array_key_exists($clave, \App\Support\AlertasClinicas::OPCIONES)) {
            return;
        }

        $marcadas = collect($paciente->riesgos ?? []);
        $paciente->update(['riesgos' => $marcadas->contains($clave)
            ? $marcadas->reject(fn ($c) => $c === $clave)->values()->all()
            : $marcadas->push($clave)->unique()->values()->all()]);
        $this->appointment->load('patient');
    }

    /** "¿Sigue igual?" → sí: queda anotado que se revisó hoy. */
    public function sigueIgual(): void
    {
        $paciente = $this->appointment?->patient;
        if (! $paciente) {
            return;
        }

        $paciente->forceFill(['riesgos_revisados_at' => now()])->save();
        $this->appointment->load('patient');
        Notification::make()->title('Anotado: sigue igual')->success()->send();
    }

    /** Se le preguntó y no tiene: queda anotado para no volver a preguntar a ciegas. */
    public function sinAlergias(): void
    {
        $this->alergiasNuevas = \App\Models\Patient::SIN_ALERGIAS;
        $this->guardarAlergias();
    }

    /**
     * Agregar y quitar medicamentos en el servidor. Los botones mandaban la
     * lista tal como estaba al dibujarse la pantalla, sin lo que el doctor
     * acababa de escribir: la amoxicilina se quedaba sin frecuencia ni días
     * y la receta salía incompleta. Livewire manda lo escrito antes de
     * correr la acción, así que aquí la lista ya viene completa.
     */
    public function agregarMedicamento(): void
    {
        $this->medications[] = [
            'medication' => '', 'presentacion' => '', 'dosage' => '', 'via_administracion' => '',
            'frequency' => '', 'duration' => '', 'instructions' => '',
        ];
    }

    public function quitarMedicamento(int $indice): void
    {
        unset($this->medications[$indice]);
        $this->medications = array_values($this->medications);
    }

    /**
     * Mensualidades de sus planes de pago que ya vencieron o vencen hoy: el
     * paciente de ortodoncia viene a su ajuste y ahí mismo se le cobra.
     */
    public function mensualidadesPorCobrar(): \Illuminate\Support\Collection
    {
        if (! $this->appointment) {
            return collect();
        }

        return \App\Models\Payment::where('clinic_id', $this->appointment->clinic_id)
            ->where('patient_id', $this->appointment->patient_id)
            ->whereNotNull('payment_plan_id')
            ->withBalance()
            ->whereDate('due_date', '<=', today())
            ->orderBy('due_date')
            ->get();
    }

    public function cobrarMensualidad(int $paymentId, string $formaDePago = 'cash'): void
    {
        // Método y no propiedad calculada: la propiedad se queda en memoria
        // durante la petición y la mensualidad cobrada seguía en la lista.
        $mensualidad = $this->mensualidadesPorCobrar()->firstWhere('id', $paymentId);

        if (! $mensualidad) {
            return;
        }

        $mensualidad->registrarAbono((float) $mensualidad->remaining, $formaDePago);

        Notification::make()
            ->title('Mensualidad cobrada')
            ->body($mensualidad->notes . ' · $' . number_format((float) $mensualidad->amount, 2))
            ->success()
            ->send();
    }

    /** Lo que el odontograma del paciente tiene por tratar, para agregarlo con un clic. */
    public function getOdontogramaPorTratarProperty(): array
    {
        if (! $this->appointment) {
            return [];
        }

        return \App\Support\OdontogramaClinico::porTratar(
            \App\Support\OdontogramaClinico::ultimo($this->appointment->clinic_id, $this->appointment->patient_id)
        );
    }

    public function agregarDesdeOdontograma(int $numero, string $condicion): void
    {
        $servicios = Service::where('clinic_id', auth()->user()->clinic_id)->where('is_active', true)->get();
        $servicio = \App\Support\OdontogramaClinico::servicioPara($condicion, $numero, $servicios);

        // Un renglón vacío que quedó de "agregar procedimiento" se reutiliza.
        $this->procedures = array_values(array_filter(
            $this->procedures,
            fn ($p) => ($p['service_id'] ?? '') !== '' || ($p['tooth_number'] ?? '') !== ''
        ));
        $this->procedures[] = [
            'service_id' => $servicio ? (string) $servicio->id : '',
            'tooth_number' => (string) $numero,
            'quantity' => 1,
        ];

        $this->updatedProcedures();
    }

    public function addProcedure(): void
    {
        $this->procedures[] = [
            'service_id' => '',
            'tooth_number' => '',
            'quantity' => 1,
        ];
    }

    public function removeProcedure(int $indice): void
    {
        unset($this->procedures[$indice]);
        $this->procedures = array_values($this->procedures);

        $this->recalculateCharge();
    }

    public function updatedProcedures(): void
    {
        $this->recalculateCharge();
        $this->refreshProposal();
    }

    // ── Los insumos que se van a descontar ───────────────────────

    /**
     * Rehace la propuesta a partir de los procedimientos capturados.
     *
     * Respeta lo que el doctor ya ajustó a mano, mientras la cuenta no haya
     * cambiado: si capturó un diente más, la cantidad sugerida es nueva y
     * manda ella; si no, se queda con lo que él puso.
     */
    /** El inventario va en Pro: sin el plan no se propone ni se descuenta nada. */
    public function llevaInventario(): bool
    {
        return (bool) auth()->user()?->clinic?->hasFeature('inventory');
    }

    public function refreshProposal(): void
    {
        if (! $this->llevaInventario()) {
            $this->supplies = [];

            return;
        }

        $propuesta = SupplyProposal::for($this->draftProcedures());
        $previos = collect($this->supplies)->keyBy('supply_id');

        $this->supplies = collect($propuesta)->map(function (array $linea) use ($previos) {
            $previo = $previos->get($linea['supply']->id);
            $suggested = $linea['quantity'];
            $conservar = $previo && (float) ($previo['suggested'] ?? -1) === (float) $suggested;

            return [
                'supply_id' => $linea['supply']->id,
                'name' => $linea['supply']->name,
                'unit' => $linea['supply']->unit,
                'quantity' => $conservar ? $previo['quantity'] : $suggested,
                'suggested' => $suggested,
                'include' => $previo['include'] ?? ! $linea['optional'],
                'detail' => $linea['detail'],
            ];
        })->values()->all();
    }

    // ── Seguridad del paciente ───────────────────────────────────

    /** Cuántos cartuchos de anestésico lleva la propuesta confirmada. */
    public function getProposedAnesthesiaProperty(): float
    {
        $total = 0.0;

        foreach ($this->supplies as $linea) {
            if (empty($linea['include'])) {
                continue;
            }

            $insumo = Supply::where('clinic_id', auth()->user()->clinic_id)->find($linea['supply_id'] ?? null);

            if ($insumo && $insumo->category === 'Anestesia') {
                $total += (float) ($linea['quantity'] ?? 0);
            }
        }

        return round($total, 3);
    }

    public function getPatientAllergiesProperty(): ?string
    {
        return $this->appointment?->patient?->allergies ?: null;
    }

    /**
     * El aviso que importa: el paciente tiene alergias Y se va a usar
     * anestésico.
     *
     * No afirma nada clínico —no sabe si la alergia es al anestésico—, solo
     * junta los dos datos en el momento en que se pueden juntar. La alergia ya
     * se ve en la cabecera, pero ahí es un chip de 40 caracteres que se pierde
     * entre los demás.
     */
    public function getAllergyAlertProperty(): ?string
    {
        if (! $this->patientAllergies) {
            return null;
        }

        return $this->proposedAnesthesia > 0
            ? 'La propuesta incluye anestésico y este paciente tiene alergias registradas. Revísalas antes de aplicar.'
            : null;
    }

    /** El estado de la dosis contra el peso, o null si no hay anestésico. */
    public function getDoseStatusProperty(): ?array
    {
        $cartuchos = $this->proposedAnesthesia;

        if ($cartuchos <= 0) {
            return null;
        }

        return AnesthesiaDose::status(
            $this->appointment->clinic,
            $this->weight !== '' && $this->weight !== null ? (float) $this->weight : null,
            $cartuchos,
        );
    }

    /**
     * Los procedimientos del borrador, como objetos.
     *
     * Todavía no son filas: se materializan al cerrar. Pero la propuesta tiene
     * que calcularse mientras el doctor captura, así que se arman aquí.
     */
    private function draftProcedures(): \Illuminate\Support\Collection
    {
        return collect($this->procedures)
            ->filter(fn ($p) => ! empty($p['service_id']))
            ->map(function (array $p) {
                $procedimiento = new ConsultationProcedure([
                    'service_id' => $p['service_id'],
                    'tooth_number' => $p['tooth_number'] ?? null,
                    'quantity' => $p['quantity'] ?? 1,
                ]);

                $procedimiento->setRelation('service', $this->serviceById($p['service_id']));

                return $procedimiento;
            });
    }

    /**
     * El total de los procedimientos capturados.
     *
     * Es lo que hace que un curetaje de dos cuadrantes se cobre dos veces: el
     * precio del servicio es POR cuadrante, y la cantidad es cuántos se
     * hicieron.
     */
    public function getProceduresTotalProperty(): float
    {
        $total = 0.0;

        foreach ($this->procedures as $capturado) {
            if ($this->serviceById($capturado['service_id'] ?? null)) {
                $total += $this->precioDeLinea($capturado) * max(1, (int) ($capturado['quantity'] ?? 1));
            }
        }

        return round($total, 2);
    }

    /** La unidad de cobro del servicio de una línea, para etiquetar la cantidad. */
    public function unitOf(?string $serviceId): string
    {
        return $this->serviceById($serviceId)?->unit ?? \App\Support\WorkUnit::VISIT;
    }

    public function questionFor(?string $serviceId): string
    {
        return \App\Support\WorkUnit::question($this->unitOf($serviceId));
    }

    /** Precio unitario del servicio de una línea, para mostrarlo en el renglón. */
    public function priceOf(?string $serviceId): float
    {
        return (float) ($this->serviceById($serviceId)?->price ?? 0);
    }

    /**
     * El precio de un renglón: el del presupuesto si de ahí viene y el doctor
     * no le cambió el servicio; si no, el del catálogo.
     */
    public function precioDeLinea(array $linea): float
    {
        $servicio = (string) ($linea['service_id'] ?? '');

        if (isset($linea['precio']) && $servicio !== '' && ($linea['precio_de'] ?? null) === $servicio) {
            return (float) $linea['precio'];
        }

        return $this->priceOf($servicio ?: null);
    }

    /** Lo que vale el renglón del presupuesto, con el descuento del total repartido. */
    private static function precioDelPresupuesto(\App\Models\TreatmentPlanItem $item): float
    {
        $presupuesto = $item->treatmentPlan;
        $precio = (float) $item->unit_price;
        $subtotal = (float) ($presupuesto?->subtotal ?? 0);

        if ($subtotal > 0 && (float) $presupuesto->total < $subtotal) {
            $precio *= (float) $presupuesto->total / $subtotal;
        }

        return round($precio, 2);
    }

    /** @var array<string, Service|null> */
    private array $servicesMemo = [];

    private function serviceById(?string $serviceId): ?Service
    {
        if (! $serviceId) {
            return null;
        }

        // La propuesta y el cobro lo piden varias veces por render.
        if (! array_key_exists($serviceId, $this->servicesMemo)) {
            $this->servicesMemo[$serviceId] = Service::where('clinic_id', auth()->user()->clinic_id)->find($serviceId);
        }

        return $this->servicesMemo[$serviceId];
    }

    /** El cobro sigue al total de los procedimientos, pero se puede ajustar. */
    private function recalculateCharge(): void
    {
        if ($this->cubiertoPorPlan) {
            return;
        }

        $total = $this->proceduresTotal;

        if ($total > 0) {
            $this->payment_amount = (string) $total;
        }
    }
}
