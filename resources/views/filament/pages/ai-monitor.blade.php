<x-filament-panels::page>
    @php $d = $this->data; @endphp

    <style>
        .ai-hero { position: relative; border-radius: 1.5rem; padding: 28px 32px; overflow: hidden; color: white; margin-bottom: 20px; }
        .ai-hero-on { background: linear-gradient(135deg, #10b981 0%, #059669 100%); box-shadow: 0 20px 60px -15px rgba(16,185,129,0.4); }
        .ai-hero-off { background: linear-gradient(135deg, #ef4444 0%, #b91c1c 100%); box-shadow: 0 20px 60px -15px rgba(239,68,68,0.4); }
        .ai-hero::before { content: ''; position: absolute; top: -80px; right: -60px; width: 280px; height: 280px; background: radial-gradient(circle, rgba(255,255,255,0.15), transparent 70%); border-radius: 50%; }
        .ai-hero-label { font-size: 11px; text-transform: uppercase; letter-spacing: 2px; opacity: 0.8; font-weight: 700; }
        .ai-hero-title { font-size: 1.75rem; font-weight: 800; letter-spacing: -0.02em; margin-top: 4px; color: white !important; -webkit-text-fill-color: white !important; background: none !important; }
        .ai-hero-sub { font-size: 0.9rem; opacity: 0.9; margin-top: 4px; }

        .ai-stats { display: grid; grid-template-columns: repeat(2, 1fr); gap: 12px; margin-top: 20px; }
        @media (min-width: 640px) { .ai-stats { grid-template-columns: repeat(4, 1fr); } }
        .ai-stat { background: rgba(255,255,255,0.15); backdrop-filter: blur(14px); border: 1px solid rgba(255,255,255,0.25); border-radius: 14px; padding: 14px 16px; }
        .ai-stat-label { font-size: 0.65rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.08em; opacity: 0.8; }
        .ai-stat-value { font-size: 1.6rem; font-weight: 800; letter-spacing: -0.02em; margin-top: 4px; line-height: 1; color: white; }

        .ai-grid { display: grid; grid-template-columns: 1fr; gap: 16px; }
        @media (min-width: 1024px) { .ai-grid { grid-template-columns: 1fr 1fr; } }
        .ai-card { background: white; border: 1px solid rgba(229,231,235,0.8); border-radius: 1.25rem; padding: 20px; }
        .dark .ai-card { background: rgba(15,23,42,0.6); border-color: rgba(94,234,212,0.15); }
        .ai-card-title { font-size: 0.95rem; font-weight: 800; color: #0f172a; letter-spacing: -0.01em; margin-bottom: 12px; }
        .dark .ai-card-title { color: #f0fdfa; }

        .ai-row { display: flex; align-items: center; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid rgba(229,231,235,0.6); font-size: 0.82rem; }
        .ai-row:last-child { border-bottom: none; }
        .ai-row-label { font-weight: 600; color: #374151; }
        .ai-row-value { color: #0d9488; font-weight: 700; }

        .ai-bar { height: 8px; background: #e5e7eb; border-radius: 999px; overflow: hidden; margin-top: 8px; }
        .ai-bar-fill { height: 100%; background: linear-gradient(90deg, #10b981, #f59e0b, #ef4444); border-radius: 999px; transition: width 0.5s; }

        .ai-code { background: #0f172a; color: #5eead4; padding: 10px 14px; border-radius: 8px; font-family: 'SF Mono', monospace; font-size: 12px; }
    </style>

    {{-- STATUS HERO --}}
    <div class="ai-hero {{ $d['ai_enabled'] ? 'ai-hero-on' : 'ai-hero-off' }}">
        <div style="position:relative;z-index:1;">
            <div class="ai-hero-label">{{ $d['ai_enabled'] ? '✅ IA ACTIVADA' : '🚫 IA DESACTIVADA' }}</div>
            <h2 class="ai-hero-title">{{ $d['ai_enabled'] ? 'Todas las features de IA están en vivo' : 'Las features de IA están apagadas' }}</h2>
            <p class="ai-hero-sub">Proveedor: {{ strtoupper($d['provider']) }} · Límite diario: ${{ number_format($d['daily_limit'], 2) }} USD</p>

            <div class="ai-stats">
                <div class="ai-stat">
                    <div class="ai-stat-label">💸 Hoy</div>
                    <div class="ai-stat-value">${{ number_format($d['today_cost'], 4) }}</div>
                </div>
                <div class="ai-stat">
                    <div class="ai-stat-label">📞 Llamadas hoy</div>
                    <div class="ai-stat-value">{{ number_format($d['today_calls']) }}</div>
                </div>
                <div class="ai-stat">
                    <div class="ai-stat-label">📅 Este mes</div>
                    <div class="ai-stat-value">${{ number_format($d['month_cost'], 4) }}</div>
                </div>
                <div class="ai-stat">
                    <div class="ai-stat-label">⚠️ Fallos hoy</div>
                    <div class="ai-stat-value">{{ $d['today_failures'] }}</div>
                </div>
            </div>

            @if($d['daily_limit'] > 0)
            <div style="margin-top:16px;">
                <div style="font-size:11px;opacity:0.85;display:flex;justify-content:space-between;margin-bottom:4px;">
                    <span>Uso del límite diario</span>
                    <span>{{ number_format(min(($d['today_cost'] / $d['daily_limit']) * 100, 100), 1) }}%</span>
                </div>
                <div class="ai-bar">
                    <div class="ai-bar-fill" style="width: {{ min(($d['today_cost'] / $d['daily_limit']) * 100, 100) }}%;"></div>
                </div>
            </div>
            @endif
        </div>
    </div>

    <div class="ai-grid">
        {{-- TOP FEATURES --}}
        <div class="ai-card">
            <div class="ai-card-title">🏆 Features más usadas este mes</div>
            @forelse($d['top_features'] as $f)
            <div class="ai-row">
                <span class="ai-row-label">{{ $f->feature }} · {{ $f->calls }} llamadas</span>
                <span class="ai-row-value">${{ number_format($f->cost, 4) }}</span>
            </div>
            @empty
            <div style="text-align:center;color:#9ca3af;padding:20px;font-size:13px;">Sin uso registrado este mes</div>
            @endforelse
        </div>

        {{-- TOP CLINICS --}}
        <div class="ai-card">
            <div class="ai-card-title">🏥 Clínicas que más consumen</div>
            @forelse($d['top_clinics'] as $c)
            <div class="ai-row">
                <span class="ai-row-label">{{ $c->clinic?->name ?? "Clínica #{$c->clinic_id}" }} · {{ $c->clinic?->plan ?? '-' }}</span>
                <span class="ai-row-value">${{ number_format($c->cost, 4) }}</span>
            </div>
            @empty
            <div style="text-align:center;color:#9ca3af;padding:20px;font-size:13px;">Sin uso por clínica</div>
            @endforelse
        </div>

        {{-- LAST 7 DAYS --}}
        <div class="ai-card" style="grid-column: 1 / -1;">
            <div class="ai-card-title">📊 Últimos 7 días</div>
            @if($d['last_7_days']->count())
            @foreach($d['last_7_days'] as $day)
            <div class="ai-row">
                <span class="ai-row-label">{{ \Carbon\Carbon::parse($day->date)->translatedFormat('l d M') }}</span>
                <div style="display:flex;gap:12px;align-items:center;">
                    <span style="color:#6b7280;font-size:0.78rem;">{{ $day->calls }} llamadas</span>
                    <span class="ai-row-value">${{ number_format($day->cost, 4) }}</span>
                </div>
            </div>
            @endforeach
            @else
            <div style="text-align:center;color:#9ca3af;padding:20px;font-size:13px;">Sin datos</div>
            @endif
        </div>

        {{-- HOW TO ENABLE --}}
        <div class="ai-card" style="grid-column: 1 / -1;">
            <div class="ai-card-title">⚙️ Cómo activar/desactivar la IA</div>
            <p style="font-size:13px;color:#4b5563;margin-bottom:10px;">Edita el archivo <code>.env</code> en producción:</p>
            <div class="ai-code">
                AI_ENABLED={{ $d['ai_enabled'] ? 'true' : 'false' }}<br>
                AI_MAX_DAILY_COST_USD={{ $d['daily_limit'] }}<br>
                DEEPSEEK_API_KEY=sk-...
            </div>
            <p style="font-size:12px;color:#6b7280;margin-top:10px;">Después ejecuta <code>php artisan config:clear</code>. Los cambios aplican inmediatamente sin reiniciar el servidor.</p>
        </div>
    </div>
</x-filament-panels::page>
