<x-filament-widgets::widget>
    <div style="background:#fff;border:1px solid #e5e7eb;border-radius:16px;padding:1rem 1.25rem;">
        <div style="font-weight:800;font-size:1.05rem;">Su mes en DocFácil</div>
        <div style="font-size:0.8rem;color:#6b7280;margin-bottom:0.75rem;">Lo que pasó en {{ $nombreMes }}, contra el mes pasado. Solo lo que quedó registrado.</div>
        @php
            $tarjetas = [
                ['Recordatorios mandados', $mes['recordatorios'], false, false],
                ['Confirmaron por la liga', $mes['confirmaron'], false, false],
                ['Inasistencias', $mes['inasistencias'], false, true],
                ['Presupuestos aceptados', $mes['presupuestos'], false, false],
            ];
            if ($veDinero) {
                $tarjetas[] = ['Aceptado en presupuestos', $mes['presupuestosMonto'], true, false];
                $tarjetas[] = ['Cobrado de saldos de antes', $mes['cobradoDeAntes'], true, false];
            }
        @endphp
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:0.75rem;">
            @foreach($tarjetas as [$titulo, $valor, $esDinero, $menosEsMejor])
                @php
                    $fmt = fn ($v) => $esDinero ? '$' . number_format($v, 0) : number_format($v);
                    $bien = $menosEsMejor ? $valor['este'] < $valor['pasado'] : $valor['este'] > $valor['pasado'];
                    $igual = $valor['este'] == $valor['pasado'];
                @endphp
                <div style="border:1px solid #e5e7eb;border-radius:12px;padding:0.75rem 0.9rem;">
                    <div style="font-size:0.78rem;color:#6b7280;">{{ $titulo }}</div>
                    <div style="font-size:1.4rem;font-weight:800;color:#111827;">{{ $fmt($valor['este']) }}</div>
                    <div style="font-size:0.75rem;color:{{ $igual ? '#6b7280' : ($bien ? '#047857' : '#b45309') }};">El mes pasado: {{ $fmt($valor['pasado']) }}</div>
                </div>
            @endforeach
        </div>
    </div>
</x-filament-widgets::widget>
