@php
    $domicilio = collect([$clinic->address, $clinic->city, $clinic->state])->filter()->implode(', ');
    $contacto = collect([
        $clinic->phone ? 'teléfono ' . $clinic->phone : null,
        $clinic->email ? 'correo ' . $clinic->email : null,
    ])->filter()->implode(' o ');
    $version = \Carbon\Carbon::parse(\App\Support\AvisoDePrivacidad::VERSION)->translatedFormat('j \d\e F \d\e Y');
@endphp
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex">
    <title>Aviso de privacidad · {{ $clinic->name }}</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: -apple-system, "Segoe UI", Arial, sans-serif; background: linear-gradient(135deg, #f0fdfa 0%, #ffffff 100%); color: #1f2937; padding: 20px 16px 40px; line-height: 1.6; }
        .page { max-width: 680px; margin: 0 auto; }
        .card { background: #fff; border-radius: 18px; padding: 28px 24px; box-shadow: 0 16px 36px -10px rgba(13,148,136,.15); border: 1px solid rgba(13,148,136,.1); }
        h1 { font-size: 22px; color: #0f766e; line-height: 1.25; }
        .meta { font-size: 13px; color: #6b7280; margin-top: 4px; }
        h2 { font-size: 15px; color: #0f172a; margin: 22px 0 6px; }
        p, li { font-size: 14px; color: #374151; }
        ul { padding-left: 20px; }
        li { margin-bottom: 4px; }
        .sensible { background: #fffbeb; border-left: 3px solid #f59e0b; padding: 10px 12px; border-radius: 8px; margin-top: 8px; }
        .aceptar { margin-top: 26px; padding: 18px; background: #f0fdfa; border: 1px solid #99f6e4; border-radius: 14px; }
        .aceptar label { display: flex; gap: 10px; align-items: flex-start; font-weight: 600; font-size: 14px; cursor: pointer; }
        .aceptar input { width: 20px; height: 20px; min-width: 20px; margin-top: 2px; accent-color: #0d9488; }
        .aceptar button { margin-top: 14px; width: 100%; padding: 13px; border: none; border-radius: 12px; background: linear-gradient(135deg, #14b8a6, #0d9488); color: #fff; font-size: 15px; font-weight: 700; cursor: pointer; }
        .error { color: #dc2626; font-size: 13px; margin-top: 6px; }
        .listo { margin-top: 26px; padding: 14px 16px; background: #ecfdf5; border: 1px solid #a7f3d0; border-radius: 12px; color: #065f46; font-size: 14px; }
        .pie { text-align: center; font-size: 11px; color: #9ca3af; margin-top: 18px; }
    </style>
</head>
<body>
<div class="page">
    <div class="card">
        <h1>Aviso de privacidad de {{ $clinic->name }}</h1>
        <p class="meta">Versión del {{ $version }}</p>

        <h2>1. Quién cuida tus datos</h2>
        <p>
            <strong>{{ $clinic->name }}</strong>@if($domicilio), con domicilio en {{ $domicilio }}@endif, es responsable de tus datos personales.
            @if($contacto) Para cualquier tema de tus datos puedes contactarnos por {{ $contacto }}.@endif
        </p>

        <h2>2. Qué datos usamos</h2>
        <ul>
            <li>De identificación y contacto: nombre, teléfono, correo, fecha de nacimiento y domicilio.</li>
            <li>De tu atención: motivo de consulta, antecedentes, alergias, tipo de sangre, signos vitales, diagnósticos, tratamientos, recetas, odontograma, imágenes y consentimientos firmados.</li>
            <li>De tus pagos: montos, fechas y forma de pago.</li>
        </ul>
        <p class="sensible">Los datos de tu salud son <strong>datos sensibles</strong>. Solo los tratamos con tu consentimiento expreso, que nos das al aceptar este aviso.</p>

        <h2>3. Para qué los usamos</h2>
        <ul>
            <li>Atenderte y darte seguimiento.</li>
            <li>Integrar y conservar tu expediente clínico, como lo piden las normas oficiales mexicanas (NOM-004-SSA3-2012 y, en odontología, NOM-013-SSA2-2015).</li>
            <li>Agendar tus citas y recordártelas por WhatsApp o correo.</li>
            <li>Registrar tus pagos y darte tus comprobantes.</li>
        </ul>
        <p>No usamos tus datos para publicidad ni los vendemos.</p>

        <h2>4. Con quién los compartimos</h2>
        <ul>
            <li><strong>DocFácil</strong>, la plataforma donde llevamos tu expediente y nuestra agenda. Solo guarda y procesa tus datos por encargo nuestro y no los usa para nada más. Sus servidores están en Estados Unidos.</li>
            <li>Laboratorios, gabinetes u otros especialistas, solo cuando hace falta para tu diagnóstico o tratamiento.</li>
            <li>Autoridades, solo cuando una ley nos obliga.</li>
        </ul>

        <h2>5. Tus derechos</h2>
        <p>
            Puedes pedirnos acceder a tus datos, corregirlos, cancelarlos u oponerte a que los usemos (derechos ARCO), y retirar tu consentimiento.
            @if($contacto) Escríbenos o llámanos por {{ $contacto }}@else Pídelo en recepción @endif
            con tu nombre completo, una identificación y lo que necesitas.
            Te respondemos en un máximo de 20 días hábiles y, si procede, lo hacemos efectivo en los 15 días hábiles siguientes. Es gratuito, salvo el costo de copias o envíos.
        </p>
        <p style="margin-top:8px;">Ten en cuenta que la ley nos obliga a conservar tu expediente clínico al menos 5 años desde tu última consulta, así que durante ese tiempo no podemos borrarlo, pero sí dejar de usarlo para otra cosa.</p>

        <h2>6. Cambios a este aviso</h2>
        <p>Si cambia, publicaremos la nueva versión en esta misma página y te pediremos aceptarla de nuevo.</p>

        <h2>7. Autoridad</h2>
        <p>Si crees que no cuidamos bien tus datos, puedes acudir a la Secretaría Anticorrupción y Buen Gobierno, la autoridad en protección de datos personales.</p>

        @if($paciente)
            @if(\App\Support\AvisoDePrivacidad::aceptoElVigente($paciente))
                <div class="listo">
                    Gracias, {{ $paciente->first_name }}. Ya aceptaste este aviso el {{ $paciente->aviso_privacidad_aceptado_at->format('d/m/Y') }}.
                </div>
            @else
                <form class="aceptar" method="POST" action="{{ request()->fullUrl() }}">
                    @csrf
                    <label>
                        <input type="checkbox" name="acepta_aviso" value="1" required>
                        <span>Soy {{ $paciente->first_name }} {{ $paciente->last_name }}, leí el aviso y acepto que {{ $clinic->name }} trate mis datos, incluidos los de salud, como aquí se describe.</span>
                    </label>
                    @error('acepta_aviso')
                        <div class="error">{{ $message }}</div>
                    @enderror
                    <button type="submit">Aceptar el aviso</button>
                </form>
            @endif
        @endif
    </div>

    <p class="pie">Aviso elaborado con la plantilla de DocFácil. {{ $clinic->name }} es responsable de tus datos.</p>
</div>
</body>
</html>
