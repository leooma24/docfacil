{{-- Su mes en DocFácil: el mes en una frase y cada número contra el mes
     pasado. Solo lo que quedó registrado; nada de "le ahorró $X". Estilos en
     línea: los colores de Tailwind no existen en el CSS del panel. --}}
<x-filament-widgets::widget>
    @php
        $grupos = [
            'Agenda' => [
                ['Recordatorios mandados', 'recordatorios', 'heroicon-o-chat-bubble-left-ellipsis', false, false],
                ['Confirmaron por la liga', 'confirmaron', 'heroicon-o-check-badge', false, false],
                ['Inasistencias', 'inasistencias', 'heroicon-o-user-minus', false, true],
            ],
            'Tratamientos y dinero' => array_values(array_filter([
                ['Presupuestos aceptados', 'presupuestos', 'heroicon-o-document-check', false, false],
                $veDinero ? ['Aceptado en presupuestos', 'presupuestosMonto', 'heroicon-o-banknotes', true, false] : null,
                $veDinero ? ['Cobrado de saldos de antes', 'cobradoDeAntes', 'heroicon-o-arrow-down-tray', true, false] : null,
            ])),
        ];
        $fmt = fn ($v, $dinero) => $dinero ? '$' . number_format($v, 2) : number_format($v);
    @endphp

    <div style="background:#fff;border:1px solid #e5e7eb;border-radius:16px;padding:18px;color:#0f172a;">
        <div style="display:flex;align-items:center;gap:12px;">
            <div style="width:40px;height:40px;border-radius:12px;background:#eef2ff;color:#4338ca;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                <x-filament::icon icon="heroicon-o-chart-bar-square" style="width:22px;height:22px;" />
            </div>
            <div style="flex:1;min-width:0;">
                <div style="font-size:12px;font-weight:800;letter-spacing:.06em;text-transform:uppercase;color:#64748b;">{{ ucfirst($nombreMes) }} contra {{ $mesPasado }}</div>
                <div style="font-size:18px;font-weight:800;line-height:1.25;">Su mes en DocFácil</div>
            </div>
        </div>

        <div style="margin:14px 0 4px;padding:12px 14px;border-radius:12px;background:#f8fafc;border:1px solid #f1f5f9;font-size:15px;line-height:1.5;color:#334155;">
            @if($resumen)
                {{ $resumen }}
            @else
                Todavía no hay movimiento en {{ $nombreMes }}. Aquí va a ver cuántos recordatorios mandó, quién confirmó su cita
                y cuántos presupuestos le aceptaron, contra el mes pasado.
            @endif
        </div>

        @foreach($grupos as $grupo => $tarjetas)
            <div style="font-size:13px;font-weight:800;color:#475569;margin:16px 0 8px;">{{ $grupo }}</div>
            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(190px,1fr));gap:10px;">
                @foreach($tarjetas as [$titulo, $clave, $icono, $esDinero, $menosEsMejor])
                    @php
                        $este = $mes[$clave]['este'];
                        $antes = $mes[$clave]['pasado'];
                        $dif = $este - $antes;
                        $bien = $menosEsMejor ? $dif < 0 : $dif > 0;
                        $tinta = $dif == 0 ? '#64748b' : ($bien ? '#15803d' : '#b45309');
                        $cambio = $dif == 0
                            ? 'Igual que ' . $mesPasado
                            : $fmt(abs($dif), $esDinero) . ($dif > 0 ? ' más' : ' menos') . ' que ' . $mesPasado;
                    @endphp
                    <div style="border:1px solid #e5e7eb;border-radius:12px;padding:14px;display:flex;flex-direction:column;gap:6px;">
                        <div style="display:flex;align-items:flex-start;gap:8px;font-size:13px;line-height:1.35;color:#475569;min-height:2.7em;">
                            <x-filament::icon :icon="$icono" style="width:16px;height:16px;color:#64748b;flex-shrink:0;" />
                            <span>{{ $titulo }}</span>
                        </div>
                        <div style="font-size:26px;font-weight:800;line-height:1.1;">{{ $fmt($este, $esDinero) }}</div>
                        <div style="display:flex;align-items:center;gap:4px;font-size:13px;font-weight:700;line-height:1.35;color:{{ $tinta }};">
                            @if($dif != 0)
                                <x-filament::icon :icon="$dif > 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down'" style="width:16px;height:16px;flex-shrink:0;" />
                            @endif
                            <span>{{ $cambio }}</span>
                        </div>
                    </div>
                @endforeach
            </div>
        @endforeach
    </div>
</x-filament-widgets::widget>
