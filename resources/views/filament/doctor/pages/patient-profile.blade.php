<x-filament-panels::page>
<style>
    .pp-hero { position: relative; background: linear-gradient(135deg, #0d9488 0%, #0891b2 50%, #7c3aed 100%); border-radius: 1.5rem; padding: 28px 32px; color: white; overflow: hidden; margin-bottom: 20px; box-shadow: 0 20px 60px -15px rgba(13,148,136,0.4); }
    .pp-hero::before { content: ''; position: absolute; top: -80px; right: -60px; width: 280px; height: 280px; background: radial-gradient(circle, rgba(255,255,255,0.15), transparent 70%); border-radius: 50%; pointer-events: none; }
    .pp-hero::after { content: ''; position: absolute; bottom: -100px; left: -40px; width: 240px; height: 240px; background: radial-gradient(circle, rgba(139,92,246,0.25), transparent 70%); border-radius: 50%; pointer-events: none; }
    .pp-hero-content { position: relative; z-index: 1; }
    .pp-hero-top { display: flex; flex-direction: column; gap: 16px; }
    @media (min-width: 768px) { .pp-hero-top { flex-direction: row; align-items: center; justify-content: space-between; gap: 20px; } }
    .pp-avatar { width: 72px; height: 72px; border-radius: 20px; background: rgba(255,255,255,0.2); backdrop-filter: blur(12px); border: 1.5px solid rgba(255,255,255,0.3); display: flex; align-items: center; justify-content: center; flex-shrink: 0; box-shadow: 0 8px 28px rgba(0,0,0,0.2); }
    .pp-avatar span { font-size: 1.75rem; font-weight: 800; letter-spacing: -0.02em; color: white; }
    .pp-identity { display: flex; align-items: center; gap: 16px; min-width: 0; }
    .pp-name { font-size: 1.6rem; font-weight: 800; letter-spacing: -0.02em; line-height: 1.15; margin: 0; color: white !important; -webkit-text-fill-color: white !important; background: none !important; }
    .pp-meta { display: flex; flex-wrap: wrap; gap: 8px 12px; font-size: 0.78rem; opacity: 0.9; margin-top: 6px; }
    .pp-meta-item { display: inline-flex; align-items: center; gap: 4px; padding: 3px 10px; background: rgba(255,255,255,0.15); border-radius: 999px; backdrop-filter: blur(8px); }
    .pp-meta-blood { background: rgba(220,38,38,0.35) !important; font-weight: 700; }
    .pp-actions { display: flex; flex-wrap: wrap; gap: 8px; }
    .pp-btn { display: inline-flex; align-items: center; gap: 6px; padding: 10px 16px; border-radius: 12px; font-weight: 700; font-size: 0.82rem; text-decoration: none; transition: all 0.2s; }
    .pp-btn-primary { background: white; color: #0f766e; box-shadow: 0 6px 18px rgba(0,0,0,0.12); }
    .pp-btn-primary:hover { transform: translateY(-2px); box-shadow: 0 10px 25px rgba(0,0,0,0.18); }
    .pp-btn-ghost { background: rgba(255,255,255,0.15); color: white; border: 1px solid rgba(255,255,255,0.25); backdrop-filter: blur(10px); }
    .pp-btn-ghost:hover { background: rgba(255,255,255,0.25); }
    .pp-btn svg { width: 15px; height: 15px; }

    .pp-stats { display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px; margin-top: 22px; }
    @media (min-width: 768px) { .pp-stats { grid-template-columns: repeat(5, 1fr); } }
    .pp-stat { background: rgba(255,255,255,0.15); backdrop-filter: blur(14px); border: 1px solid rgba(255,255,255,0.25); border-radius: 14px; padding: 14px 16px; }
    .pp-stat-label { font-size: 0.62rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.08em; opacity: 0.78; }
    .pp-stat-value { font-size: 1.75rem; font-weight: 800; letter-spacing: -0.02em; margin-top: 2px; line-height: 1.1; color: white; }
    .pp-stat-value-sm { font-size: 0.95rem; font-weight: 700; margin-top: 6px; line-height: 1.2; }

    .pp-allergies { display: flex; align-items: center; gap: 10px; margin-top: 16px; padding: 12px 16px; background: rgba(239,68,68,0.2); border: 1px solid rgba(239,68,68,0.35); backdrop-filter: blur(10px); border-radius: 12px; font-size: 0.82rem; font-weight: 600; }
    .pp-allergies svg { width: 18px; height: 18px; flex-shrink: 0; }
</style>

@if($patient)
    {{-- HERO PATIENT HEADER --}}
    <div class="pp-hero">
        <div class="pp-hero-content">
            <div class="pp-hero-top">
                <div class="pp-identity">
                    <div class="pp-avatar">
                        <span>{{ substr($patient->first_name, 0, 1) }}{{ substr($patient->last_name, 0, 1) }}</span>
                    </div>
                    <div style="min-width:0;">
                        <h2 class="pp-name">{{ $patient->first_name }} {{ $patient->last_name }}</h2>
                        <div class="pp-meta">
                            @if($patient->birth_date)
                            <span class="pp-meta-item">🎂 {{ $patient->birth_date->age }} años · {{ $patient->birth_date->format('d/m/Y') }}</span>
                            @endif
                            @if($patient->gender)
                            <span class="pp-meta-item">{{ $patient->gender === 'male' ? '♂ Masculino' : ($patient->gender === 'female' ? '♀ Femenino' : '⚥ Otro') }}</span>
                            @endif
                            @if($patient->blood_type)
                            <span class="pp-meta-item pp-meta-blood">🩸 {{ $patient->blood_type }}</span>
                            @endif
                            @if($patient->phone)
                            <span class="pp-meta-item">📞 {{ $patient->phone }}</span>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="pp-actions">
                    @if($patient->phone)
                    <a href="https://wa.me/52{{ preg_replace('/\D/', '', $patient->phone) }}" target="_blank" class="pp-btn pp-btn-primary" style="background:#22c55e;color:white;">
                        <svg fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"/></svg>
                        WhatsApp
                    </a>
                    @endif
                    <a href="{{ route('filament.doctor.resources.citas.create') }}?patient={{ $patient->id }}" class="pp-btn pp-btn-primary">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        Agendar
                    </a>
                    <a href="{{ route('filament.doctor.resources.pacientes.edit', $patient->id) }}" class="pp-btn pp-btn-ghost">
                        Editar
                    </a>
                </div>
            </div>

            {{-- STATS GRID --}}
            <div class="pp-stats">
                <div class="pp-stat">
                    <div class="pp-stat-label">Consultas</div>
                    <div class="pp-stat-value">{{ $this->stats['total_visits'] }}</div>
                </div>
                <div class="pp-stat">
                    <div class="pp-stat-label">Citas totales</div>
                    <div class="pp-stat-value">{{ $this->stats['total_appointments'] }}</div>
                </div>
                <div class="pp-stat">
                    <div class="pp-stat-label">Total pagado</div>
                    <div class="pp-stat-value">${{ number_format($this->stats['total_paid'], 0) }}</div>
                </div>
                <div class="pp-stat">
                    <div class="pp-stat-label">Pendiente</div>
                    <div class="pp-stat-value" style="{{ $this->stats['pending'] > 0 ? 'color:#fbbf24;' : 'opacity:0.6;' }}">${{ number_format($this->stats['pending'], 0) }}</div>
                </div>
                <div class="pp-stat">
                    <div class="pp-stat-label">Última visita</div>
                    <div class="pp-stat-value-sm">{{ $this->stats['last_visit'] ? \Carbon\Carbon::parse($this->stats['last_visit'])->format('d/m/Y') : 'Sin visitas' }}</div>
                </div>
            </div>

            @if($patient->allergies)
            <div class="pp-allergies">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg>
                <span>⚠️ Alergias: {{ $patient->allergies }}</span>
            </div>
            @endif
        </div>
    </div>

    @if(config('services.ai.enabled'))
    {{-- AI Summary Card --}}
    <div wire:init="loadAiSummary" style="background:linear-gradient(135deg,#ecfeff 0%,#f0fdfa 100%);border:1px solid #99f6e4;border-radius:14px;padding:16px 18px;margin-bottom:16px;position:relative;overflow:hidden;">
        <div style="display:flex;align-items:center;justify-content:space-between;gap:12px;margin-bottom:8px;flex-wrap:wrap;">
            <div style="display:flex;align-items:center;gap:8px;">
                <div style="width:28px;height:28px;background:linear-gradient(135deg,#0d9488,#0891b2);border-radius:8px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                    <svg style="width:16px;height:16px;color:white;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg>
                </div>
                <div>
                    <div style="font-size:11px;color:#0f766e;font-weight:700;text-transform:uppercase;letter-spacing:0.5px;">Resumen IA</div>
                    <div style="font-size:10px;color:#64748b;">Generado por IA</div>
                </div>
            </div>
            <button wire:click="refreshAiSummary" wire:loading.attr="disabled" style="display:inline-flex;align-items:center;gap:4px;padding:6px 10px;background:white;border:1px solid #99f6e4;border-radius:8px;font-size:11px;color:#0f766e;cursor:pointer;font-weight:600;">
                <svg wire:loading.remove wire:target="refreshAiSummary" style="width:12px;height:12px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                <svg wire:loading wire:target="refreshAiSummary" style="width:12px;height:12px;animation:spin 1s linear infinite;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                Actualizar
            </button>
        </div>

        <div wire:loading wire:target="loadAiSummary,refreshAiSummary" style="display:flex;align-items:center;gap:8px;color:#64748b;font-size:13px;padding:4px 0;">
            <div style="width:10px;height:10px;background:#0d9488;border-radius:50%;animation:pulse 1.2s infinite;"></div>
            Analizando historial del paciente...
        </div>

        <div wire:loading.remove wire:target="loadAiSummary,refreshAiSummary">
            @if($aiSummary)
                <p style="font-size:13px;line-height:1.6;color:#1f2937;margin:0;">{{ $aiSummary }}</p>
            @else
                <p style="font-size:13px;color:#94a3b8;font-style:italic;margin:0;">No se pudo generar el resumen. Verifica la configuración de IA.</p>
            @endif
        </div>
    </div>
    @endif {{-- AI Summary Card --}}
    <style>
        @keyframes pulse { 0%,100% { opacity: 1; transform: scale(1); } 50% { opacity: 0.4; transform: scale(0.8); } }
        @keyframes spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }
    </style>

    @if(config('services.ai.enabled'))
    {{-- AI Message Generator --}}
    <div style="background:white;border:1px solid #e5e7eb;border-radius:14px;padding:14px 16px;margin-bottom:16px;">
        <div style="display:flex;align-items:center;gap:8px;margin-bottom:10px;">
            <svg style="width:16px;height:16px;color:#0d9488;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
            <span style="font-size:12px;font-weight:700;color:#374151;">Generar mensaje de WhatsApp con IA</span>
        </div>
        <div style="display:flex;gap:6px;flex-wrap:wrap;">
            <button wire:click="generateMessage('reminder')" wire:loading.attr="disabled" style="padding:6px 12px;background:#f0fdfa;border:1px solid #99f6e4;color:#0f766e;border-radius:999px;font-size:11px;font-weight:600;cursor:pointer;">📅 Recordatorio</button>
            <button wire:click="generateMessage('followup')" wire:loading.attr="disabled" style="padding:6px 12px;background:#f0fdfa;border:1px solid #99f6e4;color:#0f766e;border-radius:999px;font-size:11px;font-weight:600;cursor:pointer;">💬 Seguimiento</button>
            <button wire:click="generateMessage('birthday')" wire:loading.attr="disabled" style="padding:6px 12px;background:#f0fdfa;border:1px solid #99f6e4;color:#0f766e;border-radius:999px;font-size:11px;font-weight:600;cursor:pointer;">🎂 Cumpleaños</button>
            <button wire:click="generateMessage('promotion')" wire:loading.attr="disabled" style="padding:6px 12px;background:#f0fdfa;border:1px solid #99f6e4;color:#0f766e;border-radius:999px;font-size:11px;font-weight:600;cursor:pointer;">🎁 Oferta de regreso</button>
            <button wire:click="generateMessage('payment')" wire:loading.attr="disabled" style="padding:6px 12px;background:#f0fdfa;border:1px solid #99f6e4;color:#0f766e;border-radius:999px;font-size:11px;font-weight:600;cursor:pointer;">💰 Pago pendiente</button>
            <button wire:click="generateMessage('checkup')" wire:loading.attr="disabled" style="padding:6px 12px;background:#f0fdfa;border:1px solid #99f6e4;color:#0f766e;border-radius:999px;font-size:11px;font-weight:600;cursor:pointer;">🔍 Revisión</button>
        </div>

        <div wire:loading wire:target="generateMessage" style="margin-top:10px;padding:10px;background:#f9fafb;border-radius:8px;font-size:12px;color:#6b7280;">
            ✨ Generando mensaje con IA...
        </div>

        @if($generatedMessage)
        <div wire:loading.remove wire:target="generateMessage" style="margin-top:12px;padding:12px 14px;background:#f0fdfa;border:1px solid #5eead4;border-radius:10px;">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:8px;">
                <div style="font-size:10px;color:#0f766e;font-weight:700;text-transform:uppercase;letter-spacing:0.5px;">Mensaje generado</div>
                <button wire:click="closeMessage" style="background:none;border:none;color:#64748b;cursor:pointer;font-size:14px;">✕</button>
            </div>
            <div style="font-size:13px;color:#1f2937;line-height:1.6;white-space:pre-wrap;padding:10px 12px;background:white;border-radius:8px;border:1px solid #e5e7eb;">{{ $generatedMessage }}</div>
            @if($patient->phone)
            <a href="{{ $this->whatsappUrl }}" target="_blank" style="display:inline-flex;align-items:center;gap:6px;margin-top:10px;padding:8px 14px;background:#22c55e;color:white;border-radius:8px;font-size:12px;font-weight:700;text-decoration:none;">
                <svg style="width:14px;height:14px;" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"/></svg>
                Enviar por WhatsApp
            </a>
            @endif
        </div>
        @endif
    </div>
    @endif {{-- AI Message Generator --}}

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

        {{-- Odontogram tab — vista visual de la arcada dental (read-only) --}}
        @if($activeTab === 'odontogram')
        <div class="p-4 md:p-6">
            @php
                // FDI tooth numbering: cuadrantes 1-4 superior, 5-8 inferior
                // Sup. derecho: 18-11 (de molar a incisivo central)
                // Sup. izquierdo: 21-28
                // Inf. izquierdo: 38-31 (de molar a incisivo)
                // Inf. derecho: 41-48
                $upperRight = [18, 17, 16, 15, 14, 13, 12, 11];
                $upperLeft  = [21, 22, 23, 24, 25, 26, 27, 28];
                $lowerLeft  = [38, 37, 36, 35, 34, 33, 32, 31];
                $lowerRight = [41, 42, 43, 44, 45, 46, 47, 48];
                $colors = \App\Models\OdontogramTooth::conditionColors();
                $labels = \App\Models\OdontogramTooth::conditionLabels();
            @endphp

            @forelse($this->odontograms as $odonto)
            @php
                // Indexar dientes por número para lookup rápido
                $byNum = $odonto->teeth->keyBy('tooth_number');
            @endphp
            <div class="mb-8">
                {{-- Header: fecha + doctor + total + boton editar --}}
                <div class="flex items-center justify-between mb-4 pb-3 border-b border-gray-100">
                    <div>
                        <div class="font-bold text-sm md:text-base text-gray-900">{{ $odonto->evaluation_date->format('d/m/Y') }}</div>
                        <div class="text-[11px] md:text-xs text-gray-500 mt-0.5">
                            {{ $odonto->doctor->user->name ?? 'Sin doctor' }}
                            <span class="mx-1.5 text-gray-300">·</span>
                            {{ $odonto->teeth->count() }} dientes con condición
                        </div>
                    </div>
                    <a href="{{ route('filament.doctor.resources.odontogramas.edit', ['record' => $odonto->id]) }}"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-teal-50 text-teal-700 hover:bg-teal-100 text-xs font-semibold rounded-lg border border-teal-200 transition">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        Editar
                    </a>
                </div>

                {{-- Arcada dental visual --}}
                <div class="bg-gradient-to-b from-gray-50 to-white border border-gray-200 rounded-xl p-3 md:p-5">
                    {{-- ARCADA SUPERIOR --}}
                    <div class="text-[10px] md:text-xs font-bold tracking-wider text-gray-400 text-center mb-2">SUPERIOR</div>
                    <div class="flex justify-center gap-0.5 md:gap-1 mb-1">
                        {{-- Cuadrante superior derecho (paciente) — se muestra a la izquierda visual --}}
                        @foreach($upperRight as $num)
                        @php
                            $tooth = $byNum->get($num);
                            $cond = $tooth?->condition ?? 'sano';
                            $color = $colors[$cond] ?? '#cbd5e1';
                            $label = $labels[$cond] ?? null;
                        @endphp
                        <div class="group relative" title="Diente {{ $num }} — {{ $label ?? 'Sano' }}{{ $tooth?->notes ? ' · ' . $tooth->notes : '' }}">
                            <div class="w-7 h-9 md:w-9 md:h-12 rounded-t-xl border-2 flex flex-col items-center justify-end pb-1 transition hover:scale-110 cursor-help"
                                 style="background-color: {{ $color }}25; border-color: {{ $color }};">
                                <div class="w-3 h-3 md:w-4 md:h-4 rounded-sm" style="background-color: {{ $color }};"></div>
                                <span class="text-[8px] md:text-[10px] font-bold text-gray-700 mt-0.5">{{ $num }}</span>
                            </div>
                        </div>
                        @endforeach
                        {{-- Línea media --}}
                        <div class="w-px bg-gray-300 mx-1 self-stretch"></div>
                        @foreach($upperLeft as $num)
                        @php
                            $tooth = $byNum->get($num);
                            $cond = $tooth?->condition ?? 'sano';
                            $color = $colors[$cond] ?? '#cbd5e1';
                            $label = $labels[$cond] ?? null;
                        @endphp
                        <div class="group relative" title="Diente {{ $num }} — {{ $label ?? 'Sano' }}{{ $tooth?->notes ? ' · ' . $tooth->notes : '' }}">
                            <div class="w-7 h-9 md:w-9 md:h-12 rounded-t-xl border-2 flex flex-col items-center justify-end pb-1 transition hover:scale-110 cursor-help"
                                 style="background-color: {{ $color }}25; border-color: {{ $color }};">
                                <div class="w-3 h-3 md:w-4 md:h-4 rounded-sm" style="background-color: {{ $color }};"></div>
                                <span class="text-[8px] md:text-[10px] font-bold text-gray-700 mt-0.5">{{ $num }}</span>
                            </div>
                        </div>
                        @endforeach
                    </div>

                    {{-- Separador entre arcadas --}}
                    <div class="border-t-2 border-dashed border-gray-300 my-3 mx-4"></div>

                    {{-- ARCADA INFERIOR --}}
                    <div class="flex justify-center gap-0.5 md:gap-1 mt-1">
                        @foreach($lowerLeft as $num)
                        @php
                            $tooth = $byNum->get($num);
                            $cond = $tooth?->condition ?? 'sano';
                            $color = $colors[$cond] ?? '#cbd5e1';
                            $label = $labels[$cond] ?? null;
                        @endphp
                        <div class="group relative" title="Diente {{ $num }} — {{ $label ?? 'Sano' }}{{ $tooth?->notes ? ' · ' . $tooth->notes : '' }}">
                            <div class="w-7 h-9 md:w-9 md:h-12 rounded-b-xl border-2 flex flex-col items-center justify-start pt-1 transition hover:scale-110 cursor-help"
                                 style="background-color: {{ $color }}25; border-color: {{ $color }};">
                                <span class="text-[8px] md:text-[10px] font-bold text-gray-700 mb-0.5">{{ $num }}</span>
                                <div class="w-3 h-3 md:w-4 md:h-4 rounded-sm" style="background-color: {{ $color }};"></div>
                            </div>
                        </div>
                        @endforeach
                        <div class="w-px bg-gray-300 mx-1 self-stretch"></div>
                        @foreach($lowerRight as $num)
                        @php
                            $tooth = $byNum->get($num);
                            $cond = $tooth?->condition ?? 'sano';
                            $color = $colors[$cond] ?? '#cbd5e1';
                            $label = $labels[$cond] ?? null;
                        @endphp
                        <div class="group relative" title="Diente {{ $num }} — {{ $label ?? 'Sano' }}{{ $tooth?->notes ? ' · ' . $tooth->notes : '' }}">
                            <div class="w-7 h-9 md:w-9 md:h-12 rounded-b-xl border-2 flex flex-col items-center justify-start pt-1 transition hover:scale-110 cursor-help"
                                 style="background-color: {{ $color }}25; border-color: {{ $color }};">
                                <span class="text-[8px] md:text-[10px] font-bold text-gray-700 mb-0.5">{{ $num }}</span>
                                <div class="w-3 h-3 md:w-4 md:h-4 rounded-sm" style="background-color: {{ $color }};"></div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    <div class="text-[10px] md:text-xs font-bold tracking-wider text-gray-400 text-center mt-2">INFERIOR</div>

                    {{-- Leyenda solo de condiciones presentes en este odontograma --}}
                    @php
                        $presentConditions = $odonto->teeth->pluck('condition')->unique()->values();
                    @endphp
                    @if($presentConditions->count())
                    <div class="flex flex-wrap justify-center gap-2 md:gap-3 mt-4 pt-3 border-t border-gray-100 text-[10px] md:text-xs">
                        @foreach($presentConditions as $cond)
                        <span class="inline-flex items-center gap-1.5 text-gray-600">
                            <span class="w-2.5 h-2.5 rounded-sm" style="background-color: {{ $colors[$cond] ?? '#94a3b8' }}"></span>
                            {{ $labels[$cond] ?? $cond }}
                        </span>
                        @endforeach
                    </div>
                    @endif
                </div>

                @if($odonto->notes)
                <p class="text-xs md:text-sm text-gray-600 mt-3 italic px-1">{{ $odonto->notes }}</p>
                @endif
            </div>
            @empty
            <div class="text-center text-gray-400 py-8 text-sm">Sin odontogramas registrados.</div>
            @endforelse
        </div>
        @endif

    </div>
@endif
</x-filament-panels::page>
