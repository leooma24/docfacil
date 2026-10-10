@php
    $tel = function (?string $t) {
        $d = substr(preg_replace('/\D/', '', (string) $t), -10);
        return strlen($d) === 10 ? substr($d, 0, 3) . ' ' . substr($d, 3, 3) . ' ' . substr($d, 6) : ($t ?: '—');
    };
@endphp
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Agenda {{ $dia->format('d/m/Y') }} · {{ $clinica?->name }}</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: Arial, Helvetica, sans-serif; color: #111; margin: 24px; font-size: 13px; }
        h1 { font-size: 18px; margin: 0; }
        .sub { color: #555; margin: 2px 0 14px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border-bottom: 1px solid #ccc; padding: 7px 6px; text-align: left; vertical-align: top; }
        th { font-size: 11px; text-transform: uppercase; color: #555; }
        .hora { font-weight: bold; white-space: nowrap; }
        .alerta { color: #b91c1c; font-weight: bold; }
        .debe { color: #92400e; font-weight: bold; white-space: nowrap; }
        .boton { display: inline-block; margin-bottom: 14px; padding: 8px 14px; background: #0f766e; color: #fff; border: 0; border-radius: 8px; font-size: 14px; cursor: pointer; }
        .notas { width: 30%; }
        @media print { .boton { display: none; } body { margin: 10mm; } }
    </style>
</head>
<body>
    <button class="boton" onclick="window.print()">Imprimir</button>
    <h1>{{ $clinica?->name }}</h1>
    <div class="sub">Agenda del {{ $dia->locale('es')->isoFormat('dddd D [de] MMMM [de] YYYY') }} · {{ $citas->count() }} {{ $citas->count() === 1 ? 'cita' : 'citas' }}</div>

    @if($citas->isEmpty())
        <p>No hay citas ese día.</p>
    @else
        <table>
            <tr><th>Hora</th><th>Paciente</th><th>Teléfono</th><th>Tratamiento</th><th>Alertas</th><th>Debe</th><th class="notas">Notas</th></tr>
            @foreach($citas as $c)
                @php
                    $alertas = [...(\App\Models\LabOrder::pendienteParaCita($c->id) ? ['Lab: no ha llegado'] : []), ...\App\Support\AlertasClinicas::etiquetas($c->patient)];
                    $debe = (float) ($deudas[$c->patient_id] ?? 0);
                @endphp
                <tr>
                    <td class="hora">{{ $c->starts_at->format('H:i') }}</td>
                    <td>{{ $c->patient?->first_name }} {{ $c->patient?->last_name }}@if($c->doctor?->user)<br><span style="color:#555;font-size:11px;">{{ $c->doctor->user->name }}</span>@endif</td>
                    <td>{{ $tel(method_exists($c->patient, 'telefonoDeContacto') ? $c->patient->telefonoDeContacto() : $c->patient?->phone) }}</td>
                    <td>{{ $c->service?->name ?? '—' }}</td>
                    <td class="alerta">{{ implode(' · ', $alertas) ?: '' }}</td>
                    <td class="debe">@if($debe > 0)Debe ${{ number_format($debe, 0) }}@endif</td>
                    <td class="notas"></td>
                </tr>
            @endforeach
        </table>
    @endif
</body>
</html>
