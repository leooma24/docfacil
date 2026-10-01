@php
    use App\Models\OdontogramTooth;
    $dientes = $odonto->teeth->mapWithKeys(fn ($t) => [$t->tooth_number => [
        'condition' => $t->condition, 'surfaces' => $t->caras(), 'notes' => $t->notes,
    ]])->all();
    $denticion = $odonto->teeth->contains(fn ($t) => $t->tooth_number >= 51) ? 'mixta' : 'permanente';
    $resumen = OdontogramTooth::resumenDeBoca($dientes);
    $nombres = OdontogramTooth::nombresParaResumen();
    $etiquetas = OdontogramTooth::conditionLabels();
    $colores = OdontogramTooth::conditionColors();
    $nombresCara = OdontogramTooth::caraLabels();
    $plural = fn (string $c, int $n) => $n . ' ' . ($nombres[$c][$n === 1 ? 0 : 1] ?? $c);
    $paciente = $odonto->patient;
    $clinica = $odonto->clinic;
    $doctor = $odonto->doctor;
    $hallazgos = $odonto->teeth->filter(fn ($t) => $t->condition !== 'healthy' || array_filter($t->caras()) || $t->notes);
@endphp
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Odontograma · {{ $paciente->full_name }} · {{ $odonto->evaluation_date->format('d/m/Y') }}</title>
    <style>
        @page { size: letter; margin: 14mm 12mm; }
        * { box-sizing: border-box; }
        body { margin: 0; background: #f1f5f9; font-family: Inter, system-ui, -apple-system, "Segoe UI", sans-serif; color: #0f172a; }
        .hoja { max-width: 1000px; margin: 24px auto; background: #fff; border-radius: 14px; padding: 28px 32px; box-shadow: 0 4px 24px rgba(15,23,42,.08); }
        .barra { max-width: 1000px; margin: 20px auto 0; display: flex; justify-content: flex-end; gap: 10px; }
        .btn { font: 600 14px Inter, system-ui, sans-serif; padding: 10px 16px; border-radius: 10px; border: 0; cursor: pointer; background: #0d9488; color: #fff; }
        .btn.sec { background: #fff; color: #334155; border: 1px solid #cbd5e1; }
        header { display: flex; justify-content: space-between; align-items: flex-start; gap: 20px; border-bottom: 2px solid #0d9488; padding-bottom: 14px; margin-bottom: 16px; }
        header img { max-height: 54px; max-width: 160px; }
        h1 { font-size: 20px; margin: 0 0 4px; letter-spacing: -.01em; }
        .muted { color: #64748b; font-size: 12.5px; line-height: 1.5; }
        .datos { display: grid; grid-template-columns: repeat(4, 1fr); gap: 10px 16px; margin-bottom: 14px; font-size: 13px; }
        .datos b { display: block; font-size: 10.5px; letter-spacing: .08em; text-transform: uppercase; color: #64748b; margin-bottom: 2px; }
        .caja { border: 1px solid #e2e8f0; border-radius: 12px; padding: 12px; margin-bottom: 14px; }
        .titulo { font-size: 10.5px; font-weight: 700; letter-spacing: .09em; text-transform: uppercase; color: #64748b; margin: 0 0 8px; }
        .resumen { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; font-size: 13px; }
        table { width: 100%; border-collapse: collapse; font-size: 12.5px; }
        th { text-align: left; font-size: 10.5px; letter-spacing: .06em; text-transform: uppercase; color: #64748b; border-bottom: 1px solid #e2e8f0; padding: 6px 8px; }
        td { border-bottom: 1px solid #f1f5f9; padding: 6px 8px; vertical-align: top; }
        .punto { display: inline-block; width: 9px; height: 9px; border-radius: 3px; margin-right: 6px; vertical-align: 0; }
        .firma { display: flex; justify-content: flex-end; margin-top: 36px; }
        .firma div { width: 260px; border-top: 1px solid #334155; text-align: center; padding-top: 6px; font-size: 12.5px; }
        .odo-desliza { display: none !important; }
        @media print {
            body { background: #fff; }
            .barra { display: none; }
            .hoja { box-shadow: none; margin: 0; padding: 0; max-width: none; border-radius: 0; }
            .caja { break-inside: avoid; }
        }
    </style>
</head>
<body>
    <div class="barra">
        <button class="btn sec" onclick="window.close()">Cerrar</button>
        <button class="btn" onclick="window.print()">Imprimir o guardar PDF</button>
    </div>
    <div class="hoja">
        <header>
            <div>
                <h1>Odontograma</h1>
                <div class="muted">Evaluación del {{ $odonto->evaluation_date->translatedFormat('j \d\e F \d\e Y') }}</div>
            </div>
            <div style="text-align:right;">
                @if($clinica?->logo)
                    <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($clinica->logo) }}" alt="">
                @endif
                <div style="font-weight:700;font-size:14px;">{{ $clinica?->name }}</div>
                <div class="muted">{{ collect([$clinica?->address, $clinica?->city, $clinica?->phone])->filter()->implode(' · ') }}</div>
            </div>
        </header>

        <div class="datos">
            <div><b>Paciente</b>{{ $paciente->full_name }}</div>
            <div><b>Edad</b>{{ $paciente->birth_date ? $paciente->birth_date->age . ' años' : '—' }}</div>
            <div><b>Doctor</b>{{ $doctor?->user?->name ?? '—' }}</div>
            <div><b>Cédula</b>{{ $doctor?->license_number ?: '—' }}</div>
        </div>

        <div class="caja">
            <x-odontograma.arcadas :dientes="$dientes" :denticion="$denticion" />
        </div>

        <div class="caja resumen">
            <div>
                <p class="titulo" style="color:#b91c1c;">Por tratar</p>
                {{ collect($resumen['por_tratar'])->map(fn ($n, $c) => $plural($c, $n))->implode(' · ') ?: 'Nada marcado' }}
            </div>
            <div>
                <p class="titulo" style="color:#1d4ed8;">Tratamientos existentes</p>
                {{ collect($resumen['existentes'])->map(fn ($n, $c) => $plural($c, $n))
                    ->when($resumen['ausentes'], fn ($l) => $l->push($plural('missing', $resumen['ausentes'])))->implode(' · ') ?: 'Ninguno' }}
            </div>
        </div>

        @if($hallazgos->count())
            <div class="caja">
                <p class="titulo">Detalle por diente</p>
                <table>
                    <thead><tr><th style="width:60px;">Diente</th><th>Condición</th><th>Caras</th><th>Notas</th></tr></thead>
                    <tbody>
                        @foreach($hallazgos as $t)
                            <tr>
                                <td><strong>{{ $t->tooth_number }}</strong></td>
                                <td><span class="punto" style="background:{{ $colores[$t->condition] ?? '#94a3b8' }}"></span>{{ $etiquetas[$t->condition] ?? $t->condition }}</td>
                                <td>{{ collect($t->caras())->filter()->map(fn ($c, $cara) => $nombresCara[$cara] . ': ' . ($etiquetas[$c] ?? $c))->implode(' · ') ?: '—' }}</td>
                                <td>{{ $t->notes ?: '—' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        @if($anterior)
            <div class="caja">
                <p class="titulo">Cambios desde el {{ $anterior->evaluation_date->format('d/m/Y') }}</p>
                <div style="font-size:13px;">{{ $cambios ? implode(' · ', $cambios) : 'Sin cambios.' }}</div>
            </div>
        @endif

        @if($odonto->notes)
            <div class="caja">
                <p class="titulo">Observaciones</p>
                <div style="font-size:13px;">{{ $odonto->notes }}</div>
            </div>
        @endif

        <div class="firma"><div>{{ $doctor?->user?->name }}<br><span class="muted">Firma</span></div></div>
    </div>
</body>
</html>
