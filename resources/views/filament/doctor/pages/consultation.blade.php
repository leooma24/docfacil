<x-filament-panels::page>
    <style>
        /* Consultation responsive overrides */
        .vitals-grid { display: grid; grid-template-columns: 1fr; gap: 0.75rem; }
        .meds-grid { display: grid; grid-template-columns: 1fr; gap: 0.5rem; }
        .meds-grid .med-wide { grid-column: span 1; }
        .pay-grid { display: grid; grid-template-columns: 1fr; gap: 0.75rem; }
        .next-grid { display: grid; grid-template-columns: 1fr; gap: 0.75rem; }
        .summary-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 0.5rem; }
        .steps-bar { display: flex; align-items: center; justify-content: flex-start; gap: 0.5rem; overflow-x: auto; -webkit-overflow-scrolling: touch; scrollbar-width: none; margin-bottom: 1rem; padding-bottom: 0.25rem; }
        .steps-bar::-webkit-scrollbar { display: none; }
        .step-btn { display: flex; align-items: center; gap: 0.375rem; padding: 0.5rem 0.75rem; border-radius: 0.5rem; font-size: 0.75rem; font-weight: 600; border: none; cursor: pointer; white-space: nowrap; flex-shrink: 0; transition: all 0.2s; }
        .step-circle { width: 1.25rem; height: 1.25rem; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 0.625rem; font-weight: 700; flex-shrink: 0; }
        .step-divider { width: 1rem; height: 2px; flex-shrink: 0; }
        .nav-buttons { display: flex; flex-direction: column; gap: 0.5rem; margin-top: 1rem; }
        .nav-buttons > div { display: flex; flex-direction: column; gap: 0.5rem; }
        .nav-btn { width: 100%; padding: 0.75rem 1.25rem; border-radius: 0.75rem; font-weight: 600; font-size: 0.875rem; border: none; cursor: pointer; text-align: center; }
        .header-layout { display: flex; flex-direction: column; gap: 0.75rem; }
        .header-info { display: flex; align-items: center; gap: 0.75rem; }
        .header-meta { text-align: left; font-size: 0.75rem; }
        .action-btns { display: flex; flex-direction: column; gap: 0.5rem; }
        .action-btns a { display: flex; align-items: center; justify-content: center; gap: 0.5rem; padding: 0.75rem 1rem; border-radius: 0.75rem; font-weight: 600; font-size: 0.875rem; text-decoration: none; text-align: center; }
        @media (min-width: 640px) {
            .vitals-grid { grid-template-columns: repeat(2, 1fr); }
            .meds-grid { grid-template-columns: repeat(2, 1fr); }
            .meds-grid .med-wide { grid-column: span 2; }
            .pay-grid { grid-template-columns: repeat(2, 1fr); }
            .next-grid { grid-template-columns: repeat(2, 1fr); }
            .steps-bar { justify-content: center; }
            .step-btn { padding: 0.5rem 0.875rem; font-size: 0.8125rem; }
            .step-divider { width: 2rem; }
            .nav-buttons { flex-direction: row; justify-content: space-between; align-items: center; }
            .nav-buttons > div { flex-direction: row; }
            .nav-btn { width: auto; }
            .header-layout { flex-direction: row; align-items: center; justify-content: space-between; }
            .header-meta { text-align: right; }
            .action-btns { flex-direction: row; }
            .action-btns a { width: auto; }
        }
        @media (min-width: 1024px) {
            .vitals-grid { grid-template-columns: repeat(4, 1fr); gap: 1rem; }
            .meds-grid { grid-template-columns: repeat(3, 1fr); gap: 0.75rem; }
            .meds-grid .med-wide { grid-column: span 1; }
            .pay-grid { grid-template-columns: repeat(3, 1fr); gap: 1rem; }
            .summary-grid { grid-template-columns: repeat(4, 1fr); }
            .steps-bar { margin-bottom: 2rem; gap: 0.5rem; }
            .step-btn { padding: 0.5rem 0.75rem; font-size: 0.875rem; gap: 0.5rem; }
            .step-circle { width: 1.5rem; height: 1.5rem; font-size: 0.75rem; }
        }
    </style>
    @if($appointment)
    {{-- Patient Header --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border mb-4" style="padding:1rem;">
        <div class="header-layout">
            <div class="flex items-center gap-3">
                <div class="w-11 h-11 md:w-14 md:h-14 bg-teal-100 dark:bg-teal-900 rounded-full flex items-center justify-center shrink-0">
                    <span class="text-base md:text-xl font-bold text-teal-700 dark:text-teal-300">
                        {{ substr($appointment->patient->first_name, 0, 1) }}{{ substr($appointment->patient->last_name, 0, 1) }}
                    </span>
                </div>
                <div class="min-w-0">
                    <h2 class="text-base md:text-xl font-bold text-gray-900 dark:text-white truncate">
                        {{ $appointment->patient->first_name }} {{ $appointment->patient->last_name }}
                    </h2>
                    <div class="flex flex-wrap items-center gap-x-3 gap-y-0.5 text-xs md:text-sm text-gray-500">
                        @if($appointment->patient->birth_date)
                        <span>{{ $appointment->patient->birth_date->age }} años</span>
                        @endif
                        @if($appointment->patient->phone)
                        <span>{{ $appointment->patient->phone }}</span>
                        @endif
                        @if($appointment->patient->blood_type)
                        <span class="font-medium text-red-600">{{ $appointment->patient->blood_type }}</span>
                        @endif
                    </div>
                    @if($appointment->patient->allergies)
                    <div class="text-xs text-red-500 font-medium mt-0.5 truncate">Alergias: {{ $appointment->patient->allergies }}</div>
                    @endif
                </div>
            </div>
            <div class="text-left sm:text-right text-xs md:text-sm pl-14 sm:pl-0">
                <div class="text-gray-500">{{ $appointment->starts_at->translatedFormat('l d M, H:i') }}</div>
                @if($appointment->service)
                <div class="font-medium text-teal-600">{{ $appointment->service->name }}</div>
                @endif
            </div>
        </div>
    </div>

    {{-- History drawer button --}}
    <div class="mb-3 md:mb-4">
        <button wire:click="toggleHistory" class="inline-flex items-center gap-2 px-3 py-2 bg-gray-100 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg text-xs md:text-sm font-semibold text-gray-500 dark:text-gray-400 hover:bg-gray-200 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            Historial
            @if(count($this->patientHistory) > 0)
            <span class="bg-teal-600 text-white rounded-full px-2 text-xs">{{ count($this->patientHistory) }}</span>
            @endif
        </button>
    </div>

    {{-- History drawer --}}
    @if($showHistory)
    <div class="fixed inset-0 z-50" wire:click.self="toggleHistory">
        <div class="absolute inset-0 bg-black/30"></div>
        <div class="absolute top-0 right-0 bottom-0 w-full max-w-sm bg-white dark:bg-gray-800 shadow-xl overflow-y-auto">
            <div class="sticky top-0 z-10 bg-white dark:bg-gray-800 border-b dark:border-gray-700 p-4 flex items-center justify-between">
                <div>
                    <div class="font-extrabold text-sm md:text-base">Historial clínico</div>
                    <div class="text-xs text-gray-500">{{ $appointment->patient->first_name }} {{ $appointment->patient->last_name }} — {{ count($this->patientHistory) }} consultas</div>
                </div>
                <button wire:click="toggleHistory" class="w-8 h-8 bg-gray-100 dark:bg-gray-700 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            @forelse($this->patientHistory as $record)
            <div class="p-4 border-b border-gray-100 dark:border-gray-700">
                <div class="flex justify-between mb-1">
                    <span class="font-bold text-sm text-teal-600">{{ $record['date'] }}</span>
                    <span class="text-gray-400 text-xs">{{ $record['doctor'] }}</span>
                </div>
                @if($record['complaint'])
                <div class="text-xs md:text-sm text-gray-600 dark:text-gray-400 mb-0.5"><strong class="text-gray-800 dark:text-gray-200">Motivo:</strong> {{ $record['complaint'] }}</div>
                @endif
                @if($record['diagnosis'])
                <div class="text-xs md:text-sm mb-0.5"><strong>Dx:</strong> {{ $record['diagnosis'] }}</div>
                @endif
                @if($record['treatment'])
                <div class="text-xs md:text-sm text-gray-600 dark:text-gray-400"><strong class="text-gray-800 dark:text-gray-200">Tx:</strong> {{ $record['treatment'] }}</div>
                @endif
            </div>
            @empty
            <div class="p-8 text-center">
                <svg class="w-12 h-12 text-gray-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                <div class="font-semibold text-gray-500">Primera consulta</div>
                <div class="text-xs text-gray-400">No hay historial previo</div>
            </div>
            @endforelse
        </div>
    </div>
    @endif

    {{-- Steps indicator --}}
    @php
    $stepLabels = [1 => 'Signos vitales', 2 => 'Diagnóstico', 3 => 'Receta', 4 => 'Cobro', 5 => 'Sig. cita'];
    $stepShort  = [1 => 'Vitales', 2 => 'Dx', 3 => 'Rx', 4 => 'Cobro', 5 => 'Cita'];
    @endphp
    <div>
        <div class="steps-bar">
            @foreach($stepLabels as $num => $label)
            @php
                $isActive = $currentStep === $num;
                $isDone = $currentStep > $num;
            @endphp
            <button wire:click="goToStep({{ $num }})"
                class="step-btn"
                style="background:{{ $isActive ? '#0d9488' : ($isDone ? '#ccfbf1' : '#f3f4f6') }};color:{{ $isActive ? 'white' : ($isDone ? '#0f766e' : '#9ca3af') }};{{ $isActive ? 'box-shadow:0 4px 12px rgba(13,148,136,0.3);' : '' }}">
                <span class="step-circle" style="background:{{ $isActive ? 'white' : ($isDone ? '#0d9488' : '#d1d5db') }};color:{{ $isActive ? '#0d9488' : ($isDone ? 'white' : 'white') }};">
                    @if($isDone)
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                    @else
                        {{ $num }}
                    @endif
                </span>
                <span>{{ $stepShort[$num] }}</span>
            </button>
            @if($num < 5)
            <div class="step-divider" style="background:{{ $isDone ? '#14b8a6' : '#e5e7eb' }};"></div>
            @endif
            @endforeach
        </div>
    </div>

    {{-- Step content --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border p-4 md:p-6">

        {{-- Step 1: Vital Signs --}}
        @if($currentStep === 1)
        <h3 class="text-base md:text-lg font-bold mb-2 md:mb-4">Signos Vitales</h3>
        <p class="text-xs md:text-sm text-gray-500 mb-4 md:mb-6">Opcional. Registra los signos vitales del paciente.</p>
        <div class="vitals-grid">
            <div>
                <label class="block text-xs md:text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Presión arterial</label>
                <input type="text" wire:model="blood_pressure" placeholder="120/80" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-sm">
            </div>
            <div>
                <label class="block text-xs md:text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Frec. cardíaca</label>
                <div class="relative">
                    <input type="number" wire:model="heart_rate" placeholder="72" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 pr-12 text-sm">
                    <span class="absolute right-3 top-2.5 text-xs text-gray-400">bpm</span>
                </div>
            </div>
            <div>
                <label class="block text-xs md:text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Temperatura</label>
                <div class="relative">
                    <input type="number" step="0.1" wire:model="temperature" placeholder="36.5" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 pr-10 text-sm">
                    <span class="absolute right-3 top-2.5 text-xs text-gray-400">°C</span>
                </div>
            </div>
            <div>
                <label class="block text-xs md:text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Peso</label>
                <div class="relative">
                    <input type="number" step="0.1" wire:model="weight" placeholder="70" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 pr-10 text-sm">
                    <span class="absolute right-3 top-2.5 text-xs text-gray-400">kg</span>
                </div>
            </div>
        </div>
        @endif

        {{-- Step 2: Diagnosis --}}
        @if($currentStep === 2)
        <h3 class="text-base md:text-lg font-bold mb-2 md:mb-4">Diagnóstico y Tratamiento</h3>
        <div class="space-y-3 md:space-y-4">
            <div>
                <label class="block text-xs md:text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Motivo de consulta</label>
                <textarea wire:model="chief_complaint" rows="2" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-sm" placeholder="¿Por qué viene el paciente?"></textarea>
            </div>
            <div>
                <label class="block text-xs md:text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Diagnóstico</label>
                <textarea wire:model="diagnosis" rows="3" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-sm" placeholder="Diagnóstico clínico..."></textarea>
            </div>
            <div>
                <label class="block text-xs md:text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Tratamiento realizado</label>
                <textarea wire:model="treatment" rows="3" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-sm" placeholder="Tratamiento aplicado hoy..."></textarea>
            </div>
            <div>
                <label class="block text-xs md:text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Notas adicionales</label>
                <textarea wire:model="medical_notes" rows="2" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-sm" placeholder="Observaciones..."></textarea>
            </div>
        </div>
        @endif

        {{-- Step 3: Prescription --}}
        @if($currentStep === 3)
        <h3 class="text-base md:text-lg font-bold mb-1 md:mb-2">Receta Médica</h3>
        <p class="text-xs md:text-sm text-gray-500 mb-3 md:mb-4">Opcional. Agrega medicamentos si es necesario.</p>
        <div class="space-y-3">
            @foreach($medications as $i => $med)
            <div class="p-3 md:p-4 bg-gray-50 dark:bg-gray-700 rounded-lg border">
                <div class="flex items-center justify-between mb-2 md:mb-3">
                    <span class="font-medium text-xs md:text-sm">Medicamento {{ $i + 1 }}</span>
                    <button wire:click="$set('medications', {{ json_encode(collect($medications)->forget($i)->values()->toArray()) }})" class="text-red-500 text-xs hover:text-red-700">Quitar</button>
                </div>
                <div class="meds-grid">
                    <input type="text" wire:model="medications.{{ $i }}.medication" placeholder="Medicamento" class="rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-600 text-sm med-wide">
                    <input type="text" wire:model="medications.{{ $i }}.dosage" placeholder="Dosis (500mg)" class="rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-600 text-sm">
                    <input type="text" wire:model="medications.{{ $i }}.frequency" placeholder="Cada 8 horas" class="rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-600 text-sm">
                    <input type="text" wire:model="medications.{{ $i }}.duration" placeholder="7 días" class="rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-600 text-sm">
                    <input type="text" wire:model="medications.{{ $i }}.instructions" placeholder="Indicaciones" class="rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-600 text-sm med-wide">
                </div>
            </div>
            @endforeach
            <button wire:click="$set('medications', {{ json_encode(array_merge($medications, [['medication' => '', 'dosage' => '', 'frequency' => '', 'duration' => '', 'instructions' => '']])) }})"
                class="w-full py-3 border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg text-xs md:text-sm text-gray-500 hover:border-teal-400 hover:text-teal-600 transition">
                + Agregar medicamento
            </button>
        </div>
        <div class="mt-3 md:mt-4">
            <label class="block text-xs md:text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Notas de la receta</label>
            <textarea wire:model="prescription_notes" rows="2" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-sm" placeholder="Indicaciones generales..."></textarea>
        </div>
        @endif

        {{-- Step 4: Payment --}}
        @if($currentStep === 4)
        <h3 class="text-base md:text-lg font-bold mb-1 md:mb-2">Cobro</h3>
        <p class="text-xs md:text-sm text-gray-500 mb-3 md:mb-4">Registra el pago de esta consulta.</p>
        <div class="pay-grid">
            <div>
                <label class="block text-xs md:text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Servicio</label>
                <select wire:model.live="payment_service_id" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-sm">
                    <option value="">Seleccionar...</option>
                    @foreach($this->services as $id => $name)
                    <option value="{{ $id }}">{{ $name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs md:text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Monto</label>
                <div class="relative">
                    <span class="absolute left-3 top-2.5 text-gray-400 text-sm">$</span>
                    <input type="number" wire:model="payment_amount" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 pl-7 text-sm" placeholder="0.00">
                </div>
            </div>
            <div>
                <label class="block text-xs md:text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Método de pago</label>
                <select wire:model="payment_method" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-sm">
                    <option value="cash">Efectivo</option>
                    <option value="card">Tarjeta</option>
                    <option value="transfer">Transferencia</option>
                </select>
            </div>
        </div>
        @endif

        {{-- Step 5: Next appointment --}}
        @if($currentStep === 5)
        <h3 class="text-base md:text-lg font-bold mb-1 md:mb-2">Siguiente Cita</h3>
        <p class="text-xs md:text-sm text-gray-500 mb-3 md:mb-4">Opcional. Agenda la próxima visita antes de que se vaya el paciente.</p>
        <div class="next-grid">
            <div>
                <label class="block text-xs md:text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Fecha y hora</label>
                <input type="datetime-local" wire:model="next_appointment_date" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-sm">
            </div>
            <div>
                <label class="block text-xs md:text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Servicio</label>
                <select wire:model="next_appointment_service_id" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-sm">
                    <option value="">Seleccionar...</option>
                    @foreach($this->services as $id => $name)
                    <option value="{{ $id }}">{{ $name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        @endif

        {{-- Step 6: Summary --}}
        @if($currentStep === 6 && $completed)
        <div class="text-center py-4 md:py-6">
            <div class="w-14 h-14 md:w-16 md:h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-3 md:mb-4">
                <svg class="w-7 h-7 md:w-8 md:h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
            </div>
            <h3 class="text-xl md:text-2xl font-bold text-gray-900 dark:text-white mb-1 md:mb-2">Consulta completada</h3>
            <p class="text-sm text-gray-500 mb-6 md:mb-8">Expediente de {{ $appointment->patient->first_name }} {{ $appointment->patient->last_name }} guardado.</p>

            <div class="summary-grid" style="margin-bottom:1.5rem;font-size:0.8125rem;">
                <div class="p-2 md:p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                    <div class="text-gray-500 text-[10px] md:text-xs">Diagnóstico</div>
                    <div class="font-medium mt-0.5 md:mt-1 truncate">{{ $diagnosis ?: 'No registrado' }}</div>
                </div>
                <div class="p-2 md:p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                    <div class="text-gray-500 text-[10px] md:text-xs">Medicamentos</div>
                    <div class="font-medium mt-0.5 md:mt-1">{{ count($medications) }} recetados</div>
                </div>
                <div class="p-2 md:p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                    <div class="text-gray-500 text-[10px] md:text-xs">Cobro</div>
                    <div class="font-medium mt-0.5 md:mt-1">{{ $payment_amount ? '$'.number_format($payment_amount, 0) : 'Sin cobro' }}</div>
                </div>
                <div class="p-2 md:p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                    <div class="text-gray-500 text-[10px] md:text-xs">Siguiente cita</div>
                    <div class="font-medium mt-0.5 md:mt-1">{{ $next_appointment_date ? \Carbon\Carbon::parse($next_appointment_date)->format('d/m/Y H:i') : 'No agendada' }}</div>
                </div>
            </div>

            <div class="action-btns" style="margin-top:1.5rem;">
                @if($savedPrescriptionId)
                <a href="{{ route('prescription.pdf', $savedPrescriptionId) }}" target="_blank"
                    style="background:#8b5cf6;color:white;">
                    <svg style="width:18px;height:18px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    Imprimir receta
                </a>
                @endif
                <a href="{{ route('filament.doctor.pages.perfil-paciente', ['patient' => $appointment->patient_id]) }}"
                    style="background:#3b82f6;color:white;">
                    <svg style="width:18px;height:18px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    Ver perfil
                </a>
                @if($appointment->patient->phone)
                @php
                $waMsg = "Hola {$appointment->patient->first_name}, gracias por tu visita en {$appointment->clinic->name}.";
                if ($diagnosis) $waMsg .= "\n\n*Diagnóstico:* {$diagnosis}";
                if (!empty($medications)) {
                    $waMsg .= "\n\n*Medicamentos:*";
                    foreach ($medications as $med) {
                        if (!empty($med['medication'])) {
                            $waMsg .= "\n- {$med['medication']}";
                            if (!empty($med['dosage'])) $waMsg .= " {$med['dosage']}";
                            if (!empty($med['frequency'])) $waMsg .= ", {$med['frequency']}";
                            if (!empty($med['duration'])) $waMsg .= " x {$med['duration']}";
                        }
                    }
                }
                if ($next_appointment_date) $waMsg .= "\n\n*Próxima cita:* " . \Carbon\Carbon::parse($next_appointment_date)->format('d/m/Y H:i');
                $waMsg .= "\n\n¡Que te mejores pronto!";
                @endphp
                <a href="https://wa.me/52{{ preg_replace('/\D/', '', $appointment->patient->phone) }}?text={{ urlencode($waMsg) }}" target="_blank"
                    style="background:#22c55e;color:white;">
                    <svg style="width:18px;height:18px;" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"/></svg>
                    WhatsApp
                </a>
                @endif
                <a href="{{ route('filament.doctor.pages.dashboard') }}"
                    style="background:#374151;color:white;">
                    Siguiente paciente
                </a>
            </div>
        </div>
        @endif

    </div>

    @if(!$completed)
    {{-- Navigation buttons --}}
    <div class="nav-buttons">
        <div>
            @if($currentStep > 1)
            <button wire:click="prevStep" class="nav-btn" style="background:#f3f4f6;color:#374151;">
                &larr; Anterior
            </button>
            @endif
        </div>
        <div>
            @if($currentStep < 5)
            <button wire:click="nextStep" class="nav-btn" style="background:#0d9488;color:white;">
                Siguiente &rarr;
            </button>
            @endif
            @if($currentStep === 5)
            <button wire:click="saveAndComplete" class="nav-btn" style="background:linear-gradient(135deg,#0d9488,#0891b2);color:white;font-weight:700;box-shadow:0 4px 12px rgba(13,148,136,0.3);">
                Completar consulta
            </button>
            @endif
            @if($currentStep >= 2)
            <button wire:click="saveAndComplete" class="nav-btn" style="background:#1f2937;color:white;font-size:0.8rem;">
                Guardar y terminar
            </button>
            @endif
        </div>
    </div>
    @endif

    @else
    {{-- Walk-in: select or create patient --}}
    <div class="max-w-lg mx-auto px-2 md:px-0">
        <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl md:rounded-2xl p-4 md:p-8">
            <div class="text-center mb-4 md:mb-6">
                <div class="text-4xl md:text-5xl mb-2">🩺</div>
                <div class="font-extrabold text-lg md:text-xl">Iniciar consulta</div>
                <div class="text-xs md:text-sm text-gray-500 mt-1">Busca un paciente o crea uno nuevo con el botón +</div>
            </div>

            {{ $this->walkinForm }}

            <div style="margin-top:1.5rem;">
                <button wire:click="startWalkIn" class="w-full py-3.5 md:py-3 text-white rounded-xl font-bold text-sm md:text-base cursor-pointer shadow-md hover:shadow-lg transition" style="background:linear-gradient(135deg,#0d9488,#0891b2);">
                    Iniciar consulta &rarr;
                </button>
            </div>

            <div class="text-center mt-4 md:mt-5">
                <a href="{{ route('filament.doctor.pages.dashboard') }}" class="text-gray-500 text-xs md:text-sm no-underline hover:text-gray-700">&larr; Volver al dashboard</a>
            </div>
        </div>
    </div>
    @endif
</x-filament-panels::page>
