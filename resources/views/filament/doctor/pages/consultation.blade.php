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
    <div class="flex items-center justify-center gap-2 mb-8">
        @php
        $steps = [
            1 => ['label' => 'Signos vitales', 'icon' => 'heroicon-o-heart'],
            2 => ['label' => 'Diagnóstico', 'icon' => 'heroicon-o-clipboard-document-check'],
            3 => ['label' => 'Receta', 'icon' => 'heroicon-o-document-text'],
            4 => ['label' => 'Cobro', 'icon' => 'heroicon-o-banknotes'],
            5 => ['label' => 'Siguiente cita', 'icon' => 'heroicon-o-calendar'],
        ];
        @endphp
        @foreach($steps as $num => $step)
        <button wire:click="goToStep({{ $num }})" class="flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-medium transition-all
            {{ $currentStep === $num ? 'bg-teal-600 text-white shadow-lg' : ($currentStep > $num ? 'bg-teal-100 text-teal-700' : 'bg-gray-100 text-gray-500') }}
            hover:opacity-90">
            <span class="w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold
                {{ $currentStep === $num ? 'bg-white text-teal-600' : ($currentStep > $num ? 'bg-teal-600 text-white' : 'bg-gray-300 text-white') }}">
                @if($currentStep > $num)
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                @else
                    {{ $num }}
                @endif
            </span>
            <span class="hidden sm:inline">{{ $step['label'] }}</span>
        </button>
        @if($num < 5)
        <div class="w-8 h-0.5 {{ $currentStep > $num ? 'bg-teal-500' : 'bg-gray-200' }}"></div>
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

    </div>

    {{-- Navigation buttons --}}
    <div class="flex items-center justify-between mt-6">
        <div>
            @if($currentStep > 1)
            <button wire:click="prevStep" class="px-6 py-2.5 bg-gray-100 text-gray-700 rounded-lg font-medium hover:bg-gray-200 transition">
                &larr; Anterior
            </button>
            @endif
        </div>
        <div class="flex gap-3">
            @if($currentStep < 5)
            <button wire:click="nextStep" class="px-6 py-2.5 bg-teal-600 text-white rounded-lg font-medium hover:bg-teal-700 transition">
                Siguiente &rarr;
            </button>
            @endif
            @if($currentStep === 5)
            <button wire:click="saveAndComplete" class="px-8 py-2.5 bg-gradient-to-r from-teal-600 to-cyan-600 text-white rounded-lg font-bold hover:shadow-lg transition">
                Completar consulta
            </button>
            @endif
            @if($currentStep >= 2)
            <button wire:click="saveAndComplete" class="px-6 py-2.5 bg-gray-800 text-white rounded-lg font-medium hover:bg-gray-900 transition text-sm">
                Guardar y terminar
            </button>
            @endif
        </div>
    </div>

    @else
    <div class="text-center py-12">
        <p class="text-gray-500">No se encontró la cita. Regresa al dashboard.</p>
        <a href="{{ route('filament.doctor.pages.dashboard') }}" class="mt-4 inline-block px-6 py-2 bg-teal-600 text-white rounded-lg">Ir al dashboard</a>
    </div>
    @endif
</x-filament-panels::page>
