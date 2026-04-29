<x-filament-widgets::widget>
    <div class="sc-card" x-data="{ open: true }">
        <style>
            .sc-card {
                border-radius: 1.25rem;
                background: linear-gradient(135deg, #ffffff 0%, #f0fdfa 100%);
                border: 2px solid #5eead4;
                padding: 24px 28px;
                box-shadow: 0 8px 24px -8px rgba(13, 148, 136, 0.18);
                position: relative;
                overflow: hidden;
            }
            .sc-card::before {
                content: '';
                position: absolute; top: -60px; right: -40px;
                width: 220px; height: 220px;
                background: radial-gradient(circle, rgba(13,148,136,0.12), transparent 70%);
                border-radius: 50%; pointer-events: none;
            }
            .sc-header {
                display: flex; align-items: center; justify-content: space-between;
                gap: 16px; flex-wrap: wrap;
                position: relative; z-index: 1;
            }
            .sc-header-left { flex: 1; min-width: 0; }
            .sc-kicker {
                font-size: 0.65rem; font-weight: 800;
                text-transform: uppercase; letter-spacing: 0.14em;
                color: #0d9488;
            }
            .sc-title {
                font-size: 1.25rem; font-weight: 800; color: #0f172a;
                margin-top: 2px; line-height: 1.2;
            }
            .sc-subtitle { font-size: 0.85rem; color: #475569; margin-top: 4px; }
            .sc-percent-block { display: flex; align-items: center; gap: 14px; flex-shrink: 0; }
            .sc-percent {
                font-size: 2.4rem; font-weight: 800; color: #0d9488;
                letter-spacing: -0.02em; line-height: 1;
            }
            .sc-progress-wrap {
                margin-top: 14px;
                background: #ccfbf1; border-radius: 999px;
                height: 8px; overflow: hidden;
                position: relative; z-index: 1;
            }
            .sc-progress-bar {
                height: 100%; border-radius: 999px;
                background: linear-gradient(90deg, #0d9488 0%, #06b6d4 100%);
                transition: width 0.4s ease;
                box-shadow: 0 0 12px rgba(13, 148, 136, 0.45);
            }
            .sc-items {
                display: grid; gap: 10px; margin-top: 18px;
                position: relative; z-index: 1;
            }
            @media (min-width: 768px) { .sc-items { grid-template-columns: repeat(2, 1fr); } }
            @media (min-width: 1280px) { .sc-items { grid-template-columns: repeat(3, 1fr); } }
            .sc-item {
                display: flex; align-items: flex-start; gap: 12px;
                padding: 14px 16px;
                background: white;
                border: 1px solid #e2e8f0;
                border-radius: 14px;
                transition: all 0.2s;
            }
            .sc-item:hover {
                border-color: #5eead4;
                box-shadow: 0 4px 12px rgba(13,148,136,0.08);
                transform: translateY(-1px);
            }
            .sc-item.done {
                background: #f0fdfa;
                border-color: #6ee7b7;
            }
            .sc-check {
                width: 28px; height: 28px; border-radius: 8px;
                background: #f1f5f9; color: #94a3b8;
                display: flex; align-items: center; justify-content: center;
                font-size: 14px; flex-shrink: 0;
            }
            .sc-item.done .sc-check {
                background: #10b981; color: white;
            }
            .sc-item-body { flex: 1; min-width: 0; }
            .sc-item-title { font-weight: 700; font-size: 0.85rem; color: #0f172a; line-height: 1.3; }
            .sc-item-desc { font-size: 0.72rem; color: #64748b; margin-top: 2px; line-height: 1.4; }
            .sc-item-cta {
                display: inline-block; margin-top: 8px;
                font-size: 0.72rem; font-weight: 700;
                color: #0d9488; text-decoration: none;
                padding: 4px 10px; border-radius: 8px;
                background: #ccfbf1;
                transition: all 0.2s;
            }
            .sc-item-cta:hover { background: #5eead4; color: white; }
            .sc-item.done .sc-item-cta {
                background: transparent; color: #059669;
                padding-left: 0; padding-right: 0;
            }
            .sc-dismiss {
                background: none; border: none; cursor: pointer;
                color: #94a3b8; padding: 6px;
                font-size: 0.72rem; font-weight: 600;
                transition: color 0.2s;
            }
            .sc-dismiss:hover { color: #475569; }
        </style>

        <div class="sc-header">
            <div class="sc-header-left">
                <div class="sc-kicker">⚡ Pon a punto tu DocFácil</div>
                <h3 class="sc-title">
                    @if($percent >= 80)
                        Casi listo · faltan {{ $total_count - $completed_count }} {{ ($total_count - $completed_count) === 1 ? 'paso' : 'pasos' }}
                    @elseif($percent >= 40)
                        Buen avance · {{ $completed_count }} de {{ $total_count }} hechos
                    @else
                        Empieza por aquí
                    @endif
                </h3>
                <div class="sc-subtitle">Termina estos pasos y tu consultorio queda corriendo al 100%.</div>
            </div>
            <div class="sc-percent-block">
                <div class="sc-percent">{{ $percent }}%</div>
                <button type="button" wire:click="dismiss" class="sc-dismiss" title="Ocultar hasta la próxima sesión">Ocultar</button>
            </div>
        </div>

        <div class="sc-progress-wrap">
            <div class="sc-progress-bar" style="width: {{ $percent }}%;"></div>
        </div>

        <div class="sc-items">
            @foreach($items as $item)
                <div class="sc-item {{ $item['done'] ? 'done' : '' }}">
                    <div class="sc-check">
                        @if($item['done'])
                            <svg style="width:16px;height:16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="3">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                            </svg>
                        @else
                            <span>{{ $item['icon'] }}</span>
                        @endif
                    </div>
                    <div class="sc-item-body">
                        <div class="sc-item-title">{{ $item['title'] }}</div>
                        <div class="sc-item-desc">{{ $item['desc'] }}</div>
                        <a href="{{ $item['url'] }}" class="sc-item-cta">
                            @if($item['done'])
                                ✓ {{ $item['cta'] }}
                            @else
                                {{ $item['cta'] }} →
                            @endif
                        </a>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</x-filament-widgets::widget>
