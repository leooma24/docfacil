<x-filament-widgets::widget>
    @php $ranking = $this->getRanking(); @endphp

    <style>
        .lb-card {
            position:relative; border-radius:1.25rem; padding:22px 24px;
            overflow:hidden; color:white;
            background:linear-gradient(135deg, #f59e0b 0%, #f97316 40%, #ef4444 100%);
            box-shadow:0 16px 40px -15px rgba(245,158,11,0.4), inset 0 1px 0 rgba(255,255,255,0.2);
        }
        .lb-card::before { content:''; position:absolute; top:-60px; right:-40px; width:220px; height:220px; background:radial-gradient(circle,rgba(255,255,255,0.15),transparent 70%); border-radius:50%; }
        .lb-grain { position:absolute; inset:0; background-image:radial-gradient(circle at 1px 1px,rgba(255,255,255,0.08) 1px,transparent 0); background-size:20px 20px; }
        .lb-content { position:relative; z-index:1; }
        .lb-head { display:flex; align-items:center; gap:12px; margin-bottom:16px; }
        .lb-head-icon { width:44px; height:44px; border-radius:14px; background:rgba(255,255,255,0.2); backdrop-filter:blur(12px); border:1.5px solid rgba(255,255,255,0.3); display:flex; align-items:center; justify-content:center; font-size:22px; }
        .lb-head-title { font-size:1.15rem; font-weight:800; }
        .lb-head-sub { font-size:0.75rem; opacity:0.85; }

        .lb-table { width:100%; border-collapse:collapse; }
        .lb-table th { font-size:0.65rem; text-transform:uppercase; letter-spacing:0.08em; opacity:0.8; font-weight:700; padding:8px 6px; text-align:center; border-bottom:1px solid rgba(255,255,255,0.2); }
        .lb-table th:first-child, .lb-table td:first-child { text-align:left; }
        .lb-table td { padding:10px 6px; text-align:center; font-size:0.82rem; border-bottom:1px solid rgba(255,255,255,0.1); }
        .lb-table tr:last-child td { border-bottom:none; }
        .lb-me { background:rgba(255,255,255,0.12); border-radius:8px; }
        .lb-pos { width:28px; height:28px; border-radius:8px; display:inline-flex; align-items:center; justify-content:center; font-weight:800; font-size:0.75rem; }
        .lb-pos-1 { background:rgba(255,255,255,0.3); }
        .lb-pos-2 { background:rgba(255,255,255,0.2); }
        .lb-pos-3 { background:rgba(255,255,255,0.15); }
        .lb-name { font-weight:700; text-align:left !important; }
        .lb-highlight { font-weight:800; font-size:0.95rem; }
    </style>

    <div class="lb-card">
        <div class="lb-grain"></div>
        <div class="lb-content">
            <div class="lb-head">
                <div class="lb-head-icon">🏆</div>
                <div>
                    <div class="lb-head-title">Leaderboard — {{ now()->translatedFormat('F Y') }}</div>
                    <div class="lb-head-sub">Ranking por cierres y comisiones del mes</div>
                </div>
            </div>

            @if(count($ranking) === 0)
            <div style="text-align:center;padding:20px;opacity:0.85;font-size:0.85rem;">Aún no hay actividad este mes.</div>
            @else
            <table class="lb-table">
                <thead>
                    <tr>
                        <th style="width:40px;">#</th>
                        <th style="text-align:left;">Vendedor</th>
                        <th>Contactos</th>
                        <th>Demos</th>
                        <th>Cierres</th>
                        <th>Tasa</th>
                        <th>Comisión</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($ranking as $i => $rep)
                    <tr class="{{ $rep['is_me'] ? 'lb-me' : '' }}">
                        <td>
                            <span class="lb-pos {{ $i < 3 ? 'lb-pos-' . ($i + 1) : '' }}">
                                {{ $i === 0 ? '🥇' : ($i === 1 ? '🥈' : ($i === 2 ? '🥉' : $i + 1)) }}
                            </span>
                        </td>
                        <td class="lb-name">{{ $rep['name'] }}{{ $rep['is_me'] ? ' (tú)' : '' }}</td>
                        <td>{{ $rep['contacts'] }}</td>
                        <td>{{ $rep['demos'] }}</td>
                        <td class="lb-highlight">{{ $rep['conversions'] }}</td>
                        <td>{{ $rep['conv_rate'] }}%</td>
                        <td class="lb-highlight">${{ number_format($rep['commission']) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @endif
        </div>
    </div>
</x-filament-widgets::widget>
