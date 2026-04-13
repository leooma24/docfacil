<x-filament-widgets::widget>
    @php $alerts = $this->getAlerts(); @endphp

    <style>
        .aw-card {
            position: relative;
            border-radius: 1.25rem;
            padding: 22px 24px;
            overflow: hidden;
            background: linear-gradient(135deg, #f59e0b 0%, #f97316 40%, #ef4444 100%);
            color: white;
            box-shadow: 0 16px 40px -15px rgba(239, 68, 68, 0.4), inset 0 1px 0 rgba(255,255,255,0.2);
        }
        .aw-card::before {
            content: ''; position: absolute; top: -60px; right: -50px;
            width: 220px; height: 220px;
            background: radial-gradient(circle, rgba(255,255,255,0.18), transparent 70%);
            border-radius: 50%; pointer-events: none;
        }
        .aw-card::after {
            content: ''; position: absolute; bottom: -80px; left: -30px;
            width: 180px; height: 180px;
            background: radial-gradient(circle, rgba(255,255,255,0.12), transparent 70%);
            border-radius: 50%; pointer-events: none;
        }
        .aw-grain {
            position: absolute; inset: 0;
            background-image: radial-gradient(circle at 1px 1px, rgba(255,255,255,0.08) 1px, transparent 0);
            background-size: 20px 20px; pointer-events: none;
        }
        .aw-content { position: relative; z-index: 1; }
        .aw-head {
            display: flex; align-items: center; gap: 12px; margin-bottom: 18px;
        }
        .aw-head-icon {
            width: 44px; height: 44px; border-radius: 14px;
            background: rgba(255,255,255,0.2);
            backdrop-filter: blur(12px);
            border: 1.5px solid rgba(255,255,255,0.3);
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0; font-size: 22px;
            box-shadow: 0 6px 20px rgba(0,0,0,0.15);
        }
        .aw-head-label {
            font-size: 0.62rem; font-weight: 700;
            text-transform: uppercase; letter-spacing: 0.12em;
            opacity: 0.85;
        }
        .aw-head-title {
            font-size: 1.2rem; font-weight: 800; letter-spacing: -0.01em;
            line-height: 1.15; color: white !important;
        }

        .aw-list { display: flex; flex-direction: column; gap: 10px; }
        .aw-item {
            display: flex; align-items: flex-start; gap: 12px;
            padding: 14px 16px;
            background: rgba(255,255,255,0.14);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255,255,255,0.22);
            border-radius: 12px;
            transition: all 0.2s;
        }
        .aw-item:hover {
            background: rgba(255,255,255,0.22);
            transform: translateX(3px);
        }
        .aw-item-icon {
            width: 32px; height: 32px; border-radius: 10px;
            background: rgba(255,255,255,0.22);
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0;
        }
        .aw-item-title {
            font-size: 0.85rem; font-weight: 700; color: white;
        }
        .aw-item-desc {
            font-size: 0.74rem; opacity: 0.85; margin-top: 2px;
        }

        .aw-empty {
            display: flex; flex-direction: column; align-items: center;
            padding: 20px 16px; text-align: center;
            background: rgba(255,255,255,0.12);
            backdrop-filter: blur(10px);
            border: 1px dashed rgba(255,255,255,0.3);
            border-radius: 14px;
        }
        .aw-empty-emoji { font-size: 28px; margin-bottom: 4px; }
        .aw-empty-text { font-size: 0.8rem; opacity: 0.92; font-weight: 600; }
    </style>

    <div class="aw-card">
        <div class="aw-grain"></div>
        <div class="aw-content">
            <div class="aw-head">
                <div class="aw-head-icon">🔔</div>
                <div>
                    <div class="aw-head-label">Radar del consultorio</div>
                    <div class="aw-head-title">Alertas y avisos</div>
                </div>
            </div>

            @if(count($alerts) > 0)
            <div class="aw-list">
                @foreach($alerts as $alert)
                <div class="aw-item">
                    <div class="aw-item-icon">
                        <x-filament::icon :icon="$alert['icon']" class="w-4 h-4 text-white" />
                    </div>
                    <div style="min-width:0;flex:1;">
                        <div class="aw-item-title">{{ $alert['title'] }}</div>
                        <div class="aw-item-desc">{{ $alert['desc'] }}</div>
                    </div>
                </div>
                @endforeach
            </div>
            @else
            <div class="aw-empty">
                <div class="aw-empty-emoji">✅</div>
                <div class="aw-empty-text">Todo en orden. Nada que reportar ahora mismo.</div>
            </div>
            @endif
        </div>
    </div>
</x-filament-widgets::widget>
