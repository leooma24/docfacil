<x-filament-widgets::widget>
    @php $prospects = $this->getProspects(); @endphp

    <style>
        .pf-card {
            position: relative; border-radius: 1.25rem; padding: 22px 24px;
            overflow: hidden; color: white;
            background: linear-gradient(135deg, #3b82f6 0%, #0891b2 40%, #06b6d4 100%);
            box-shadow: 0 16px 40px -15px rgba(59,130,246,0.4), inset 0 1px 0 rgba(255,255,255,0.2);
        }
        .pf-card::before { content:''; position:absolute; top:-60px; right:-40px; width:220px; height:220px; background:radial-gradient(circle,rgba(255,255,255,0.15),transparent 70%); border-radius:50%; }
        .pf-grain { position:absolute; inset:0; background-image:radial-gradient(circle at 1px 1px,rgba(255,255,255,0.08) 1px,transparent 0); background-size:20px 20px; }
        .pf-content { position: relative; z-index: 1; }
        .pf-head { display:flex; align-items:center; gap:12px; margin-bottom:16px; }
        .pf-head-icon { width:44px; height:44px; border-radius:14px; background:rgba(255,255,255,0.2); backdrop-filter:blur(12px); border:1.5px solid rgba(255,255,255,0.3); display:flex; align-items:center; justify-content:center; font-size:22px; flex-shrink:0; }
        .pf-head-title { font-size:1.15rem; font-weight:800; }
        .pf-head-sub { font-size:0.75rem; opacity:0.85; }

        .pf-list { display:flex; flex-direction:column; gap:8px; }
        .pf-item {
            display:flex; align-items:center; gap:12px;
            background:rgba(255,255,255,0.14); backdrop-filter:blur(10px);
            border:1px solid rgba(255,255,255,0.22); border-radius:12px;
            padding:12px 14px; transition:all 0.2s;
        }
        .pf-item:hover { background:rgba(255,255,255,0.22); transform:translateX(3px); }
        .pf-item-day {
            width:36px; height:36px; border-radius:10px;
            background:rgba(255,255,255,0.22); display:flex; align-items:center; justify-content:center;
            font-weight:800; font-size:0.75rem; flex-shrink:0;
        }
        .pf-item-overdue { background:rgba(239,68,68,0.4); border:1px solid rgba(239,68,68,0.5); }
        .pf-item-info { flex:1; min-width:0; }
        .pf-item-name { font-weight:700; font-size:0.85rem; }
        .pf-item-meta { font-size:0.72rem; opacity:0.82; }
        .pf-item-actions { display:flex; gap:6px; flex-shrink:0; }
        .pf-btn {
            display:inline-flex; align-items:center; justify-content:center;
            width:34px; height:34px; border-radius:10px;
            background:rgba(255,255,255,0.2); border:1px solid rgba(255,255,255,0.3);
            color:white; text-decoration:none; font-size:16px;
            transition:all 0.2s;
        }
        .pf-btn:hover { background:rgba(255,255,255,0.35); transform:scale(1.08); }
        .pf-btn-green { background:rgba(16,185,129,0.4); border-color:rgba(16,185,129,0.5); }

        .pf-empty { text-align:center; padding:20px; background:rgba(255,255,255,0.12); border:1px dashed rgba(255,255,255,0.3); border-radius:14px; }
    </style>

    <div class="pf-card">
        <div class="pf-grain"></div>
        <div class="pf-content">
            <div class="pf-head">
                <div class="pf-head-icon">📋</div>
                <div>
                    <div class="pf-head-title">Seguimientos de hoy ({{ count($prospects) }})</div>
                    <div class="pf-head-sub">Prospectos que necesitan contacto hoy. Click en WhatsApp para mandar mensaje pre-armado.</div>
                </div>
            </div>

            @if(count($prospects) > 0)
            <div class="pf-list">
                @foreach($prospects as $p)
                <div class="pf-item">
                    <div class="pf-item-day {{ $p['overdue'] ? 'pf-item-overdue' : '' }}">
                        {{ $p['day'] == 0 ? 'NEW' : 'D' . $p['day'] }}
                    </div>
                    <div class="pf-item-info">
                        <div class="pf-item-name">{{ $p['name'] }}</div>
                        <div class="pf-item-meta">
                            {{ $p['clinic'] ?: $p['specialty'] ?: 'Sin consultorio' }}
                            {{ $p['overdue'] ? ' · ⚠️ Atrasado' : '' }}
                        </div>
                    </div>
                    <div class="pf-item-actions">
                        @if($p['phone'])
                        <a href="{{ $p['wa_url'] }}" target="_blank" class="pf-btn pf-btn-green" title="WhatsApp con mensaje pre-armado">💬</a>
                        @endif
                        <a href="{{ route('filament.ventas.resources.prospectos.edit', $p['id']) }}" class="pf-btn" title="Ver prospecto">👤</a>
                    </div>
                </div>
                @endforeach
            </div>
            @else
            <div class="pf-empty">
                <div style="font-size:28px;margin-bottom:4px;">✅</div>
                <div style="font-size:0.85rem;font-weight:600;">Sin seguimientos pendientes. ¡Buen trabajo!</div>
            </div>
            @endif
        </div>
    </div>
</x-filament-widgets::widget>
