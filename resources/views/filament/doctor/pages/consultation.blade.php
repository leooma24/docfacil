<x-filament-panels::page>
    @if($appointment)
    {{-- Patient Header --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border p-6 mb-6">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 bg-teal-100 dark:bg-teal-900 rounded-full flex items-center justify-center">
                    <span class="text-xl font-bold text-teal-700 dark:text-teal-300">
                        {{ substr($appointment->patient->first_name, 0, 1) }}{{ substr($appointment->patient->last_name, 0, 1) }}
                    </span>
                </div>
                <div>
                    <h2 class="text-xl font-bold text-gray-900 dark:text-white">
                        {{ $appointment->patient->first_name }} {{ $appointment->patient->last_name }}
                    </h2>
                    <div class="flex items-center gap-4 text-sm text-gray-500">
                        @if($appointment->patient->birth_date)
                        <span>{{ $appointment->patient->birth_date->age }} años</span>
                        @endif
                        @if($appointment->patient->phone)
                        <span>{{ $appointment->patient->phone }}</span>
                        @endif
                        @if($appointment->patient->blood_type)
                        <span class="font-medium text-red-600">{{ $appointment->patient->blood_type }}</span>
                        @endif
                        @if($appointment->patient->allergies)
                        <span class="text-red-500 font-medium">Alergias: {{ $appointment->patient->allergies }}</span>
                        @endif
                    </div>
                </div>
            </div>
            <div class="text-right">
                <div class="text-sm text-gray-500">{{ $appointment->starts_at->translatedFormat('l d M, H:i') }}</div>
                @if($appointment->service)
                <div class="text-sm font-medium text-teal-600">{{ $appointment->service->name }}</div>
                @endif
            </div>
        </div>
    </div>

    {{-- Steps indicator --}}
    @php
    $stepLabels = [1 => 'Signos vitales', 2 => 'Diagnostico', 3 => 'Receta', 4 => 'Cobro', 5 => 'Siguiente cita'];
    @endphp
    <div style="display:flex;align-items:center;justify-content:center;gap:0.5rem;margin-bottom:2rem;flex-wrap:wrap;">
        @foreach($stepLabels as $num => $label)
        @php
            $isActive = $currentStep === $num;
            $isDone = $currentStep > $num;
            $btnBg = $isActive ? '#0d9488' : ($isDone ? '#ccfbf1' : '#f3f4f6');
            $btnColor = $isActive ? 'white' : ($isDone ? '#0f766e' : '#9ca3af');
            $circleBg = $isActive ? 'white' : ($isDone ? '#0d9488' : '#d1d5db');
            $circleColor = $isActive ? '#0d9488' : ($isDone ? 'white' : 'white');
        @endphp
        <button wire:click="goToStep({{ $num }})" style="display:flex;align-items:center;gap:0.5rem;padding:0.5rem 1rem;border-radius:0.5rem;font-size:0.875rem;font-weight:600;border:none;cursor:pointer;background:{{ $btnBg }};color:{{ $btnColor }};{{ $isActive ? 'box-shadow:0 4px 12px rgba(13,148,136,0.3);' : '' }}">
            <span style="width:24px;height:24px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:0.75rem;font-weight:700;background:{{ $circleBg }};color:{{ $circleColor }};">
                @if($isDone)
                    <svg style="width:14px;height:14px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                @else
                    {{ $num }}
                @endif
            </span>
            <span>{{ $label }}</span>
        </button>
        @if($num < 5)
        <div style="width:2rem;height:2px;background:{{ $isDone ? '#14b8a6' : '#e5e7eb' }};"></div>
        @endif
        @endforeach
    </div>

    {{-- Step content --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border p-6">

        {{-- Step 1: Vital Signs --}}
        @if($currentStep === 1)
        <h3 class="text-lg font-bold mb-4">Signos Vitales</h3>
        <p class="text-sm text-gray-500 mb-6">Opcional. Registra los signos vitales del paciente.</p>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Presión arterial</label>
                <input type="text" wire:model="blood_pressure" placeholder="120/80" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Frec. cardíaca</label>
                <div class="relative">
                    <input type="number" wire:model="heart_rate" placeholder="72" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 pr-12">
                    <span class="absolute right-3 top-2.5 text-sm text-gray-400">bpm</span>
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Temperatura</label>
                <div class="relative">
                    <input type="number" step="0.1" wire:model="temperature" placeholder="36.5" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 pr-10">
                    <span class="absolute right-3 top-2.5 text-sm text-gray-400">°C</span>
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Peso</label>
                <div class="relative">
                    <input type="number" step="0.1" wire:model="weight" placeholder="70" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 pr-10">
                    <span class="absolute right-3 top-2.5 text-sm text-gray-400">kg</span>
                </div>
            </div>
        </div>
        @endif

        {{-- Step 2: Diagnosis --}}
        @if($currentStep === 2)
        <h3 class="text-lg font-bold mb-4">Diagnóstico y Tratamiento</h3>
        <div class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Motivo de consulta</label>
                <textarea wire:model="chief_complaint" rows="2" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700" placeholder="¿Por qué viene el paciente?"></textarea>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Diagnóstico</label>
                <textarea wire:model="diagnosis" rows="3" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700" placeholder="Diagnóstico clínico..."></textarea>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Tratamiento realizado</label>
                <textarea wire:model="treatment" rows="3" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700" placeholder="Tratamiento aplicado hoy..."></textarea>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Notas adicionales</label>
                <textarea wire:model="medical_notes" rows="2" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700" placeholder="Observaciones..."></textarea>
            </div>
        </div>
        @endif

        {{-- Step 3: Prescription --}}
        @if($currentStep === 3)
        <h3 class="text-lg font-bold mb-2">Receta Médica</h3>
        <p class="text-sm text-gray-500 mb-4">Opcional. Agrega medicamentos si es necesario.</p>
        <div class="space-y-3">
            @foreach($medications as $i => $med)
            <div class="p-4 bg-gray-50 dark:bg-gray-700 rounded-lg border">
                <div class="flex items-center justify-between mb-3">
                    <span class="font-medium text-sm">Medicamento {{ $i + 1 }}</span>
                    <button wire:click="$set('medications', {{ json_encode(collect($medications)->forget($i)->values()->toArray()) }})" class="text-red-500 text-sm hover:text-red-700">Quitar</button>
                </div>
                <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
                    <input type="text" wire:model="medications.{{ $i }}.medication" placeholder="Medicamento" class="rounded-lg border-gray-300 text-sm col-span-2 md:col-span-1">
                    <input type="text" wire:model="medications.{{ $i }}.dosage" placeholder="Dosis (500mg)" class="rounded-lg border-gray-300 text-sm">
                    <input type="text" wire:model="medications.{{ $i }}.frequency" placeholder="Cada 8 horas" class="rounded-lg border-gray-300 text-sm">
                    <input type="text" wire:model="medications.{{ $i }}.duration" placeholder="7 días" class="rounded-lg border-gray-300 text-sm">
                    <input type="text" wire:model="medications.{{ $i }}.instructions" placeholder="Indicaciones" class="rounded-lg border-gray-300 text-sm col-span-2">
                </div>
            </div>
            @endforeach
            <button wire:click="$set('medications', {{ json_encode(array_merge($medications, [['medication' => '', 'dosage' => '', 'frequency' => '', 'duration' => '', 'instructions' => '']])) }})"
                class="w-full py-3 border-2 border-dashed border-gray-300 rounded-lg text-sm text-gray-500 hover:border-teal-400 hover:text-teal-600 transition">
                + Agregar medicamento
            </button>
        </div>
        <div class="mt-4">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Notas de la receta</label>
            <textarea wire:model="prescription_notes" rows="2" class="w-full rounded-lg border-gray-300" placeholder="Indicaciones generales..."></textarea>
        </div>
        @endif

        {{-- Step 4: Payment --}}
        @if($currentStep === 4)
        <h3 class="text-lg font-bold mb-2">Cobro</h3>
        <p class="text-sm text-gray-500 mb-4">Registra el pago de esta consulta.</p>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Servicio</label>
                <select wire:model.live="payment_service_id" class="w-full rounded-lg border-gray-300">
                    <option value="">Seleccionar...</option>
                    @foreach($this->services as $id => $name)
                    <option value="{{ $id }}">{{ $name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Monto</label>
                <div class="relative">
                    <span class="absolute left-3 top-2.5 text-gray-400">$</span>
                    <input type="number" wire:model="payment_amount" class="w-full rounded-lg border-gray-300 pl-7" placeholder="0.00">
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Método de pago</label>
                <select wire:model="payment_method" class="w-full rounded-lg border-gray-300">
                    <option value="cash">Efectivo</option>
                    <option value="card">Tarjeta</option>
                    <option value="transfer">Transferencia</option>
                </select>
            </div>
        </div>
        @endif

        {{-- Step 5: Next appointment --}}
        @if($currentStep === 5)
        <h3 class="text-lg font-bold mb-2">Siguiente Cita</h3>
        <p class="text-sm text-gray-500 mb-4">Opcional. Agenda la próxima visita antes de que se vaya el paciente.</p>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Fecha y hora</label>
                <input type="datetime-local" wire:model="next_appointment_date" class="w-full rounded-lg border-gray-300">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Servicio</label>
                <select wire:model="next_appointment_service_id" class="w-full rounded-lg border-gray-300">
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
        <div class="text-center py-6">
            <div style="width:64px;height:64px;background:#d1fae5;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 1rem;">
                <svg style="width:32px;height:32px;color:#059669;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
            </div>
            <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">Consulta completada</h3>
            <p class="text-gray-500 mb-8">Expediente de {{ $appointment->patient->first_name }} {{ $appointment->patient->last_name }} guardado correctamente.</p>

            <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-8 text-sm">
                <div class="p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                    <div class="text-gray-500 text-xs">Diagnostico</div>
                    <div class="font-medium mt-1">{{ $diagnosis ?: 'No registrado' }}</div>
                </div>
                <div class="p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                    <div class="text-gray-500 text-xs">Medicamentos</div>
                    <div class="font-medium mt-1">{{ count($medications) }} recetados</div>
                </div>
                <div class="p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                    <div class="text-gray-500 text-xs">Cobro</div>
                    <div class="font-medium mt-1">{{ $payment_amount ? '$'.number_format($payment_amount, 0) : 'Sin cobro' }}</div>
                </div>
                <div class="p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                    <div class="text-gray-500 text-xs">Siguiente cita</div>
                    <div class="font-medium mt-1">{{ $next_appointment_date ? \Carbon\Carbon::parse($next_appointment_date)->format('d/m/Y H:i') : 'No agendada' }}</div>
                </div>
            </div>

            <div class="flex flex-wrap justify-center gap-3">
                @if($savedPrescriptionId)
                <a href="{{ route('filament.doctor.resources.prescriptions.edit', $savedPrescriptionId) }}"
                    style="display:inline-flex;align-items:center;gap:0.5rem;padding:0.625rem 1.25rem;background:#8b5cf6;color:white;border-radius:0.75rem;font-weight:600;font-size:0.875rem;text-decoration:none;">
                    <svg style="width:18px;height:18px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    Ver / Imprimir receta
                </a>
                @endif
                <a href="{{ route('filament.doctor.pages.patient-profile', ['patient' => $appointment->patient_id]) }}"
                    style="display:inline-flex;align-items:center;gap:0.5rem;padding:0.625rem 1.25rem;background:#3b82f6;color:white;border-radius:0.75rem;font-weight:600;font-size:0.875rem;text-decoration:none;">
                    <svg style="width:18px;height:18px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    Ver perfil paciente
                </a>
                @if($appointment->patient->phone)
                <a href="https://wa.me/52{{ preg_replace('/\D/', '', $appointment->patient->phone) }}" target="_blank"
                    style="display:inline-flex;align-items:center;gap:0.5rem;padding:0.625rem 1.25rem;background:#22c55e;color:white;border-radius:0.75rem;font-weight:600;font-size:0.875rem;text-decoration:none;">
                    <svg style="width:18px;height:18px;" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"/></svg>
                    Enviar receta por WhatsApp
                </a>
                @endif
                <a href="{{ route('filament.doctor.pages.dashboard') }}"
                    style="display:inline-flex;align-items:center;gap:0.5rem;padding:0.625rem 1.25rem;background:#374151;color:white;border-radius:0.75rem;font-weight:600;font-size:0.875rem;text-decoration:none;">
                    Siguiente paciente
                </a>
            </div>
        </div>
        @endif

    </div>

    @if(!$completed)
    {{-- Navigation buttons --}}
    <div style="display:flex;align-items:center;justify-content:space-between;margin-top:1.5rem;">
        <div>
            @if($currentStep > 1)
            <button wire:click="prevStep" style="padding:0.625rem 1.5rem;background:#f3f4f6;color:#374151;border:none;border-radius:0.75rem;font-weight:600;font-size:0.875rem;cursor:pointer;">
                &larr; Anterior
            </button>
            @endif
        </div>
        <div style="display:flex;gap:0.75rem;">
            @if($currentStep < 5)
            <button wire:click="nextStep" style="padding:0.625rem 1.5rem;background:#0d9488;color:white;border:none;border-radius:0.75rem;font-weight:600;font-size:0.875rem;cursor:pointer;">
                Siguiente &rarr;
            </button>
            @endif
            @if($currentStep === 5)
            <button wire:click="saveAndComplete" style="padding:0.625rem 2rem;background:linear-gradient(135deg,#0d9488,#0891b2);color:white;border:none;border-radius:0.75rem;font-weight:700;font-size:0.875rem;cursor:pointer;box-shadow:0 4px 12px rgba(13,148,136,0.3);">
                Completar consulta
            </button>
            @endif
            @if($currentStep >= 2)
            <button wire:click="saveAndComplete" style="padding:0.625rem 1.5rem;background:#1f2937;color:white;border:none;border-radius:0.75rem;font-weight:600;font-size:0.8rem;cursor:pointer;">
                Guardar y terminar
            </button>
            @endif
        </div>
    </div>
    @endif

    @else
    <div class="text-center py-12">
        <p class="text-gray-500">No se encontro la cita. Regresa al dashboard.</p>
        <a href="{{ route('filament.doctor.pages.dashboard') }}" class="mt-4 inline-block px-6 py-2 bg-teal-600 text-white rounded-lg">Ir al dashboard</a>
    </div>
    @endif
</x-filament-panels::page>
