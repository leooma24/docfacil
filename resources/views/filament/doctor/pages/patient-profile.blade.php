<x-filament-panels::page>
@if($patient)
    {{-- Patient Header Card --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border p-4 md:p-6 mb-4 md:mb-6">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div class="flex items-center gap-3 md:gap-4">
                <div class="w-12 h-12 md:w-16 md:h-16 bg-gradient-to-br from-teal-400 to-cyan-500 rounded-full flex items-center justify-center shadow-lg shrink-0">
                    <span class="text-lg md:text-2xl font-bold text-white">
                        {{ substr($patient->first_name, 0, 1) }}{{ substr($patient->last_name, 0, 1) }}
                    </span>
                </div>
                <div class="min-w-0">
                    <h2 class="text-lg md:text-2xl font-bold text-gray-900 dark:text-white truncate">{{ $patient->first_name }} {{ $patient->last_name }}</h2>
                    <div class="flex flex-wrap items-center gap-x-2 gap-y-0.5 text-xs md:text-sm text-gray-500 mt-0.5">
                        @if($patient->birth_date)
                        <span>{{ $patient->birth_date->age }} años ({{ $patient->birth_date->format('d/m/Y') }})</span>
                        @endif
                        @if($patient->gender)
                        <span>{{ $patient->gender === 'male' ? 'Masculino' : ($patient->gender === 'female' ? 'Femenino' : 'Otro') }}</span>
                        @endif
                        @if($patient->blood_type)
                        <span class="px-1.5 py-0.5 bg-red-100 text-red-700 rounded font-medium text-[10px] md:text-xs">{{ $patient->blood_type }}</span>
                        @endif
                    </div>
                </div>
            </div>
            <div class="flex flex-wrap gap-2 pl-15 md:pl-0">
                @if($patient->phone)
                <a href="https://wa.me/52{{ preg_replace('/\D/', '', $patient->phone) }}" target="_blank"
                    class="px-3 md:px-4 py-2.5 md:py-2 bg-green-500 text-white rounded-lg text-xs md:text-sm font-medium hover:bg-green-600 transition flex items-center gap-1.5 no-underline">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"/></svg>
                    WhatsApp
                </a>
                @endif
                <a href="{{ route('filament.doctor.resources.citas.create') }}?patient={{ $patient->id }}"
                    class="px-3 md:px-4 py-2.5 md:py-2 bg-teal-500 text-white rounded-lg text-xs md:text-sm font-medium hover:bg-teal-600 transition flex items-center gap-1.5 no-underline">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    Agendar
                </a>
                <a href="{{ route('filament.doctor.resources.pacientes.edit', $patient->id) }}"
                    class="px-3 md:px-4 py-2.5 md:py-2 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg text-xs md:text-sm font-medium hover:bg-gray-200 transition no-underline">
                    Editar
                </a>
            </div>
        </div>

        {{-- Quick stats --}}
        <div class="grid grid-cols-3 md:grid-cols-5 gap-2 md:gap-4 mt-4 md:mt-6 pt-4 md:pt-6 border-t">
            <div class="text-center">
                <div class="text-lg md:text-2xl font-bold text-gray-900 dark:text-white">{{ $this->stats['total_visits'] }}</div>
                <div class="text-[10px] md:text-xs text-gray-500">Consultas</div>
            </div>
            <div class="text-center">
                <div class="text-lg md:text-2xl font-bold text-gray-900 dark:text-white">{{ $this->stats['total_appointments'] }}</div>
                <div class="text-[10px] md:text-xs text-gray-500">Citas</div>
            </div>
            <div class="text-center">
                <div class="text-lg md:text-2xl font-bold text-teal-600">${{ number_format($this->stats['total_paid'], 0) }}</div>
                <div class="text-[10px] md:text-xs text-gray-500">Pagado</div>
            </div>
            <div class="text-center hidden md:block">
                <div class="text-2xl font-bold {{ $this->stats['pending'] > 0 ? 'text-amber-500' : 'text-gray-400' }}">${{ number_format($this->stats['pending'], 0) }}</div>
                <div class="text-xs text-gray-500">Pendiente</div>
            </div>
            <div class="text-center hidden md:block">
                <div class="text-sm font-bold text-gray-900 dark:text-white">{{ $this->stats['last_visit'] ? \Carbon\Carbon::parse($this->stats['last_visit'])->format('d/m/Y') : 'Sin visitas' }}</div>
                <div class="text-xs text-gray-500">Ultima visita</div>
            </div>
        </div>

        @if($patient->allergies)
        <div class="mt-3 md:mt-4 p-2.5 md:p-3 bg-red-50 dark:bg-red-950 border border-red-200 rounded-lg flex items-center gap-2">
            <svg class="w-4 h-4 md:w-5 md:h-5 text-red-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg>
            <span class="text-xs md:text-sm text-red-700 dark:text-red-300 font-medium">Alergias: {{ $patient->allergies }}</span>
        </div>
        @endif
    </div>

    {{-- Tabs --}}
    <div class="overflow-x-auto -mx-4 px-4 md:mx-0 md:px-0 mb-4 md:mb-6 scrollbar-hide">
        <div class="flex gap-1 bg-gray-100 dark:bg-gray-700 rounded-lg p-1 min-w-max md:min-w-0">
            @php
            $tabs = [
                'info' => 'Datos',
                'history' => 'Historial',
                'prescriptions' => 'Recetas',
                'payments' => 'Pagos',
                'appointments' => 'Citas',
                'odontogram' => 'Odontograma',
            ];
            @endphp
            @foreach($tabs as $key => $label)
            <button wire:click="setTab('{{ $key }}')"
                class="px-3 md:px-4 py-2 rounded-md text-xs md:text-sm font-medium transition-all whitespace-nowrap
                {{ $activeTab === $key ? 'bg-white dark:bg-gray-800 text-teal-600 shadow-sm' : 'text-gray-500 hover:text-gray-700' }}">
                {{ $label }}
            </button>
            @endforeach
        </div>
    </div>

    {{-- Tab Content --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border">

        {{-- Info tab --}}
        @if($activeTab === 'info')
        <div class="p-4 md:p-6 grid md:grid-cols-2 gap-4 md:gap-6">
            <div class="space-y-3 md:space-y-4">
                <h3 class="font-bold text-sm md:text-base text-gray-900 dark:text-white">Datos Personales</h3>
                <div class="space-y-2 md:space-y-3 text-xs md:text-sm">
                    <div class="flex justify-between"><span class="text-gray-500">Teléfono</span><span class="font-medium">{{ $patient->phone ?? '-' }}</span></div>
                    <div class="flex justify-between"><span class="text-gray-500">Email</span><span class="font-medium truncate ml-2">{{ $patient->email ?? '-' }}</span></div>
                    <div class="flex justify-between"><span class="text-gray-500">Dirección</span><span class="font-medium truncate ml-2">{{ $patient->address ?? '-' }}</span></div>
                    <div class="flex justify-between"><span class="text-gray-500">Género</span><span class="font-medium">{{ $patient->gender === 'male' ? 'Masculino' : ($patient->gender === 'female' ? 'Femenino' : ($patient->gender ?? '-')) }}</span></div>
                </div>
            </div>
            <div class="space-y-3 md:space-y-4">
                <h3 class="font-bold text-sm md:text-base text-gray-900 dark:text-white">Información Médica</h3>
                <div class="space-y-2 md:space-y-3 text-xs md:text-sm">
                    <div class="flex justify-between"><span class="text-gray-500">Tipo de sangre</span><span class="font-medium">{{ $patient->blood_type ?? '-' }}</span></div>
                    <div class="flex justify-between"><span class="text-gray-500">Alergias</span><span class="font-medium text-red-600">{{ $patient->allergies ?? 'Ninguna' }}</span></div>
                </div>
                @if($patient->medical_notes)
                <div class="mt-3 md:mt-4 p-2.5 md:p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                    <div class="text-[10px] md:text-xs text-gray-500 font-medium mb-1">Notas médicas</div>
                    <div class="text-xs md:text-sm">{{ $patient->medical_notes }}</div>
                </div>
                @endif
            </div>
        </div>
        @endif

        {{-- History tab --}}
        @if($activeTab === 'history')
        <div class="divide-y">
            @forelse($this->medicalRecords as $record)
            <div class="p-4 md:p-5 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition">
                <div class="flex items-start justify-between mb-1.5 md:mb-2">
                    <div>
                        <span class="text-xs md:text-sm font-bold text-gray-900 dark:text-white">{{ $record->visit_date->format('d/m/Y') }}</span>
                        <span class="text-[10px] md:text-xs text-gray-500 ml-2">{{ $record->doctor->user->name ?? '' }}</span>
                    </div>
                </div>
                @if($record->chief_complaint)<div class="text-xs md:text-sm"><span class="text-gray-500">Motivo:</span> {{ $record->chief_complaint }}</div>@endif
                @if($record->diagnosis)<div class="text-xs md:text-sm mt-0.5 md:mt-1"><span class="text-gray-500">Dx:</span> <span class="font-medium">{{ $record->diagnosis }}</span></div>@endif
                @if($record->treatment)<div class="text-xs md:text-sm mt-0.5 md:mt-1"><span class="text-gray-500">Tx:</span> {{ $record->treatment }}</div>@endif
            </div>
            @empty
            <div class="p-8 text-center text-gray-400 text-sm">Sin historial clínico</div>
            @endforelse
        </div>
        @endif

        {{-- Prescriptions tab --}}
        @if($activeTab === 'prescriptions')
        <div class="divide-y">
            @forelse($this->prescriptions as $rx)
            <div class="p-4 md:p-5">
                <div class="flex items-center justify-between mb-2 md:mb-3">
                    <div>
                        <span class="font-bold text-xs md:text-sm">{{ $rx->prescription_date->format('d/m/Y') }}</span>
                        <span class="text-[10px] md:text-xs text-gray-500 ml-2">{{ $rx->doctor->user->name ?? '' }}</span>
                    </div>
                    <span class="text-[10px] md:text-xs text-gray-500">{{ $rx->items->count() }} med.</span>
                </div>
                @if($rx->diagnosis)<div class="text-xs md:text-sm text-gray-600 mb-2">{{ $rx->diagnosis }}</div>@endif
                <div class="space-y-1">
                    @foreach($rx->items as $item)
                    <div class="text-xs md:text-sm bg-gray-50 dark:bg-gray-700 rounded px-2.5 md:px-3 py-1.5 md:py-2">
                        <span class="font-medium">{{ $item->medication }}</span>
                        <span class="text-gray-500">— {{ $item->dosage }} {{ $item->frequency }} x {{ $item->duration }}</span>
                    </div>
                    @endforeach
                </div>
            </div>
            @empty
            <div class="p-8 text-center text-gray-400 text-sm">Sin recetas</div>
            @endforelse
        </div>
        @endif

        {{-- Payments tab - card layout on mobile, table on desktop --}}
        @if($activeTab === 'payments')
        {{-- Desktop table --}}
        <div class="hidden md:block">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 dark:bg-gray-700">
                    <tr>
                        <th class="px-5 py-3 text-left text-gray-500 font-medium">Fecha</th>
                        <th class="px-5 py-3 text-left text-gray-500 font-medium">Servicio</th>
                        <th class="px-5 py-3 text-right text-gray-500 font-medium">Monto</th>
                        <th class="px-5 py-3 text-center text-gray-500 font-medium">Método</th>
                        <th class="px-5 py-3 text-center text-gray-500 font-medium">Estado</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @forelse($this->payments as $pay)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                        <td class="px-5 py-3">{{ $pay->payment_date->format('d/m/Y') }}</td>
                        <td class="px-5 py-3">{{ $pay->service->name ?? '-' }}</td>
                        <td class="px-5 py-3 text-right font-bold">${{ number_format($pay->amount, 0) }}</td>
                        <td class="px-5 py-3 text-center">{{ match($pay->payment_method) { 'cash' => 'Efectivo', 'card' => 'Tarjeta', 'transfer' => 'Transf.', default => $pay->payment_method } }}</td>
                        <td class="px-5 py-3 text-center">
                            <span class="px-2 py-1 rounded-full text-xs font-medium {{ $pay->status === 'paid' ? 'bg-green-100 text-green-700' : 'bg-amber-100 text-amber-700' }}">
                                {{ $pay->status === 'paid' ? 'Pagado' : ($pay->status === 'pending' ? 'Pendiente' : 'Parcial') }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="p-8 text-center text-gray-400">Sin pagos registrados</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{-- Mobile cards --}}
        <div class="md:hidden divide-y">
            @forelse($this->payments as $pay)
            <div class="p-3 flex items-center justify-between gap-3">
                <div class="min-w-0">
                    <div class="text-xs font-bold text-gray-900 dark:text-white">{{ $pay->payment_date->format('d/m/Y') }}</div>
                    <div class="text-[10px] text-gray-500 truncate">{{ $pay->service->name ?? 'Sin servicio' }} · {{ match($pay->payment_method) { 'cash' => 'Efectivo', 'card' => 'Tarjeta', 'transfer' => 'Transf.', default => $pay->payment_method } }}</div>
                </div>
                <div class="text-right shrink-0">
                    <div class="text-sm font-bold">${{ number_format($pay->amount, 0) }}</div>
                    <span class="px-1.5 py-0.5 rounded-full text-[10px] font-medium {{ $pay->status === 'paid' ? 'bg-green-100 text-green-700' : 'bg-amber-100 text-amber-700' }}">
                        {{ $pay->status === 'paid' ? 'Pagado' : ($pay->status === 'pending' ? 'Pendiente' : 'Parcial') }}
                    </span>
                </div>
            </div>
            @empty
            <div class="p-8 text-center text-gray-400 text-sm">Sin pagos registrados</div>
            @endforelse
        </div>
        @endif

        {{-- Appointments tab --}}
        @if($activeTab === 'appointments')
        <div class="divide-y">
            @forelse($this->appointments as $apt)
            <div class="p-3 md:p-4 flex items-center justify-between gap-2 hover:bg-gray-50 dark:hover:bg-gray-700/50">
                <div class="flex items-center gap-2 md:gap-4 min-w-0">
                    <div class="text-center min-w-[40px] md:min-w-[60px]">
                        <div class="text-sm md:text-lg font-bold text-gray-900 dark:text-white">{{ $apt->starts_at->format('d') }}</div>
                        <div class="text-[10px] md:text-xs text-gray-500">{{ $apt->starts_at->translatedFormat('M') }}</div>
                    </div>
                    <div class="min-w-0">
                        <div class="text-xs md:text-sm font-medium truncate">{{ $apt->starts_at->format('H:i') }} — {{ $apt->service->name ?? 'Sin servicio' }}</div>
                        <div class="text-[10px] md:text-xs text-gray-500">{{ $apt->doctor->user->name ?? '' }}</div>
                    </div>
                </div>
                <span class="px-1.5 md:px-2 py-0.5 md:py-1 rounded-full text-[10px] md:text-xs font-medium shrink-0
                    {{ match($apt->status) { 'completed' => 'bg-green-100 text-green-700', 'scheduled' => 'bg-amber-100 text-amber-700', 'confirmed' => 'bg-blue-100 text-blue-700', 'cancelled' => 'bg-red-100 text-red-700', 'no_show' => 'bg-gray-100 text-gray-700', default => 'bg-gray-100 text-gray-700' } }}">
                    {{ match($apt->status) { 'completed' => 'Completada', 'scheduled' => 'Programada', 'confirmed' => 'Confirmada', 'cancelled' => 'Cancelada', 'no_show' => 'No asistió', 'in_progress' => 'En consulta', default => $apt->status } }}
                </span>
            </div>
            @empty
            <div class="p-8 text-center text-gray-400 text-sm">Sin citas</div>
            @endforelse
        </div>
        @endif

        {{-- Odontogram tab --}}
        @if($activeTab === 'odontogram')
        <div class="p-4 md:p-6">
            @forelse($this->odontograms as $odonto)
            <div class="mb-6">
                <div class="flex items-center justify-between mb-3 md:mb-4">
                    <div>
                        <span class="font-bold text-xs md:text-base">{{ $odonto->evaluation_date->format('d/m/Y') }}</span>
                        <span class="text-[10px] md:text-sm text-gray-500 ml-2">{{ $odonto->doctor->user->name ?? '' }}</span>
                    </div>
                    <span class="text-[10px] md:text-sm text-gray-500">{{ $odonto->teeth->count() }} dientes</span>
                </div>
                @if($odonto->teeth->count())
                <div class="grid grid-cols-4 sm:grid-cols-6 md:grid-cols-8 gap-1.5 md:gap-2">
                    @foreach($odonto->teeth as $tooth)
                    <div class="text-center p-1.5 md:p-2 rounded-lg border text-[10px] md:text-xs" style="background-color: {{ \App\Models\OdontogramTooth::conditionColors()[$tooth->condition] ?? '#10b981' }}15; border-color: {{ \App\Models\OdontogramTooth::conditionColors()[$tooth->condition] ?? '#10b981' }}40;">
                        <div class="font-bold">{{ $tooth->tooth_number }}</div>
                        <div style="color: {{ \App\Models\OdontogramTooth::conditionColors()[$tooth->condition] ?? '#10b981' }}">
                            {{ \App\Models\OdontogramTooth::conditionLabels()[$tooth->condition] ?? $tooth->condition }}
                        </div>
                    </div>
                    @endforeach
                </div>
                @endif
                @if($odonto->notes)<p class="text-xs md:text-sm text-gray-500 mt-2 md:mt-3">{{ $odonto->notes }}</p>@endif
            </div>
            @empty
            <div class="text-center text-gray-400 py-8 text-sm">Sin odontogramas</div>
            @endforelse
        </div>
        @endif

    </div>
@endif
</x-filament-panels::page>
