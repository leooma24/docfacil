{{-- Lo que hay que atender: primero lo urgente, cada aviso con lo que hay
     que hacer escrito en su botón. Estilos en línea: los colores de Tailwind
     no existen en el CSS del panel (auditoría del 12-oct-2026). --}}
<x-filament-widgets::widget>
    @php
        $alerts = $this->getAlerts();
        $tonos = [
            'danger'  => ['fondo' => '#fef2f2', 'tinta' => '#b91c1c', 'borde' => '#fecaca'],
            'warning' => ['fondo' => '#fffbeb', 'tinta' => '#b45309', 'borde' => '#fde68a'],
            'info'    => ['fondo' => '#eff6ff', 'tinta' => '#1d4ed8', 'borde' => '#bfdbfe'],
            'success' => ['fondo' => '#f0fdf4', 'tinta' => '#15803d', 'borde' => '#bbf7d0'],
        ];
        $urgentes = collect($alerts)->where('type', 'danger')->count();
    @endphp

    <style>
        .lqa-fila { display:flex; align-items:center; gap:14px; padding:14px 4px; border-top:1px solid #f1f5f9; color:#0f172a; text-decoration:none; }
        .lqa-fila:first-child { border-top:0; }
        a.lqa-fila:hover { background:#f8fafc; }
        .lqa-boton { flex-shrink:0; min-height:40px; padding:0 16px; display:inline-flex; align-items:center; border-radius:999px; border:1px solid #99f6e4; background:#f0fdfa; color:#0f766e; font-size:14px; font-weight:700; white-space:nowrap; }
        a.lqa-fila:hover .lqa-boton { background:#ccfbf1; }
        @media (max-width: 560px) {
            .lqa-fila { flex-wrap:wrap; }
            .lqa-boton { margin-left:50px; }
        }
    </style>

    <div style="background:#fff;border:1px solid #e5e7eb;border-radius:16px;padding:18px 18px 8px;color:#0f172a;">
        <div style="display:flex;align-items:center;gap:12px;margin-bottom:6px;">
            <div style="width:40px;height:40px;border-radius:12px;background:#f0fdfa;color:#0f766e;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                <x-filament::icon icon="heroicon-o-bell-alert" style="width:22px;height:22px;" />
            </div>
            <div style="flex:1;min-width:0;">
                <div style="font-size:12px;font-weight:800;letter-spacing:.06em;text-transform:uppercase;color:#64748b;">Para hoy</div>
                <div style="font-size:18px;font-weight:800;line-height:1.25;">Lo que hay que atender</div>
            </div>
            @if(count($alerts) > 0)
                <span style="flex-shrink:0;padding:4px 12px;border-radius:999px;font-size:13px;font-weight:700;background:{{ $urgentes ? '#fef2f2' : '#f1f5f9' }};color:{{ $urgentes ? '#b91c1c' : '#475569' }};">
                    {{ $urgentes ? $urgentes . ($urgentes === 1 ? ' urgente' : ' urgentes') : count($alerts) . (count($alerts) === 1 ? ' pendiente' : ' pendientes') }}
                </span>
            @endif
        </div>

        @if(count($alerts) > 0)
            <div>
                @foreach($alerts as $alert)
                    @php $t = $tonos[$alert['type']] ?? $tonos['info']; @endphp
                    <{{ isset($alert['url']) ? 'a' : 'div' }} @isset($alert['url']) href="{{ $alert['url'] }}" @endisset class="lqa-fila">
                        <div style="width:36px;height:36px;border-radius:12px;background:{{ $t['fondo'] }};border:1px solid {{ $t['borde'] }};color:{{ $t['tinta'] }};display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                            <x-filament::icon :icon="$alert['icon']" style="width:18px;height:18px;" />
                        </div>
                        <div style="flex:1;min-width:0;">
                            <div style="font-size:15px;font-weight:700;line-height:1.35;color:{{ $alert['type'] === 'danger' ? '#b91c1c' : '#0f172a' }};">{{ $alert['title'] }}</div>
                            <div style="font-size:13px;color:#64748b;margin-top:2px;line-height:1.4;">{{ $alert['desc'] }}</div>
                        </div>
                        @isset($alert['url'])
                            <span class="lqa-boton">{{ $alert['boton'] ?? 'Ver' }}</span>
                        @endisset
                    </{{ isset($alert['url']) ? 'a' : 'div' }}>
                @endforeach
            </div>
        @else
            <div style="display:flex;align-items:center;gap:12px;padding:14px 4px 12px;">
                <div style="width:36px;height:36px;border-radius:12px;background:#f0fdf4;color:#15803d;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                    <x-filament::icon icon="heroicon-o-check-circle" style="width:20px;height:20px;" />
                </div>
                <div>
                    <div style="font-size:15px;font-weight:700;">Todo en orden por ahora</div>
                    <div style="font-size:13px;color:#64748b;">Aquí le avisamos cuando haya algo que atender.</div>
                </div>
            </div>
        @endif
    </div>
</x-filament-widgets::widget>
