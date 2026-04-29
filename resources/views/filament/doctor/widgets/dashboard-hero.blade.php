<x-filament-widgets::widget>
    @php $d = $this->getData(); @endphp

    <style>
        .dh-hero {
            position: relative;
            border-radius: 1.5rem;
            padding: 32px 36px;
            overflow: hidden;
            background: linear-gradient(135deg, #0d9488 0%, #0891b2 35%, #7c3aed 100%);
            color: white;
            box-shadow: 0 24px 60px -15px rgba(13, 148, 136, 0.45), inset 0 1px 0 rgba(255,255,255,0.2);
        }
        .dh-hero::before {
            content: ''; position: absolute; top: -80px; right: -60px;
            width: 300px; height: 300px;
            background: radial-gradient(circle, rgba(255,255,255,0.18), transparent 70%);
            border-radius: 50%; pointer-events: none;
        }
        .dh-hero::after {
            content: ''; position: absolute; bottom: -100px; left: -40px;
            width: 260px; height: 260px;
            background: radial-gradient(circle, rgba(124,58,237,0.35), transparent 70%);
            border-radius: 50%; pointer-events: none;
        }
        .dh-hero-grain {
            position: absolute; inset: 0;
            background-image: radial-gradient(circle at 1px 1px, rgba(255,255,255,0.08) 1px, transparent 0);
            background-size: 20px 20px; pointer-events: none;
        }
        .dh-hero-content { position: relative; z-index: 1; }

        .dh-hero-top { display: flex; align-items: center; gap: 18px; flex-wrap: wrap; }
        .dh-hero-icon {
            width: 64px; height: 64px; border-radius: 20px;
            background: rgba(255,255,255,0.18);
            backdrop-filter: blur(14px);
            border: 1.5px solid rgba(255,255,255,0.32);
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0; font-size: 32px;
            box-shadow: 0 8px 28px rgba(0,0,0,0.18);
        }
        .dh-hero-kicker {
            font-size: 0.68rem; font-weight: 700;
            text-transform: uppercase; letter-spacing: 0.14em;
            opacity: 0.82;
        }
        .dh-hero-title {
            font-size: 1.85rem; font-weight: 800; letter-spacing: -0.02em;
            line-height: 1.15; margin-top: 2px; color: white !important;
            -webkit-text-fill-color: white !important; background: none !important;
        }
        .dh-hero-subtitle {
            font-size: 0.9rem; opacity: 0.88; margin-top: 4px;
        }

        .dh-stats {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 14px;
            margin-top: 26px;
        }
        @media (min-width: 768px) { .dh-stats { grid-template-columns: repeat(4, 1fr); } }

        .dh-stat {
            background: rgba(255,255,255,0.15);
            backdrop-filter: blur(14px);
            border: 1px solid rgba(255,255,255,0.25);
            border-radius: 16px;
            padding: 16px 18px;
            transition: all 0.2s;
        }
        .dh-stat:hover {
            background: rgba(255,255,255,0.22);
            transform: translateY(-2px);
            border-color: rgba(255,255,255,0.4);
        }
        .dh-stat-label {
            font-size: 0.65rem; font-weight: 700;
            text-transform: uppercase; letter-spacing: 0.1em;
            opacity: 0.82;
        }
        .dh-stat-value {
            font-size: 2rem; font-weight: 800; letter-spacing: -0.02em;
            margin-top: 6px; line-height: 1; color: white;
        }
        .dh-stat-sub {
            font-size: 0.68rem; opacity: 0.72; margin-top: 4px;
        }
    </style>

    <style>
        /* Empty state — paso a paso para nuevos doctores */
        .dh-empty-steps { display: grid; gap: 12px; margin-top: 22px; }
        @media (min-width: 768px) { .dh-empty-steps { grid-template-columns: repeat(3, 1fr); } }
        .dh-empty-step {
            background: rgba(255,255,255,0.16);
            backdrop-filter: blur(14px);
            border: 1px solid rgba(255,255,255,0.28);
            border-radius: 16px;
            padding: 18px 20px;
            display: flex; flex-direction: column; gap: 10px;
            transition: all 0.2s;
        }
        .dh-empty-step:hover {
            background: rgba(255,255,255,0.24);
            transform: translateY(-2px);
            border-color: rgba(255,255,255,0.45);
        }
        .dh-empty-step.done {
            background: rgba(16,185,129,0.18);
            border-color: rgba(110,231,183,0.55);
        }
        .dh-empty-num {
            width: 32px; height: 32px; border-radius: 50%;
            background: rgba(255,255,255,0.28);
            display: flex; align-items: center; justify-content: center;
            font-weight: 800; font-size: 15px; color: white;
        }
        .dh-empty-step.done .dh-empty-num {
            background: #10b981; color: white;
        }
        .dh-empty-step-title { font-weight: 800; font-size: 0.95rem; color: white; line-height: 1.2; }
        .dh-empty-step-desc { font-size: 0.78rem; opacity: 0.82; line-height: 1.4; }
        .dh-empty-cta {
            display: inline-flex; align-items: center; gap: 6px;
            padding: 8px 14px; background: white; color: #0d9488;
            font-weight: 700; font-size: 0.78rem; border-radius: 10px;
            text-decoration: none; align-self: flex-start;
            transition: all 0.2s; margin-top: 4px;
        }
        .dh-empty-cta:hover { transform: translateY(-1px); box-shadow: 0 6px 14px rgba(0,0,0,0.18); }
        .dh-empty-step.done .dh-empty-cta { background: rgba(255,255,255,0.22); color: white; }
    </style>

    <div class="dh-hero">
        <div class="dh-hero-grain"></div>
        <div class="dh-hero-content">
            <div class="dh-hero-top">
                <div class="dh-hero-icon">{{ $d['empty_state'] === 'normal' ? '🩺' : '👋' }}</div>
                <div style="flex:1;min-width:0;">
                    <div class="dh-hero-kicker">{{ $d['date'] }}</div>
                    @if($d['empty_state'] === 'fresh')
                        <h2 class="dh-hero-title">Bienvenido, Dr. {{ explode(' ', trim($d['name']))[0] }}</h2>
                        <div class="dh-hero-subtitle">Tu consultorio está listo. Empieza por aquí — te toma 5 minutos y ya tendrás corriendo el sistema.</div>
                    @elseif($d['empty_state'] === 'has_patients')
                        <h2 class="dh-hero-title">Buen avance, Dr. {{ explode(' ', trim($d['name']))[0] }}</h2>
                        <div class="dh-hero-subtitle">Ya tienes {{ $d['total_patients'] }} {{ $d['total_patients'] === 1 ? 'paciente' : 'pacientes' }}. Falta agendar su primera cita y mandar su primer recordatorio.</div>
                    @else
                        <h2 class="dh-hero-title">{{ $d['greeting'] }}, Dr. {{ explode(' ', trim($d['name']))[0] }}</h2>
                        <div class="dh-hero-subtitle">Este es el resumen de tu consultorio de hoy. Que tengas un gran día.</div>
                    @endif
                </div>
            </div>

            @if($d['empty_state'] === 'normal')
                {{-- Estado normal: tarjetas con métricas del día --}}
                <div class="dh-stats">
                    <div class="dh-stat">
                        <div class="dh-stat-label">📅 Citas hoy</div>
                        <div class="dh-stat-value">{{ $d['today_appts'] }}</div>
                        <div class="dh-stat-sub">Agendadas para hoy</div>
                    </div>
                    <div class="dh-stat">
                        <div class="dh-stat-label">💰 Cobrado hoy</div>
                        <div class="dh-stat-value">${{ number_format($d['today_income']) }}</div>
                        <div class="dh-stat-sub">Pagos recibidos</div>
                    </div>
                    <div class="dh-stat">
                        <div class="dh-stat-label">⏳ Por cobrar</div>
                        <div class="dh-stat-value">${{ number_format($d['pending_payments']) }}</div>
                        <div class="dh-stat-sub">Pagos pendientes</div>
                    </div>
                    <div class="dh-stat">
                        <div class="dh-stat-label">🆕 Pacientes nuevos</div>
                        <div class="dh-stat-value">{{ $d['new_patients'] }}</div>
                        <div class="dh-stat-sub">Registrados hoy</div>
                    </div>
                </div>
            @else
                {{-- Empty state: 3 pasos guiados al primer uso --}}
                <div class="dh-empty-steps">
                    <div class="dh-empty-step {{ $d['total_patients'] > 0 ? 'done' : '' }}">
                        <div style="display:flex;align-items:center;gap:10px;">
                            <div class="dh-empty-num">{{ $d['total_patients'] > 0 ? '✓' : '1' }}</div>
                            <div class="dh-empty-step-title">Agrega tu primer paciente</div>
                        </div>
                        <div class="dh-empty-step-desc">
                            @if($d['total_patients'] > 0)
                                Listo · {{ $d['total_patients'] }} {{ $d['total_patients'] === 1 ? 'paciente registrado' : 'pacientes registrados' }}
                            @else
                                Solo nombre, teléfono y email. Lo demás lo llenas después.
                            @endif
                        </div>
                        <a href="{{ $d['patients_create_url'] }}" class="dh-empty-cta">
                            {{ $d['total_patients'] > 0 ? 'Agregar otro' : 'Crear paciente' }} →
                        </a>
                    </div>

                    <div class="dh-empty-step {{ $d['total_appointments'] > 0 ? 'done' : '' }}">
                        <div style="display:flex;align-items:center;gap:10px;">
                            <div class="dh-empty-num">{{ $d['total_appointments'] > 0 ? '✓' : '2' }}</div>
                            <div class="dh-empty-step-title">Crea tu primera cita</div>
                        </div>
                        <div class="dh-empty-step-desc">
                            @if($d['total_appointments'] > 0)
                                Listo · {{ $d['total_appointments'] }} {{ $d['total_appointments'] === 1 ? 'cita agendada' : 'citas agendadas' }}
                            @else
                                Selecciona paciente, fecha y servicio. La duración se calcula sola.
                            @endif
                        </div>
                        <a href="{{ $d['appointments_create_url'] }}" class="dh-empty-cta">
                            {{ $d['total_appointments'] > 0 ? 'Agregar otra' : 'Crear cita' }} →
                        </a>
                    </div>

                    <div class="dh-empty-step">
                        <div style="display:flex;align-items:center;gap:10px;">
                            <div class="dh-empty-num">3</div>
                            <div class="dh-empty-step-title">Manda tu primer recordatorio WhatsApp</div>
                        </div>
                        <div class="dh-empty-step-desc">
                            Desde la cita: 1 clic abre WhatsApp con el mensaje listo. Tú solo le das enviar.
                        </div>
                        <span class="dh-empty-cta" style="opacity:0.7;cursor:default;">
                            {{ $d['total_appointments'] > 0 ? 'Abre la cita ↗' : 'Disponible al crear cita' }}
                        </span>
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-filament-widgets::widget>
