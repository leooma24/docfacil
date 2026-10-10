{{-- Brief comercial de DocFácil para enseñarle a un dentista (12-oct-2026).
     Dos páginas que sirven en la web (/brief) y en PDF (/brief.pdf, DomPDF):
     por eso las columnas son tablas y no flex/grid. Solo lo que existe hoy
     (MaterialesDeVentaHonestosTest), capturas del consultorio de práctica y
     los planes de LoQueTraeCadaPlan. --}}
@php
    $web = $mode === 'web';
    $nombres = ['Free' => 'Gratis'];
@endphp
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>DocFácil · Brief para consultorios dentales</title>
    <meta name="description" content="Agenda, expediente y cobros del consultorio dental en un solo lugar. Vea la demo y pruébelo 15 días sin tarjeta.">
    <meta property="og:title" content="DocFácil · Brief para consultorios dentales">
    <meta property="og:description" content="Agenda, expediente y cobros del consultorio dental en un solo lugar.">
    <meta property="og:image" content="https://docfacil.tu-app.co/images/og-docfacil.jpg">
    <meta property="og:url" content="{{ url('/brief') }}">
    <meta property="og:type" content="website">
    <meta property="og:locale" content="es_MX">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}">
    <link rel="canonical" href="{{ url('/brief') }}">
    <style>
        @page { margin: 1.1cm 1.2cm; }
        * { box-sizing: border-box; }
        body { font-family: 'DejaVu Sans', sans-serif; color: #0f172a; font-size: 9.2pt; line-height: 1.38; margin: 0; padding: 0; background: #fff; }
        .hoja { page-break-after: always; }
        .hoja:last-child { page-break-after: auto; }
        table { border-collapse: collapse; width: 100%; }
        td { vertical-align: top; }
        img { max-width: 100%; }

        .cabeza td { vertical-align: middle; padding-bottom: 8px; border-bottom: 2px solid #0d9488; }
        .marca { font-size: 15pt; font-weight: bold; color: #0d9488; }
        
        .cabeza-der { text-align: right; font-size: 8pt; color: #64748b; }

        h1 { font-size: 17pt; line-height: 1.15; margin: 10px 0 4px; color: #0f172a; letter-spacing: -0.3px; }
        .sub { font-size: 10pt; color: #475569; margin: 0 0 9px; }
        h2 { font-size: 11pt; color: #0f766e; margin: 10px 0 6px; }
        .etiqueta { font-size: 7.5pt; font-weight: bold; color: #64748b; text-transform: uppercase; letter-spacing: 0.6px; }

        .captura { border: 1px solid #e2e8f0; border-radius: 8px; padding: 4px; background: #f8fafc; }
        .captura img { display: block; width: 100%; border-radius: 6px; }
        .pie-foto { font-size: 8pt; color: #64748b; margin: 4px 2px 0; }

        .caja { border: 1px solid #e2e8f0; border-radius: 8px; padding: 7px 10px; }
        .dolor { font-size: 8.3pt; color: #b45309; font-weight: bold; margin-bottom: 3px; }
        .caja h3 { font-size: 10pt; margin: 0 0 3px; color: #0f172a; }
        .caja p { margin: 0; font-size: 8.4pt; line-height: 1.32; color: #334155; }

        .planes td { border: 1px solid #e2e8f0; padding: 7px 8px; width: 25%; }
        .planes .destacado { border: 2px solid #0d9488; background: #f0fdfa; }
        .plan-nombre { font-size: 10.5pt; font-weight: bold; }
        .plan-precio { font-size: 15pt; font-weight: bold; color: #0f766e; }
        .plan-precio small { font-size: 8pt; color: #64748b; font-weight: normal; }
        .plan-limite { font-size: 8pt; color: #334155; font-weight: bold; margin: 3px 0 4px; }
        .planes ul { margin: 0; padding-left: 13px; font-size: 8pt; color: #334155; }
        .planes li { margin-bottom: 2px; }
        .recomendado { font-size: 7pt; font-weight: bold; color: #fff; background: #0d9488; border-radius: 4px; padding: 1px 6px; }

        .faq td { width: 50%; padding: 0 8px 6px 0; }
        .faq strong { display: block; font-size: 9pt; margin-bottom: 1px; }
        .faq span { font-size: 8.6pt; color: #475569; }

        .probar { background: #0f172a; color: #fff; border-radius: 10px; padding: 12px 14px; }
        .probar h2 { color: #5eead4; margin-top: 0; }
        .probar p, .probar li { color: #e2e8f0; font-size: 9pt; margin: 0 0 4px; }
        .probar ol { margin: 4px 0 0; padding-left: 16px; }
        .acceso { background: #1e293b; border-radius: 6px; padding: 7px 9px; font-size: 9pt; margin: 6px 0; }
        .acceso b { color: #5eead4; }
        .contacto { margin-top: 10px; font-size: 8.6pt; color: #475569; border-top: 1px solid #e2e8f0; padding-top: 8px; }
        .contacto b { color: #0f172a; }
        a { color: #0f766e; }

        @if($web)
        body { background: #f1f5f9; }
        .hoja { max-width: 860px; margin: 24px auto; background: #fff; padding: 34px 38px; border-radius: 16px; box-shadow: 0 10px 30px -18px rgba(15,23,42,.35); }
        .barra { max-width: 860px; margin: 18px auto 0; text-align: right; }
        .barra a { display: inline-block; background: #0d9488; color: #fff; text-decoration: none; font-weight: bold; padding: 10px 16px; border-radius: 8px; font-size: 10pt; margin-left: 6px; }
        .barra a.claro { background: #fff; color: #0f766e; border: 1px solid #99f6e4; }
        @media (max-width: 700px) {
            .hoja { margin: 12px; padding: 20px 16px; }
            .apila, .apila tbody, .apila tr, .apila td { display: block; width: 100% !important; }
            .apila td { padding: 0 0 10px 0 !important; }
            .barra { margin: 12px 12px 0; text-align: left; }
            .barra a { margin: 0 6px 6px 0; }
            h1 { font-size: 16pt; }
            .captura { width: 100% !important; }
        }
        @endif
    </style>
</head>
<body>
@if($web)
    <div class="barra">
        <a class="claro" href="{{ url('/brief.pdf?view=1') }}">Ver en PDF</a>
        <a href="{{ $demoUrl }}">Abrir la demo</a>
    </div>
@endif

{{-- ── Página 1: qué es y qué le resuelve ─────────────────────────── --}}
<div class="hoja">
    <table class="cabeza"><tr>
        <td class="marca">DocFácil</td>
        <td class="cabeza-der">Brief para consultorios dentales<br>docfacil.tu-app.co</td>
    </tr></table>

    <h1>La agenda, el expediente y los cobros de su consultorio, en un solo lugar.</h1>
    <p class="sub">Para el dentista que trabaja solo o con su asistente. También lleva el laboratorio, la caja del día y el corte del mes. Se usa desde la computadora y el celular, sin instalar nada.</p>

    <div class="captura" style="width:78%;margin:0 auto;"><img src="{{ $img('atender') }}" alt="Lo que hay que atender hoy"></div>
    <p class="pie-foto" style="text-align:center;">Al entrar ve lo que hay que atender hoy, lo urgente primero, cada cosa con su botón.</p>

    <h2>Lo que le resuelve</h2>
    <table class="apila">
        <tr>
            <td style="width:50%;padding:0 6px 8px 0;">
                <div class="caja">
                    <div class="dolor">"Se le olvidó la cita"</div>
                    <h3>Recordatorios de mañana, uno tras otro</h3>
                    <p>Toca el botón y se abre su WhatsApp con el mensaje ya escrito; usted da enviar. El paciente confirma con una liga.</p>
                </div>
            </td>
            <td style="width:50%;padding:0 0 8px 6px;">
                <div class="caja">
                    <div class="dolor">"Le pago después"</div>
                    <h3>Le deben: quién, cuánto y desde cuándo</h3>
                    <p>Cobra con abonos, lleva mensualidades de ortodoncia y le recuerda por WhatsApp a quien paga, aunque sea la mamá.</p>
                </div>
            </td>
        </tr>
        <tr>
            <td style="width:50%;padding:0 6px 0 0;">
                <div class="caja">
                    <div class="dolor">"¿Qué le hice la vez pasada?"</div>
                    <h3>Al abrir la cita, todo a la vista</h3>
                    <p>Alergias y anticoagulantes en rojo, lo último que se le hizo en cada diente y lo que falta del presupuesto. Odontograma y recetas en PDF.</p>
                </div>
            </td>
            <td style="width:50%;padding:0 0 0 6px;">
                <div class="caja">
                    <div class="dolor">"Lo voy a pensar"</div>
                    <h3>Presupuestos que no se quedan en el cajón</h3>
                    <p>El paciente lo ve y lo acepta desde su celular. Los que no contestan aparecen en Pendientes con su recordatorio.</p>
                </div>
            </td>
        </tr>
    </table>

    <table class="apila" style="margin-top:9px;"><tr>
        <td style="width:50%;padding-right:5px;">
            <div class="captura"><img src="{{ $img('consulta') }}" alt="La consulta con alergias y lo último del diente"></div>
            <p class="pie-foto">Al abrir la cita: alergias en rojo y "Lo último en cada diente".</p>
        </td>
        <td style="width:50%;padding-left:5px;">
            <div class="captura"><img src="{{ $img('le-deben') }}" alt="Le deben"></div>
            <p class="pie-foto">Le deben: Cobrar pregunta cuánto trae; Recordarle abre su WhatsApp.</p>
        </td>
    </tr></table>

</div>

{{-- ── Página 2: planes, preguntas y cómo probarlo ─────────────────── --}}
<div class="hoja">
    <table class="cabeza"><tr>
        <td class="marca">DocFácil</td>
        <td class="cabeza-der">Planes, preguntas y cómo probarlo</td>
    </tr></table>

    <h2>Planes</h2>
    <table class="planes apila"><tr>
        @foreach($planes as $plan)
            <td class="{{ $plan['popular'] ? 'destacado' : '' }}">
                <div class="plan-nombre">{{ $nombres[$plan['name']] ?? $plan['name'] }} @if($plan['popular'])<span class="recomendado">Recomendado</span>@endif</div>
                <div class="plan-precio">${{ $plan['price'] }} <small>al mes</small></div>
                <div class="etiqueta" style="text-transform:none;letter-spacing:0;">{{ $plan['ideal'] }}</div>
                <div class="plan-limite">{{ $plan['limits'] }}</div>
                <ul>
                    @if($plan['lead'])<li><b>{{ rtrim($plan['lead'], ':') }}</b></li>@endif
                    @foreach(array_slice($plan['features'], 0, 4) as $f)<li>{{ $f }}</li>@endforeach
                </ul>
            </td>
        @endforeach
    </tr></table>
    <p class="pie-foto">Precios en pesos mexicanos. Pagando anual son 10 meses. 15 días de prueba con todo, sin tarjeta, y garantía de 30 días en su primer pago.</p>

    <h2>Lo que siempre preguntan</h2>
    <table class="faq apila">
        <tr>
            <td><strong>¿Los mensajes salen solos?</strong><span>No. Se abre su WhatsApp con el mensaje ya escrito y usted da enviar: sale de su número, el que sus pacientes conocen.</span></td>
            <td><strong>¿Hace facturas?</strong><span>No timbra facturas. Deja anotado quién pidió factura y la hace su contador.</span></td>
        </tr>
        <tr>
            <td><strong>¿Y mis pacientes de antes?</strong><span>Los sube desde un Excel, o los va capturando conforme vienen.</span></td>
            <td><strong>¿Y si dejo de pagar?</strong><span>Nada se borra: su cuenta pasa al plan Gratis y puede bajar todos sus datos cuando quiera.</span></td>
        </tr>
    </table>

    <div class="probar">
        <table class="apila"><tr>
            <td style="width:72%;padding-right:12px;">
                <h2>Véalo funcionando</h2>
                <p>Es un consultorio de práctica con pacientes inventados. Puede mover lo que quiera: se reinicia cada día.</p>
                <div class="acceso">
                    <b>{{ str_replace(['https://', 'http://'], '', $demoUrl) }}</b><br>
                    Correo: <b>demo@docfacil.com</b> &nbsp;·&nbsp; Contraseña: <b>demo2026</b>
                </div>
                <p>Tres cosas para probar:</p>
                <ol>
                    <li>En el Escritorio, vea "Lo que hay que atender".</li>
                    <li>En Citas, abra una cita de hoy o de mañana: alergias y lo último del diente.</li>
                    <li>En "Le deben", toque Cobrar y registre un abono.</li>
                </ol>
            </td>
            <td style="width:28%;text-align:center;">
                <img src="{{ $qrDataUri }}" alt="Crear cuenta" style="width:96px;height:96px;background:#fff;border-radius:6px;padding:4px;">
                <p style="font-size:8pt;margin-top:4px;">Su cuenta: 15 días con todo, sin tarjeta</p>
            </td>
        </tr></table>
        <p style="margin:8px 0 0;padding-top:8px;border-top:1px solid #334155;font-size:8.6pt;">
            <b style="color:#fff;">Omar Lerma</b>, fundador de DocFácil · Los Mochis, Sinaloa · WhatsApp <a href="{{ $whatsappLink }}" style="color:#5eead4;"><b>668 249 3398</b></a> · leooma24@gmail.com
        </p>
    </div>

</div>
</body>
</html>
