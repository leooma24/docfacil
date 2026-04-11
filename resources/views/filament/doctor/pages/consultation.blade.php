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

        /* Voice dictation mic button */
        .field-with-mic { display: flex; gap: 0.375rem; align-items: stretch; }
        .field-with-mic > .field-main { flex: 1; min-width: 0; }
        .mic-btn { display: inline-flex; align-items: center; justify-content: center; width: 2.25rem; min-width: 2.25rem; border-radius: 0.5rem; border: 1px solid #d1d5db; background: #f9fafb; cursor: pointer; flex-shrink: 0; transition: all 0.15s; color: #6b7280; align-self: flex-end; height: 2.5rem; }
        .mic-btn:hover { background: #f3f4f6; color: #0d9488; }
        .mic-btn.recording { background: #dc2626; border-color: #dc2626; color: #fff; animation: micPulse 1.2s infinite; }
        .mic-btn svg { width: 1.1rem; height: 1.1rem; }
        @keyframes micPulse { 0%,100% { box-shadow: 0 0 0 0 rgba(220,38,38,0.55); } 50% { box-shadow: 0 0 0 8px rgba(220,38,38,0); } }
    </style>

    <script>
        window.voiceDictation = function () {
            return {
                recognition: null,
                activeKey: null,
                supported: true,
                init() {
                    const SR = window.SpeechRecognition || window.webkitSpeechRecognition;
                    if (!SR) { this.supported = false; return; }
                    this.recognition = new SR();
                    this.recognition.lang = 'es-MX';
                    this.recognition.continuous = true;
                    this.recognition.interimResults = false;
                    this.recognition.onresult = (event) => {
                        if (!this.activeKey) return;
                        let transcript = '';
                        for (let i = event.resultIndex; i < event.results.length; i++) {
                            if (event.results[i].isFinal) transcript += event.results[i][0].transcript;
                        }
                        if (!transcript) return;
                        const key = this.activeKey;
                        const current = this.$wire.get(key) || '';
                        const separator = current && !current.endsWith(' ') ? ' ' : '';
                        this.$wire.set(key, (current + separator + transcript.trim()).trim(), false);
                    };
                    this.recognition.onend = () => {
                        if (this.activeKey) {
                            // Was stopped externally — restart to keep continuous until user stops
                            // But only if it wasn't a manual stop
                            this.activeKey = null;
                        }
                    };
                    this.recognition.onerror = (e) => {
                        console.warn('Dictation error:', e.error);
                        this.activeKey = null;
                    };
                },
                toggle(key) {
                    if (!this.supported) {
                        alert('Tu navegador no soporta dictado por voz. Usa Chrome o Edge.');
                        return;
                    }
                    if (this.activeKey === key) {
                        this.stop();
                        return;
                    }
                    if (this.activeKey) this.stop();
                    this.activeKey = key;
                    try {
                        this.recognition.start();
                    } catch (e) {
                        // Already started; restart
                        this.recognition.stop();
                        setTimeout(() => this.recognition.start(), 100);
                    }
                },
                stop() {
                    if (this.recognition) this.recognition.stop();
                    this.activeKey = null;
                },
            };
        };

        window.liveConsultation = function () {
            return {
                listening: false,
                processing: false,
                transcript: '',
                recognition: null,
                init() {
                    const SR = window.SpeechRecognition || window.webkitSpeechRecognition;
                    if (!SR) return;
                    this.recognition = new SR();
                    this.recognition.lang = 'es-MX';
                    this.recognition.continuous = true;
                    this.recognition.interimResults = true;
                    this.recognition.onresult = (event) => {
                        let finalText = '';
                        let interim = '';
                        for (let i = event.resultIndex; i < event.results.length; i++) {
                            const t = event.results[i][0].transcript;
                            if (event.results[i].isFinal) finalText += t + ' ';
                            else interim += t;
                        }
                        if (finalText) this.transcript += finalText;
                        // Show interim in display but only save final
                    };
                    this.recognition.onerror = (e) => {
                        console.warn('Live consult error:', e.error);
                    };
                    this.recognition.onend = () => {
                        if (this.listening) {
                            // Auto-restart for continuous listening
                            try { this.recognition.start(); } catch (e) {}
                        }
                    };
                },
                async toggle() {
                    if (!this.recognition) {
                        alert('Tu navegador no soporta transcripción por voz. Usa Chrome o Edge.');
                        return;
                    }
                    if (this.listening) {
                        // Stop and process
                        this.listening = false;
                        try { this.recognition.stop(); } catch (e) {}
                        if (!this.transcript.trim()) {
                            alert('No se grabó audio. Intenta de nuevo.');
                            return;
                        }
                        this.processing = true;
                        await this.$wire.set('fullDictation', this.transcript, false);
                        await this.$wire.call('processFullDictation');
                        this.processing = false;
                        this.transcript = '';
                    } else {
                        this.transcript = '';
                        this.listening = true;
                        try { this.recognition.start(); } catch (e) {
                            console.warn(e);
                        }
                    }
                },
            };
        };
    </script>

    <div x-data="voiceDictation()">

    @if($appointment)
    <style>
        .cons-hero { position: relative; border-radius: 1.5rem; padding: 1.5rem 1.75rem; overflow: hidden; background: linear-gradient(135deg, #0d9488 0%, #0891b2 50%, #7c3aed 100%); color: white; box-shadow: 0 20px 60px -15px rgba(13,148,136,0.5), inset 0 1px 0 rgba(255,255,255,0.2); margin-bottom: 1.25rem; }
        .cons-hero::before { content: ''; position: absolute; top: -80px; right: -60px; width: 260px; height: 260px; background: radial-gradient(circle, rgba(255,255,255,0.18), transparent 70%); border-radius: 50%; pointer-events: none; }
        .cons-hero::after { content: ''; position: absolute; bottom: -80px; left: -40px; width: 200px; height: 200px; background: radial-gradient(circle, rgba(139,92,246,0.3), transparent 70%); border-radius: 50%; pointer-events: none; }
        .cons-hero-grain { position: absolute; inset: 0; background-image: radial-gradient(circle at 1px 1px, rgba(255,255,255,0.08) 1px, transparent 0); background-size: 20px 20px; pointer-events: none; }
        .cons-hero-body { position: relative; z-index: 1; display: flex; flex-direction: column; gap: 14px; }
        @media (min-width: 768px) { .cons-hero-body { flex-direction: row; align-items: center; justify-content: space-between; gap: 20px; } }
        .cons-hero-left { display: flex; align-items: center; gap: 14px; min-width: 0; }
        .cons-avatar { width: 60px; height: 60px; border-radius: 18px; background: rgba(255,255,255,0.2); backdrop-filter: blur(12px); border: 1.5px solid rgba(255,255,255,0.3); display: flex; align-items: center; justify-content: center; flex-shrink: 0; box-shadow: 0 8px 24px rgba(0,0,0,0.2); }
        .cons-avatar span { font-size: 1.3rem; font-weight: 800; letter-spacing: -0.02em; color: white; }
        .cons-label { font-size: 0.62rem; text-transform: uppercase; letter-spacing: 0.12em; opacity: 0.8; font-weight: 700; }
        .cons-name { font-size: 1.3rem; font-weight: 800; letter-spacing: -0.015em; line-height: 1.2; margin-top: 2px; color: white !important; -webkit-text-fill-color: white !important; background: none !important; }
        .cons-meta { display: flex; flex-wrap: wrap; gap: 6px 10px; font-size: 0.72rem; margin-top: 6px; }
        .cons-chip { display: inline-flex; align-items: center; gap: 4px; padding: 3px 9px; background: rgba(255,255,255,0.18); border: 1px solid rgba(255,255,255,0.25); border-radius: 999px; backdrop-filter: blur(8px); font-weight: 600; }
        .cons-chip-alert { background: rgba(239,68,68,0.35); border-color: rgba(239,68,68,0.5); }
        .cons-right { display: flex; align-items: center; gap: 8px; padding-left: 72px; }
        @media (min-width: 768px) { .cons-right { padding-left: 0; text-align: right; flex-direction: column; align-items: flex-end; gap: 6px; } }
        .cons-time { font-size: 0.72rem; opacity: 0.85; }
        .cons-service { font-size: 0.82rem; font-weight: 700; }
        .cons-history-btn { display: inline-flex; align-items: center; gap: 6px; padding: 9px 14px; background: rgba(255,255,255,0.18); backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.28); border-radius: 10px; color: white; font-size: 0.72rem; font-weight: 700; cursor: pointer; transition: all 0.2s; }
        .cons-history-btn:hover { background: rgba(255,255,255,0.28); transform: translateY(-1px); }
        .cons-history-count { background: #fbbf24; color: #78350f; padding: 1px 7px; border-radius: 999px; font-size: 0.65rem; font-weight: 800; }
    </style>

    {{-- Patient Hero Header --}}
    <div class="cons-hero">
        <div class="cons-hero-grain"></div>
        <div class="cons-hero-body">
            <div class="cons-hero-left">
                <div class="cons-avatar">
                    <span>{{ substr($appointment->patient->first_name, 0, 1) }}{{ substr($appointment->patient->last_name, 0, 1) }}</span>
                </div>
                <div style="min-width:0;flex:1;">
                    <div class="cons-label">🩺 En consulta</div>
                    <h2 class="cons-name">{{ $appointment->patient->first_name }} {{ $appointment->patient->last_name }}</h2>
                    <div class="cons-meta">
                        @if($appointment->patient->birth_date)
                        <span class="cons-chip">🎂 {{ $appointment->patient->birth_date->age }} años</span>
                        @endif
                        @if($appointment->patient->phone)
                        <span class="cons-chip">📞 {{ $appointment->patient->phone }}</span>
                        @endif
                        @if($appointment->patient->blood_type)
                        <span class="cons-chip" style="background:rgba(220,38,38,0.4);border-color:rgba(220,38,38,0.5);">🩸 {{ $appointment->patient->blood_type }}</span>
                        @endif
                        @if($appointment->patient->allergies)
                        <span class="cons-chip cons-chip-alert">⚠️ {{ Str::limit($appointment->patient->allergies, 40) }}</span>
                        @endif
                    </div>
                </div>
            </div>

            <div class="cons-right">
                <div>
                    <div class="cons-time">{{ $appointment->starts_at->translatedFormat('l d M, H:i') }}</div>
                    @if($appointment->service)
                    <div class="cons-service">{{ $appointment->service->name }}</div>
                    @endif
                </div>
                <button wire:click="toggleHistory" type="button" class="cons-history-btn">
                    <svg style="width:14px;height:14px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Historial
                    @if(count($this->patientHistory) > 0)
                    <span class="cons-history-count">{{ count($this->patientHistory) }}</span>
                    @endif
                </button>
            </div>
        </div>
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
    $stepConfig = [
        1 => ['label' => 'Signos vitales', 'short' => 'Vitales', 'icon' => '❤️', 'color' => '#ef4444', 'colorDark' => '#dc2626'],
        2 => ['label' => 'Diagnóstico', 'short' => 'Dx', 'icon' => '🔬', 'color' => '#0d9488', 'colorDark' => '#0f766e'],
        3 => ['label' => 'Receta', 'short' => 'Rx', 'icon' => '💊', 'color' => '#8b5cf6', 'colorDark' => '#7c3aed'],
        4 => ['label' => 'Cobro', 'short' => 'Cobro', 'icon' => '💰', 'color' => '#f59e0b', 'colorDark' => '#d97706'],
        5 => ['label' => 'Siguiente cita', 'short' => 'Cita', 'icon' => '📅', 'color' => '#3b82f6', 'colorDark' => '#2563eb'],
    ];
    @endphp
    <style>
        .v2-steps-wrap { margin-bottom: 1.5rem; overflow-x: auto; -webkit-overflow-scrolling: touch; scrollbar-width: none; padding: 4px 2px; }
        .v2-steps-wrap::-webkit-scrollbar { display: none; }
        .v2-steps-container { display: flex; align-items: center; justify-content: flex-start; gap: 8px; min-width: max-content; }
        @media (min-width: 768px) { .v2-steps-container { justify-content: center; min-width: 0; } }
        .v2-step { display: flex; align-items: center; gap: 8px; padding: 11px 16px; border-radius: 14px; font-size: 0.78rem; font-weight: 700; border: none; cursor: pointer; white-space: nowrap; flex-shrink: 0; transition: all 0.25s cubic-bezier(0.4,0,0.2,1); }
        .v2-step-active { color: white; transform: scale(1.05); }
        .v2-step-done { background: rgba(13, 148, 136, 0.1); border: 1px solid rgba(13, 148, 136, 0.3); color: #0f766e; }
        .v2-step-pending { background: rgba(243, 244, 246, 0.9); border: 1px solid rgba(229, 231, 235, 1); color: #9ca3af; }
        .v2-step-circle { width: 22px; height: 22px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 0.65rem; font-weight: 800; flex-shrink: 0; }
        .v2-step-icon { font-size: 1rem; }
        .v2-step-divider { width: 18px; height: 2px; flex-shrink: 0; border-radius: 2px; }
        @media (min-width: 768px) { .v2-step-divider { width: 28px; } }
    </style>

    <div class="v2-steps-wrap">
        <div class="v2-steps-container">
            @foreach($stepConfig as $num => $cfg)
            @php
                $isActive = $currentStep === $num;
                $isDone = $currentStep > $num;
            @endphp
            <button wire:click="goToStep({{ $num }})" type="button"
                class="v2-step {{ $isActive ? 'v2-step-active' : ($isDone ? 'v2-step-done' : 'v2-step-pending') }}"
                @if($isActive)
                    style="background: linear-gradient(135deg, {{ $cfg['color'] }}, {{ $cfg['colorDark'] }}); box-shadow: 0 8px 20px -4px {{ $cfg['color'] }}66, inset 0 1px 0 rgba(255,255,255,0.2);"
                @endif>
                <span class="v2-step-circle"
                    style="{{ $isActive ? 'background: rgba(255,255,255,0.3); color: white; border: 1.5px solid rgba(255,255,255,0.5);' : ($isDone ? 'background: #0d9488; color: white;' : 'background: #d1d5db; color: white;') }}">
                    @if($isDone)
                        <svg style="width:12px;height:12px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3.5" d="M5 13l4 4L19 7"/></svg>
                    @else
                        {{ $num }}
                    @endif
                </span>
                <span class="v2-step-icon">{{ $cfg['icon'] }}</span>
                <span class="hidden sm:inline">{{ $cfg['label'] }}</span>
                <span class="sm:hidden">{{ $cfg['short'] }}</span>
            </button>
            @if($num < 5)
            <div class="v2-step-divider" style="background: {{ $isDone ? '#14b8a6' : '#e5e7eb' }};"></div>
            @endif
            @endforeach
        </div>
    </div>

    {{-- Step content --}}
    <style>
        .step-card { background: white; border-radius: 1.25rem; padding: 1.5rem; box-shadow: 0 4px 16px rgba(0,0,0,0.04), 0 1px 2px rgba(13,148,136,0.05); border: 1px solid rgba(229, 231, 235, 0.8); position: relative; overflow: hidden; }
        .dark .step-card { background: rgba(15, 23, 42, 0.6); border-color: rgba(94, 234, 212, 0.15); }
        .step-card::before { content: ''; position: absolute; top: 0; left: 0; right: 0; height: 3px; background: linear-gradient(90deg, var(--step-accent, #0d9488), var(--step-accent-2, #0891b2)); }
        .step-title { display: flex; align-items: center; gap: 10px; margin-bottom: 6px; }
        .step-title-icon { font-size: 1.6rem; }
        .step-title-text { font-size: 1.25rem; font-weight: 800; color: #111; letter-spacing: -0.015em; }
        .dark .step-title-text { color: #f3f4f6; }
        .step-subtitle { font-size: 0.82rem; color: #6b7280; margin-bottom: 18px; }
    </style>

    <div class="step-card" style="--step-accent: {{ $stepConfig[$currentStep]['color'] ?? '#0d9488' }}; --step-accent-2: {{ $stepConfig[$currentStep]['colorDark'] ?? '#0891b2' }};">

        {{-- Step 1: Vital Signs --}}
        @if($currentStep === 1)
        <div class="step-title">
            <span class="step-title-icon">❤️</span>
            <span class="step-title-text">Signos Vitales</span>
        </div>
        <p class="step-subtitle">Opcional. Registra los signos vitales del paciente.</p>
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
        <div class="step-title">
            <span class="step-title-icon">🔬</span>
            <span class="step-title-text">Diagnóstico y Tratamiento</span>
        </div>
        <p class="step-subtitle">Usa la IA para llenar todo automáticamente o escribe manual.</p>

        {{-- LIVE CONSULTATION MODE --}}
        <div x-data="liveConsultation()" style="background:linear-gradient(135deg,#0f172a 0%,#1e293b 50%,#4c1d95 100%);border-radius:16px;padding:18px 20px;margin-bottom:16px;color:white;position:relative;overflow:hidden;box-shadow:0 12px 32px -8px rgba(15,23,42,0.5);">
            <div style="position:absolute;top:-60px;right:-60px;width:200px;height:200px;background:radial-gradient(circle,rgba(239,68,68,0.25),transparent 70%);border-radius:50%;pointer-events:none;"></div>
            <div style="position:absolute;bottom:-80px;left:-40px;width:180px;height:180px;background:radial-gradient(circle,rgba(139,92,246,0.3),transparent 70%);border-radius:50%;pointer-events:none;"></div>
            <div style="position:relative;z-index:1;">
                <div style="display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;">
                    <div style="display:flex;align-items:center;gap:12px;">
                        <div :class="listening ? 'live-pulse' : ''" style="width:44px;height:44px;background:linear-gradient(135deg,#ef4444,#dc2626);border-radius:12px;display:flex;align-items:center;justify-content:center;flex-shrink:0;box-shadow:0 8px 20px rgba(239,68,68,0.4);">
                            <svg style="width:22px;height:22px;color:white;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-14 0m7 7v4m-4 0h8M12 3a3 3 0 00-3 3v5a3 3 0 006 0V6a3 3 0 00-3-3z"/></svg>
                        </div>
                        <div>
                            <div style="font-size:10px;text-transform:uppercase;letter-spacing:0.12em;opacity:0.7;font-weight:700;">⭐ Feature exclusivo</div>
                            <div style="font-size:15px;font-weight:800;letter-spacing:-0.01em;margin-top:2px;">Modo Consulta en Vivo</div>
                            <div style="font-size:11px;opacity:0.8;margin-top:2px;">La IA escucha toda la consulta y llena todo automáticamente</div>
                        </div>
                    </div>
                    <button type="button" @click="toggle" x-text="listening ? '⏹ Detener y procesar' : '▶ Iniciar escucha'"
                        :style="listening ? 'background:linear-gradient(135deg,#dc2626,#991b1b);box-shadow:0 8px 20px rgba(220,38,38,0.4);' : 'background:linear-gradient(135deg,#10b981,#059669);box-shadow:0 8px 20px rgba(16,185,129,0.4);'"
                        style="padding:12px 20px;color:white;border:none;border-radius:12px;font-weight:800;font-size:13px;cursor:pointer;white-space:nowrap;transition:transform 0.2s;"
                        onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='translateY(0)'"></button>
                </div>

                <div x-show="listening" x-cloak style="margin-top:14px;padding:12px 14px;background:rgba(255,255,255,0.08);border:1px solid rgba(255,255,255,0.12);border-radius:10px;max-height:120px;overflow-y:auto;">
                    <div style="font-size:10px;text-transform:uppercase;letter-spacing:0.08em;opacity:0.6;margin-bottom:6px;font-weight:700;">🎤 Transcribiendo...</div>
                    <div x-text="transcript || 'Habla normal con el paciente. Escucharé todo.'" style="font-size:12px;line-height:1.6;"></div>
                </div>

                <div x-show="processing" x-cloak style="margin-top:14px;padding:12px 14px;background:rgba(13,148,136,0.25);border:1px solid rgba(13,148,136,0.4);border-radius:10px;display:flex;align-items:center;gap:10px;">
                    <div style="width:10px;height:10px;background:#5eead4;border-radius:50%;animation:pulse 1s infinite;"></div>
                    <span style="font-size:12px;font-weight:600;">✨ La IA está estructurando tu consulta...</span>
                </div>
            </div>

            <input type="hidden" wire:model="fullDictation" x-ref="dictationField">
        </div>
        <style>
            .live-pulse { animation: livePulse 1.2s infinite; }
            @keyframes livePulse { 0%,100% { box-shadow: 0 0 0 0 rgba(239,68,68,0.7); } 50% { box-shadow: 0 0 0 12px rgba(239,68,68,0); } }
        </style>

        {{-- AI Smart Dictation --}}
        <div style="background:linear-gradient(135deg,#ecfeff 0%,#f0fdfa 50%,#faf5ff 100%);border:1.5px solid #5eead4;border-radius:16px;padding:16px 18px;margin-bottom:16px;position:relative;overflow:hidden;">
            <div style="position:absolute;top:-40px;right:-40px;width:140px;height:140px;background:radial-gradient(circle,rgba(13,148,136,0.15),transparent 70%);border-radius:50%;pointer-events:none;"></div>
            <div style="position:relative;z-index:1;">
                <div style="display:flex;align-items:center;gap:10px;margin-bottom:12px;">
                    <div style="width:36px;height:36px;background:linear-gradient(135deg,#0d9488,#0891b2);border-radius:10px;display:flex;align-items:center;justify-content:center;flex-shrink:0;box-shadow:0 6px 16px rgba(13,148,136,0.35);">
                        <svg style="width:20px;height:20px;color:white;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg>
                    </div>
                    <div style="flex:1;min-width:0;">
                        <div style="font-size:10px;color:#0d9488;text-transform:uppercase;letter-spacing:0.1em;font-weight:800;">✨ Con IA</div>
                        <div style="font-size:14px;color:#0f172a;font-weight:800;letter-spacing:-0.01em;">Dictado inteligente</div>
                        <div style="font-size:11px;color:#64748b;margin-top:1px;">Escribe o dicta lo que pasó en la consulta y la IA llena todo</div>
                    </div>
                </div>
            <div class="field-with-mic">
                <textarea wire:model="fullDictation" rows="3" class="field-main w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-sm" placeholder="Ej: Paciente con dolor en molar superior derecho, veo caries profunda, voy a aplicar resina, recetar ibuprofeno 400mg cada 8 horas por 5 días..."></textarea>
                <button type="button" class="mic-btn" :class="{ recording: activeKey === 'fullDictation' }" @click="toggle('fullDictation')" title="Dictar por voz">
                    <svg x-show="activeKey !== 'fullDictation'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-14 0m7 7v4m-4 0h8M12 3a3 3 0 00-3 3v5a3 3 0 006 0V6a3 3 0 00-3-3z"/></svg>
                    <svg x-show="activeKey === 'fullDictation'" x-cloak fill="currentColor" viewBox="0 0 24 24"><rect x="6" y="6" width="12" height="12" rx="2"/></svg>
                </button>
            </div>
                <button wire:click="processFullDictation" wire:loading.attr="disabled" wire:target="processFullDictation"
                    style="margin-top:12px;width:100%;padding:12px 16px;background:linear-gradient(135deg,#0d9488,#0891b2);color:white;border:none;border-radius:12px;font-weight:800;font-size:13px;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:8px;box-shadow:0 8px 20px rgba(13,148,136,0.35);transition:transform 0.2s;"
                    onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='translateY(0)'">
                    <svg wire:loading.remove wire:target="processFullDictation" style="width:16px;height:16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    <svg wire:loading wire:target="processFullDictation" style="width:16px;height:16px;animation:spin 1s linear infinite;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                    <span wire:loading.remove wire:target="processFullDictation">✨ Procesar con IA</span>
                    <span wire:loading wire:target="processFullDictation">Analizando...</span>
                </button>
            </div>
        </div>

        <div class="space-y-3 md:space-y-4">
            <div>
                <label class="block text-xs md:text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Motivo de consulta</label>
                <div class="field-with-mic">
                    <textarea wire:model.live.debounce.1500ms="chief_complaint" wire:change="fetchDxSuggestions" rows="2" class="field-main w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-sm" placeholder="¿Por qué viene el paciente?"></textarea>
                    <button type="button" class="mic-btn" :class="{ recording: activeKey === 'chief_complaint' }" @click="toggle('chief_complaint')" title="Dictar por voz">
                        <svg x-show="activeKey !== 'chief_complaint'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-14 0m7 7v4m-4 0h8M12 3a3 3 0 00-3 3v5a3 3 0 006 0V6a3 3 0 00-3-3z"/></svg>
                        <svg x-show="activeKey === 'chief_complaint'" x-cloak fill="currentColor" viewBox="0 0 24 24"><rect x="6" y="6" width="12" height="12" rx="2"/></svg>
                    </button>
                </div>

                {{-- AI Diagnosis Suggestions --}}
                <div wire:loading wire:target="fetchDxSuggestions" style="margin-top:10px;display:flex;align-items:center;gap:8px;padding:10px 14px;background:#f0fdfa;border:1px dashed #5eead4;border-radius:10px;font-size:12px;color:#0f766e;">
                    <div style="width:8px;height:8px;background:#0d9488;border-radius:50%;animation:pulse 1s infinite;"></div>
                    <span>IA analizando el motivo...</span>
                </div>

                @if(!empty($dxSuggestions))
                <div wire:loading.remove wire:target="fetchDxSuggestions" style="margin-top:10px;background:linear-gradient(135deg,#ecfeff 0%,#f0fdfa 100%);border:1.5px solid #5eead4;border-radius:12px;padding:12px 14px;">
                    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:10px;">
                        <div style="display:flex;align-items:center;gap:6px;">
                            <svg style="width:14px;height:14px;color:#0d9488;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                            <span style="font-size:11px;color:#0f766e;font-weight:700;text-transform:uppercase;letter-spacing:0.5px;">Sugerencias IA</span>
                        </div>
                        <button wire:click="dismissSuggestions" style="background:none;border:none;color:#64748b;cursor:pointer;font-size:11px;padding:2px 6px;">✕</button>
                    </div>
                    <div style="display:flex;flex-direction:column;gap:6px;">
                        @foreach($dxSuggestions as $i => $sug)
                        <button type="button" wire:click="applySuggestion({{ $i }})" style="text-align:left;background:white;border:1px solid #d1fae5;border-radius:8px;padding:10px 12px;cursor:pointer;transition:all 0.15s;display:flex;flex-direction:column;gap:3px;" onmouseover="this.style.borderColor='#0d9488';this.style.boxShadow='0 2px 8px rgba(13,148,136,0.15)';" onmouseout="this.style.borderColor='#d1fae5';this.style.boxShadow='none';">
                            <div style="display:flex;align-items:center;gap:6px;">
                                <span style="background:#0d9488;color:white;width:18px;height:18px;border-radius:50%;display:inline-flex;align-items:center;justify-content:center;font-size:10px;font-weight:700;flex-shrink:0;">{{ $i + 1 }}</span>
                                <span style="font-weight:700;color:#111;font-size:13px;">{{ $sug['diagnosis'] }}</span>
                            </div>
                            <div style="font-size:11px;color:#4b5563;margin-left:24px;">
                                <strong style="color:#0f766e;">Tx:</strong> {{ $sug['treatment'] }}
                                @if(!empty($sug['medication']['medication']))
                                <br>
                                <strong style="color:#0f766e;">Rx:</strong> {{ $sug['medication']['medication'] }} {{ $sug['medication']['dosage'] }}
                                @endif
                            </div>
                        </button>
                        @endforeach
                    </div>
                    <div style="font-size:10px;color:#64748b;margin-top:8px;text-align:center;">Haz click en una sugerencia para aplicarla</div>
                </div>
                @endif
            </div>
            <div>
                <label class="block text-xs md:text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Diagnóstico</label>
                <div class="field-with-mic">
                    <textarea wire:model="diagnosis" rows="3" class="field-main w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-sm" placeholder="Diagnóstico clínico..."></textarea>
                    <button type="button" class="mic-btn" :class="{ recording: activeKey === 'diagnosis' }" @click="toggle('diagnosis')" title="Dictar por voz">
                        <svg x-show="activeKey !== 'diagnosis'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-14 0m7 7v4m-4 0h8M12 3a3 3 0 00-3 3v5a3 3 0 006 0V6a3 3 0 00-3-3z"/></svg>
                        <svg x-show="activeKey === 'diagnosis'" x-cloak fill="currentColor" viewBox="0 0 24 24"><rect x="6" y="6" width="12" height="12" rx="2"/></svg>
                    </button>
                </div>
            </div>
            <div>
                <label class="block text-xs md:text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Tratamiento realizado</label>
                <div class="field-with-mic">
                    <textarea wire:model="treatment" rows="3" class="field-main w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-sm" placeholder="Tratamiento aplicado hoy..."></textarea>
                    <button type="button" class="mic-btn" :class="{ recording: activeKey === 'treatment' }" @click="toggle('treatment')" title="Dictar por voz">
                        <svg x-show="activeKey !== 'treatment'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-14 0m7 7v4m-4 0h8M12 3a3 3 0 00-3 3v5a3 3 0 006 0V6a3 3 0 00-3-3z"/></svg>
                        <svg x-show="activeKey === 'treatment'" x-cloak fill="currentColor" viewBox="0 0 24 24"><rect x="6" y="6" width="12" height="12" rx="2"/></svg>
                    </button>
                </div>
            </div>
            <div>
                <label class="block text-xs md:text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Notas adicionales</label>
                <div class="field-with-mic">
                    <textarea wire:model="medical_notes" rows="2" class="field-main w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-sm" placeholder="Observaciones..."></textarea>
                    <button type="button" class="mic-btn" :class="{ recording: activeKey === 'medical_notes' }" @click="toggle('medical_notes')" title="Dictar por voz">
                        <svg x-show="activeKey !== 'medical_notes'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-14 0m7 7v4m-4 0h8M12 3a3 3 0 00-3 3v5a3 3 0 006 0V6a3 3 0 00-3-3z"/></svg>
                        <svg x-show="activeKey === 'medical_notes'" x-cloak fill="currentColor" viewBox="0 0 24 24"><rect x="6" y="6" width="12" height="12" rx="2"/></svg>
                    </button>
                </div>
            </div>
        </div>
        @endif

        {{-- Step 3: Prescription --}}
        @if($currentStep === 3)
        <div class="step-title">
            <span class="step-title-icon">💊</span>
            <span class="step-title-text">Receta Médica</span>
        </div>
        <p class="step-subtitle">Opcional. Agrega medicamentos si es necesario.</p>
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
            <div class="field-with-mic">
                <textarea wire:model="prescription_notes" rows="2" class="field-main w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-sm" placeholder="Indicaciones generales..."></textarea>
                <button type="button" class="mic-btn" :class="{ recording: activeKey === 'prescription_notes' }" @click="toggle('prescription_notes')" title="Dictar por voz">
                    <svg x-show="activeKey !== 'prescription_notes'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-14 0m7 7v4m-4 0h8M12 3a3 3 0 00-3 3v5a3 3 0 006 0V6a3 3 0 00-3-3z"/></svg>
                    <svg x-show="activeKey === 'prescription_notes'" x-cloak fill="currentColor" viewBox="0 0 24 24"><rect x="6" y="6" width="12" height="12" rx="2"/></svg>
                </button>
            </div>
        </div>
        @endif

        {{-- Step 4: Payment --}}
        @if($currentStep === 4)
        <div class="step-title">
            <span class="step-title-icon">💰</span>
            <span class="step-title-text">Cobro</span>
        </div>
        <p class="step-subtitle">Registra el pago de esta consulta.</p>
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
        @if($appointment->patient->phone && $payment_amount > 0)
        @php
            $serviceName = collect($this->services)->get($payment_service_id) ?? 'consulta';
            $clinicName = $appointment->clinic->name ?? '';
            $waPhone = preg_replace('/\D/', '', $appointment->patient->phone);
            $waMsg = "Hola {$appointment->patient->first_name}, te comparto el cobro de tu consulta de hoy en {$clinicName}:\n\n"
                   . "💰 *Total: \${" . number_format($payment_amount, 2) . "}*\n"
                   . "📋 Concepto: {$serviceName}\n\n"
                   . "Puedes pagar por transferencia. ¡Gracias!";
        @endphp
        <div style="margin-top:1rem;padding:12px 14px;background:#f0fdf4;border:1px solid #bbf7d0;border-radius:10px;display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;">
            <div style="font-size:12px;color:#166534;">
                <strong>💬 Envía el cobro por WhatsApp</strong><br>
                <span style="font-size:11px;color:#15803d;">Mensaje pre-armado con el monto y concepto</span>
            </div>
            <a href="https://wa.me/52{{ $waPhone }}?text={{ urlencode($waMsg) }}" target="_blank"
                style="display:inline-flex;align-items:center;gap:6px;padding:10px 16px;background:#22c55e;color:white;border-radius:10px;font-weight:700;font-size:13px;text-decoration:none;white-space:nowrap;">
                <svg style="width:16px;height:16px;" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"/></svg>
                Cobrar por WhatsApp
            </a>
        </div>
        @endif
        @endif

        {{-- Step 5: Next appointment --}}
        @if($currentStep === 5)
        <div class="step-title">
            <span class="step-title-icon">📅</span>
            <span class="step-title-text">Siguiente Cita</span>
        </div>
        <p class="step-subtitle">Opcional. Agenda la próxima visita antes de que se vaya el paciente.</p>
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

    </div>{{-- /x-data voiceDictation --}}
</x-filament-panels::page>
