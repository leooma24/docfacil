@php
    $clinica = $patient->clinic;
    $domicilio = $clinica ? collect([$clinica->address, $clinica->city, $clinica->state])->filter()->implode(', ') : '';
    $generos = ['male' => 'Masculino', 'female' => 'Femenino', 'other' => 'Otro'];
    $cie10 = fn ($codigos) => collect($codigos ?? [])
        ->map(fn ($c) => is_array($c) ? trim(($c['code'] ?? '') . ' ' . ($c['description'] ?? $c['name'] ?? '')) : (string) $c)
        ->filter()
        ->implode('; ');
    $signos = fn ($nota) => collect([
        'TA' => $nota->vital_signs['blood_pressure'] ?? null,
        'FC' => ($nota->vital_signs['heart_rate'] ?? null) ? $nota->vital_signs['heart_rate'] . ' lpm' : null,
        'Temp.' => ($nota->vital_signs['temperature'] ?? null) ? $nota->vital_signs['temperature'] . ' °C' : null,
        'Peso' => ($nota->vital_signs['weight'] ?? null) ? $nota->vital_signs['weight'] . ' kg' : null,
        'FR' => $nota->respiratory_rate ? $nota->respiratory_rate . ' rpm' : null,
        'SpO2' => $nota->oxygen_saturation ? $nota->oxygen_saturation . ' %' : null,
        'Talla' => $nota->height ? $nota->height . ' cm' : null,
    ])->filter()->map(fn ($valor, $clave) => "{$clave} {$valor}")->implode(' · ');
@endphp
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Expediente de {{ $patient->full_name }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'DejaVu Sans', Arial, sans-serif; font-size: 10px; color: #333; line-height: 1.45; }
        .page { padding: 28px 36px 50px; }
        .encabezado { border-bottom: 3px solid #14b8a6; padding-bottom: 10px; margin-bottom: 14px; }
        .consultorio { font-size: 13px; font-weight: bold; color: #111; }
        .detalle { font-size: 9px; color: #666; }
        h1 { font-size: 15px; color: #0f766e; margin-top: 8px; }
        h2 { font-size: 11px; color: #0d9488; text-transform: uppercase; letter-spacing: 0.5px; margin: 16px 0 6px; border-bottom: 1px solid #e5e7eb; padding-bottom: 3px; }
        .ficha { width: 100%; border-collapse: collapse; }
        .ficha td { padding: 4px 8px; border: 1px solid #e5e7eb; vertical-align: top; width: 25%; }
        .etiqueta { font-size: 8px; color: #999; text-transform: uppercase; display: block; }
        .valor { font-size: 10px; color: #111; font-weight: bold; }
        .bloque { border: 1px solid #e5e7eb; border-radius: 4px; padding: 8px 10px; margin-bottom: 8px; }
        .bloque-titulo { font-size: 10.5px; font-weight: bold; color: #111; }
        .autoria { font-size: 8.5px; color: #6b7280; margin-bottom: 4px; }
        .campo { margin-top: 3px; }
        .campo strong { color: #374151; }
        .vacio { color: #9ca3af; font-style: italic; }
        .pie { position: fixed; bottom: 14px; left: 36px; right: 36px; font-size: 7.5px; color: #9ca3af; text-align: center; border-top: 1px solid #eee; padding-top: 4px; }
    </style>
</head>
<body>
<div class="page">
    <div class="encabezado">
        @if($clinica)
            <div class="consultorio">{{ $clinica->name }}</div>
            @if($domicilio)<div class="detalle">{{ $domicilio }}</div>@endif
            @if($clinica->phone)<div class="detalle">Tel: {{ $clinica->phone }}</div>@endif
        @endif
        <h1>Resumen del expediente clínico</h1>
        <div class="detalle">Generado el {{ now()->format('d/m/Y') }} a las {{ now()->format('H:i') }}@if(auth()->user()) por {{ auth()->user()->name }}@endif</div>
    </div>

    {{-- Ficha de identificación --}}
    <h2>Ficha de identificación</h2>
    <table class="ficha">
        <tr>
            <td colspan="2"><span class="etiqueta">Paciente</span><span class="valor">{{ $patient->full_name }}</span></td>
            <td><span class="etiqueta">Nacimiento</span><span class="valor">@if($patient->birth_date){{ $patient->birth_date->format('d/m/Y') }} ({{ $patient->birth_date->age }} años)@else — @endif</span></td>
            <td><span class="etiqueta">Género</span><span class="valor">{{ $generos[$patient->gender] ?? '—' }}</span></td>
        </tr>
        <tr>
            <td><span class="etiqueta">Teléfono</span><span class="valor">{{ $patient->phone ?: '—' }}</span></td>
            <td><span class="etiqueta">Correo</span><span class="valor">{{ $patient->email ?: '—' }}</span></td>
            <td><span class="etiqueta">Tipo de sangre</span><span class="valor">{{ $patient->blood_type ?: '—' }}</span></td>
            <td><span class="etiqueta">Alergias</span><span class="valor">{{ $patient->allergies ?: 'Ninguna registrada' }}</span></td>
        </tr>
        <tr>
            <td colspan="4"><span class="etiqueta">Domicilio</span><span class="valor">{{ $patient->address ?: '—' }}</span></td>
        </tr>
        @if($patient->medical_notes)
        <tr>
            <td colspan="4"><span class="etiqueta">Antecedentes y notas</span>{!! nl2br(e($patient->medical_notes)) !!}</td>
        </tr>
        @endif
        <tr>
            <td colspan="4">
                <span class="etiqueta">Aviso de privacidad</span>
                @if($patient->aviso_privacidad_aceptado_at)
                    Aceptado el {{ $patient->aviso_privacidad_aceptado_at->format('d/m/Y') }} ({{ \App\Support\AvisoDePrivacidad::MEDIOS[$patient->aviso_privacidad_medio] ?? $patient->aviso_privacidad_medio }}, versión {{ $patient->aviso_privacidad_version }})
                @else
                    <span class="vacio">Pendiente de aceptar</span>
                @endif
            </td>
        </tr>
    </table>

    {{-- Notas clínicas --}}
    <h2>Notas clínicas ({{ $patient->medicalRecords->count() }})</h2>
    @forelse($patient->medicalRecords as $nota)
        <div class="bloque">
            <div class="bloque-titulo">Consulta del {{ $nota->visit_date->format('d/m/Y') }}</div>
            <div class="autoria">Elaboró: {{ $nota->autoria() }}</div>
            @if($nota->chief_complaint)<div class="campo"><strong>Motivo:</strong> {{ $nota->chief_complaint }}</div>@endif
            @if($signos($nota))<div class="campo"><strong>Signos vitales:</strong> {{ $signos($nota) }}</div>@endif
            @if($nota->diagnosis)<div class="campo"><strong>Diagnóstico:</strong> {{ $nota->diagnosis }}</div>@endif
            @if($cie10($nota->cie10_codes))<div class="campo"><strong>CIE-10:</strong> {{ $cie10($nota->cie10_codes) }}</div>@endif
            @if($nota->treatment)<div class="campo"><strong>Tratamiento:</strong> {{ $nota->treatment }}</div>@endif
            @if($nota->notes)<div class="campo"><strong>Notas:</strong> {{ $nota->notes }}</div>@endif
        </div>
    @empty
        <p class="vacio">Sin notas clínicas.</p>
    @endforelse

    {{-- Recetas --}}
    <h2>Recetas ({{ $patient->prescriptions->count() }})</h2>
    @forelse($patient->prescriptions as $receta)
        <div class="bloque">
            <div class="bloque-titulo">Receta del {{ $receta->prescription_date->format('d/m/Y') }}</div>
            <div class="autoria">{{ $receta->doctor?->user?->name }}@if($receta->doctor?->license_number) · Céd. Prof. {{ $receta->doctor->license_number }}@endif</div>
            @if($receta->diagnosis)<div class="campo"><strong>Diagnóstico:</strong> {{ $receta->diagnosis }}</div>@endif
            @foreach($receta->items as $item)
                <div class="campo">
                    • <strong>{{ $item->medication }}</strong>@if($item->presentacion) — {{ $item->presentacion }}@endif:
                    {{ collect([$item->dosage, $item->via_administracion ? 'vía ' . mb_strtolower($item->via_administracion) : null, $item->frequency, $item->duration])->filter()->implode(', ') }}
                    @if($item->instructions)<span class="vacio"> ({{ $item->instructions }})</span>@endif
                </div>
            @endforeach
            @if($receta->notes)<div class="campo"><strong>Indicaciones:</strong> {{ $receta->notes }}</div>@endif
        </div>
    @empty
        <p class="vacio">Sin recetas.</p>
    @endforelse

    {{-- Consentimientos --}}
    <h2>Consentimientos informados ({{ $patient->consentForms->count() }})</h2>
    @forelse($patient->consentForms as $consentimiento)
        <div class="bloque">
            <div class="bloque-titulo">{{ $consentimiento->title }}@if($consentimiento->procedure_name) — {{ $consentimiento->procedure_name }}@endif</div>
            <div class="campo">
                @if($consentimiento->signed_at)
                    Firmado el {{ $consentimiento->signed_at->format('d/m/Y') }} a las {{ $consentimiento->signed_at->format('H:i') }}@if($consentimiento->testigo_nombre) · Testigo: {{ $consentimiento->testigo_nombre }}@endif
                @else
                    <span class="vacio">Sin firmar</span>
                @endif
            </div>
        </div>
    @empty
        <p class="vacio">Sin consentimientos.</p>
    @endforelse

    {{-- Odontogramas --}}
    @if($patient->odontograms->isNotEmpty())
        <h2>Odontogramas ({{ $patient->odontograms->count() }})</h2>
        @foreach($patient->odontograms as $odontograma)
            <div class="bloque">
                <div class="bloque-titulo">Evaluación del {{ $odontograma->evaluation_date->format('d/m/Y') }}</div>
                <div class="autoria">{{ $odontograma->doctor?->user?->name }} · {{ $odontograma->teeth->count() }} dientes con condición registrada</div>
            </div>
        @endforeach
    @endif
</div>

<div class="pie">
    Documento confidencial: contiene datos personales sensibles de salud. {{ $clinica?->name }} es responsable de su resguardo. Generado con DocFácil.
</div>
</body>
</html>
