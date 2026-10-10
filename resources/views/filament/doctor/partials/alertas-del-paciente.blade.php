{{-- Lo que el doctor tiene que tener a la vista antes de tratar y recetar:
     alergias (o que nadie ha preguntado), antecedentes (casillas y notas
     juntas) y, cada 6 meses, la pregunta de si sigue igual. --}}
@php
    $pac = $this->appointment?->patient;
    $al = $pac ? \App\Support\AlertasClinicas::delPaciente($pac) : null;
    $anticoagulado = $pac ? \App\Support\AlertasClinicas::tomaAnticoagulantes($pac->notasParaAlertas()) : false;
@endphp
@if($pac)
    @if($this->isFieldEnabled('allergies_alert'))
        @if($pac->tieneAlergias())
            <div style="background:#fef2f2;border-left:4px solid #ef4444;padding:10px 14px;border-radius:8px;margin-bottom:12px;display:flex;align-items:center;gap:10px;">
                <span style="font-size:18px;color:#dc2626;"><x-icono nombre="exclamation-triangle" /></span>
                <div style="font-size:13px;color:#991b1b;"><strong>Alergias:</strong> {{ $pac->allergies }}</div>
            </div>
        @elseif(blank($pac->allergies))
            <div style="background:#fffbeb;border-left:4px solid #f59e0b;padding:10px 14px;border-radius:8px;margin-bottom:12px;">
                <div style="font-size:13px;color:#92400e;margin-bottom:8px;"><strong>Alergias no registradas.</strong> Pregúntele antes de recetar y anótelas aquí; quedan en su expediente.</div>
                <div style="display:flex;gap:8px;flex-wrap:wrap;">
                    <input type="text" wire:model="alergiasNuevas" placeholder="Ej. Penicilina, látex" style="flex:1;min-width:180px;padding:7px 10px;border:1px solid #fcd34d;border-radius:8px;font-size:13px;">
                    <button type="button" wire:click="guardarAlergias" style="padding:7px 12px;border-radius:8px;background:#b45309;color:#fff;font-size:12px;font-weight:700;">Guardar</button>
                    <button type="button" wire:click="sinAlergias" style="padding:7px 12px;border-radius:8px;background:#fff;color:#92400e;border:1px solid #fcd34d;font-size:12px;font-weight:700;">No tiene</button>
                </div>
            </div>
        @endif
    @endif
    @if($this->isFieldEnabled('anticoagulants_alert') && $anticoagulado)
        <div style="background:#fff7ed;border-left:4px solid #f97316;padding:10px 14px;border-radius:8px;margin-bottom:12px;display:flex;align-items:center;gap:10px;">
            <span style="font-size:18px;color:#ea580c;"><x-icono nombre="exclamation-triangle" /></span>
            <div style="font-size:13px;color:#9a3412;"><strong>Toma anticoagulantes:</strong> precaución con procedimientos invasivos y antiinflamatorios.</div>
        </div>
    @endif

    {{-- Antecedentes: en rojo los que tiene, y las casillas para marcar o quitar con un toque. --}}
    <div style="background:#f8fafc;border-left:4px solid {{ $al['riesgos'] ? '#dc2626' : '#64748b' }};padding:10px 14px;border-radius:8px;margin-bottom:12px;">
        <div style="font-size:13px;color:#334155;margin-bottom:8px;">
            <strong>Antecedentes importantes:</strong>
            @if($al['riesgos'])
                {{-- Los anticoagulantes ya tienen su aviso arriba: no se repiten aquí. --}}
                @php $resumen = $anticoagulado && $this->isFieldEnabled('anticoagulants_alert') ? array_values(array_diff($al['riesgos'], ['Anticoagulantes'])) : $al['riesgos']; @endphp
                @if($resumen)<span style="color:#b91c1c;font-weight:700;">{{ implode(' · ', $resumen) }}</span>@else<span style="color:#64748b;">los de arriba.</span>@endif
            @else
                <span style="color:#64748b;">ninguno marcado. Toque los que tenga.</span>
            @endif
        </div>
        <div style="display:flex;gap:6px;flex-wrap:wrap;">
            @foreach(\App\Support\AlertasClinicas::OPCIONES as $clave => $nombre)
                @php $puesta = in_array($clave, $pac->riesgos ?? [], true); @endphp
                <button type="button" wire:click="alternarRiesgo('{{ $clave }}')"
                        style="padding:5px 10px;border-radius:999px;font-size:12px;font-weight:700;border:1px solid {{ $puesta ? '#dc2626' : '#cbd5e1' }};background:{{ $puesta ? '#fee2e2' : '#fff' }};color:{{ $puesta ? '#991b1b' : '#475569' }};">
                    {{ $puesta ? '✓ ' : '' }}{{ $nombre }}
                </button>
            @endforeach
        </div>
    </div>

    @if($al['revisar'])
        <div style="background:#fffbeb;border-left:4px solid #f59e0b;padding:10px 14px;border-radius:8px;margin-bottom:12px;display:flex;align-items:center;gap:10px;flex-wrap:wrap;">
            <div style="flex:1;min-width:200px;font-size:13px;color:#92400e;">
                <strong>¿Sigue igual?</strong>
                @if($al['meses'] !== null)
                    Lo último que se anotó de {{ $pac->first_name }} es de hace {{ $al['meses'] }} {{ $al['meses'] === 1 ? 'mes' : 'meses' }}.
                @else
                    Nadie ha confirmado esto con {{ $pac->first_name }}.
                @endif
                Si cambió algo, toque las casillas de arriba.
            </div>
            <button type="button" wire:click="sigueIgual" style="padding:7px 14px;border-radius:8px;background:#b45309;color:#fff;font-size:12px;font-weight:700;">Sigue igual</button>
        </div>
    @endif
@endif
