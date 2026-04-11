<x-filament-panels::page>
    <style>
        .pi-hero { background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); border-radius: 20px; padding: 28px 32px; color: white; position: relative; overflow: hidden; }
        .pi-hero::before { content: ''; position: absolute; top: -50%; right: -20%; width: 400px; height: 400px; background: radial-gradient(circle, rgba(13,148,136,0.3), transparent); border-radius: 50%; }
        .pi-hero-label { font-size: 11px; text-transform: uppercase; letter-spacing: 2px; opacity: 0.7; font-weight: 700; }
        .pi-hero-title { font-size: 28px; font-weight: 800; margin-top: 6px; }
        .pi-hero-summary { font-size: 14px; margin-top: 12px; line-height: 1.6; opacity: 0.95; max-width: 700px; position: relative; z-index: 1; }
        .pi-refresh { position: absolute; top: 20px; right: 24px; background: rgba(255,255,255,0.15); border: none; color: white; padding: 8px 14px; border-radius: 10px; font-size: 12px; font-weight: 600; cursor: pointer; display: inline-flex; align-items: center; gap: 6px; z-index: 2; }
        .pi-refresh:hover { background: rgba(255,255,255,0.25); }

        .pi-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 16px; margin-top: 20px; }
        .pi-card { background: white; border: 1px solid #e5e7eb; border-radius: 16px; padding: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.04); transition: all 0.2s; }
        .pi-card:hover { box-shadow: 0 8px 20px rgba(0,0,0,0.08); transform: translateY(-2px); }
        .pi-card-header { display: flex; align-items: center; gap: 10px; margin-bottom: 12px; }
        .pi-card-icon { width: 40px; height: 40px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 22px; flex-shrink: 0; }

        .pi-type-revenue .pi-card-icon { background: #d1fae5; }
        .pi-type-workload .pi-card-icon { background: #dbeafe; }
        .pi-type-retention .pi-card-icon { background: #fef3c7; }
        .pi-type-pricing .pi-card-icon { background: #ede9fe; }

        .pi-card-title { font-weight: 800; font-size: 15px; color: #111; }
        .pi-card-type { font-size: 10px; color: #6b7280; text-transform: uppercase; letter-spacing: 0.5px; font-weight: 600; }

        .pi-section { margin-top: 12px; }
        .pi-section-label { font-size: 10px; text-transform: uppercase; color: #6b7280; font-weight: 700; letter-spacing: 0.5px; margin-bottom: 4px; }
        .pi-prediction { font-size: 13px; color: #374151; line-height: 1.5; }
        .pi-action { font-size: 13px; color: #0f766e; line-height: 1.5; font-weight: 500; }

        .pi-impact { display: inline-block; margin-top: 12px; padding: 6px 12px; background: linear-gradient(135deg, #d1fae5, #ccfbf1); color: #064e3b; border-radius: 999px; font-size: 12px; font-weight: 700; }

        .pi-loading { text-align: center; padding: 60px 20px; color: #6b7280; }
        .pi-loading-spinner { width: 40px; height: 40px; border: 3px solid #e5e7eb; border-top-color: #0d9488; border-radius: 50%; animation: spin 1s linear infinite; margin: 0 auto 12px; }
        @keyframes spin { to { transform: rotate(360deg); } }
    </style>

    <div wire:loading.remove wire:target="refreshInsights">
        <div class="pi-hero">
            <button wire:click="refreshInsights" class="pi-refresh">
                <svg style="width:12px;height:12px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                Actualizar
            </button>
            <div class="pi-hero-label">✨ IA Predictiva</div>
            <div class="pi-hero-title">Inteligencia del consultorio</div>
            @if($insights && !empty($insights['summary']))
            <div class="pi-hero-summary">{{ $insights['summary'] }}</div>
            @endif
        </div>

        @if($insights && !empty($insights['predictions']))
        <div class="pi-grid">
            @foreach($insights['predictions'] as $p)
            <div class="pi-card pi-type-{{ $p['type'] ?? 'revenue' }}">
                <div class="pi-card-header">
                    <div class="pi-card-icon">{{ $p['icon'] }}</div>
                    <div style="flex:1;min-width:0;">
                        <div class="pi-card-title">{{ $p['title'] }}</div>
                        <div class="pi-card-type">{{ match($p['type'] ?? 'revenue') { 'revenue' => 'Ingresos', 'workload' => 'Agenda', 'retention' => 'Retención', 'pricing' => 'Precios', default => 'Negocio' } }}</div>
                    </div>
                </div>

                @if(!empty($p['prediction']))
                <div class="pi-section">
                    <div class="pi-section-label">📊 Predicción</div>
                    <div class="pi-prediction">{{ $p['prediction'] }}</div>
                </div>
                @endif

                @if(!empty($p['action']))
                <div class="pi-section">
                    <div class="pi-section-label">🎯 Qué hacer</div>
                    <div class="pi-action">{{ $p['action'] }}</div>
                </div>
                @endif

                @if(!empty($p['impact']))
                <div class="pi-impact">💰 {{ $p['impact'] }}</div>
                @endif
            </div>
            @endforeach
        </div>
        @else
        <div class="pi-loading">
            <div class="pi-loading-spinner"></div>
            <div>Analizando los datos de tu consultorio...</div>
            <div style="font-size:11px;margin-top:6px;opacity:0.7;">Esto tarda unos segundos la primera vez</div>
        </div>
        @endif
    </div>

    <div wire:loading wire:target="refreshInsights" class="pi-loading">
        <div class="pi-loading-spinner"></div>
        <div>Regenerando predicciones...</div>
    </div>
</x-filament-panels::page>
