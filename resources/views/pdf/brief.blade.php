<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>DocFácil — Brief para consultorios dentales</title>
    <meta name="description" content="Brief de DocFácil: agenda, expedientes, recetas PDF, recordatorios WhatsApp y cobros. Empiece gratis.">

    {{-- OpenGraph --}}
    <meta property="og:title" content="DocFácil — Brief para consultorios dentales">
    <meta property="og:description" content="Agenda, expedientes, recetas PDF, recordatorios WhatsApp y cobros — todo en un solo lugar.">
    <meta property="og:image" content="https://docfacil.tu-app.co/images/og-docfacil.jpg">
    <meta property="og:image:secure_url" content="https://docfacil.tu-app.co/images/og-docfacil.jpg">
    <meta property="og:image:type" content="image/jpeg">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:image:alt" content="DocFácil — Software para consultorios dentales">
    <meta property="og:url" content="{{ url('/brief') }}">
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="DocFácil">
    <meta property="og:locale" content="es_MX">

    {{-- Twitter --}}
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="DocFácil — Brief para consultorios">
    <meta name="twitter:description" content="Agenda, expedientes, recetas PDF, recordatorios WhatsApp y cobros.">
    <meta name="twitter:image" content="https://docfacil.tu-app.co/images/og-docfacil.jpg">

    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}">
    <link rel="canonical" href="{{ url('/brief') }}">

    <style>
        @page { margin: 1cm; }
        * { box-sizing: border-box; }
        body {
            font-family: 'DejaVu Sans', sans-serif;
            color: #1f2937;
            font-size: 10pt;
            line-height: 1.4;
            margin: 0;
            padding: 0;
        }
        .page { page-break-after: always; }
        .page:last-child { page-break-after: auto; }

        /* Header */
        .header { display: flex; align-items: center; justify-content: space-between; border-bottom: 3px solid #14b8a6; padding-bottom: 8px; margin-bottom: 12px; }
        .brand { font-size: 20pt; font-weight: bold; color: #0d9488; letter-spacing: -0.5px; }
        .brand small { font-weight: normal; color: #6b7280; font-size: 9pt; display: block; margin-top: 2px; }
        .header-right { text-align: right; font-size: 8pt; color: #6b7280; }

        /* Hero */
        .hero { background: linear-gradient(135deg, #0d9488 0%, #06b6d4 100%); color: white; padding: 12px 16px; border-radius: 10px; margin-bottom: 10px; }
        .hero h1 { margin: 0 0 4px 0; font-size: 15pt; font-weight: 800; letter-spacing: -0.5px; line-height: 1.15; }
        .hero p { margin: 0; font-size: 9pt; opacity: 0.95; line-height: 1.4; }

        /* Sections */
        h2 { font-size: 12pt; color: #0d9488; margin: 10px 0 6px 0; border-bottom: 1px solid #e5e7eb; padding-bottom: 3px; }
        h3 { font-size: 10pt; font-weight: 700; color: #111827; margin: 0 0 3px 0; }

        /* 2 column grid */
        .row { width: 100%; margin-bottom: 8px; }
        .col-2 { width: 48%; display: inline-block; vertical-align: top; margin-right: 3%; }
        .col-2:last-child { margin-right: 0; }

        /* Pain / Solution boxes */
        .pain-box, .solution-box { padding: 8px 10px; border-radius: 6px; margin-bottom: 6px; font-size: 8.8pt; line-height: 1.35; }
        .pain-box { background: #fef2f2; border-left: 3px solid #ef4444; }
        .solution-box { background: #f0fdfa; border-left: 3px solid #14b8a6; }
        .pain-box strong { color: #b91c1c; }
        .solution-box strong { color: #0d9488; }

        /* Screenshot frame */
        .shot { width: 100%; border-radius: 8px; border: 1px solid #e5e7eb; display: block; }
        .shot-hero { margin: 8px 0 10px 0; box-shadow: 0 2px 8px rgba(13,148,136,0.12); }
        .shot-cap { font-size: 8pt; color: #6b7280; text-align: center; margin-top: 2px; font-style: italic; }

        /* Feature block grande (página 2) */
        .feat-big { background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 10px; padding: 12px 14px; margin-bottom: 14px; page-break-inside: avoid; }
        .feat-big .feat-num { display: inline-block; background: #0d9488; color: white; font-weight: bold; font-size: 9pt; padding: 2px 8px; border-radius: 5px; margin-bottom: 4px; }
        .feat-big h3 { margin: 0 0 4px 0; font-size: 12pt; color: #111827; }
        .feat-big p { margin: 0 0 8px 0; font-size: 9.5pt; color: #4b5563; line-height: 1.45; }
        .feat-big .shot { margin-top: 4px; }

        /* 2x2 screenshot grid */
        .shot-grid { width: 100%; border-collapse: separate; border-spacing: 6px; margin: 6px 0 10px 0; }
        .shot-grid td { width: 50%; vertical-align: top; padding: 0; }
        .shot-card { background: white; border: 1px solid #e5e7eb; border-radius: 8px; padding: 6px; }
        .shot-card img { width: 100%; display: block; border-radius: 4px; }
        .shot-card .title { font-size: 9pt; font-weight: 700; color: #0d9488; margin: 4px 0 1px 0; }
        .shot-card .desc { font-size: 7.8pt; color: #6b7280; line-height: 1.3; }

        /* Features grid */
        .features { width: 100%; border-collapse: collapse; margin: 6px 0; }
        .features td { padding: 5px 6px; vertical-align: top; font-size: 8.8pt; border-bottom: 1px solid #f3f4f6; width: 50%; line-height: 1.3; }
        .features td strong { color: #0d9488; display: block; margin-bottom: 1px; font-size: 9pt; }
        .icon { display: inline-block; width: 12px; color: #14b8a6; font-weight: bold; }

        /* Stats */
        .stats { width: 100%; background: #f9fafb; border-radius: 8px; padding: 10px; margin: 8px 0; }
        .stats td { text-align: center; padding: 3px 2px; }
        .stats .num { font-size: 16pt; font-weight: 800; color: #0d9488; line-height: 1; }
        .stats .label { font-size: 8pt; color: #6b7280; margin-top: 2px; }

        /* Pricing */
        .pricing { width: 100%; border-collapse: collapse; margin: 8px 0; }
        .pricing th { background: #f0fdfa; color: #0d9488; padding: 6px; font-size: 9pt; text-align: left; border-bottom: 2px solid #14b8a6; }
        .pricing td { padding: 6px; font-size: 8.8pt; border-bottom: 1px solid #e5e7eb; }
        .pricing .popular { background: #fff7ed; }
        .pricing .popular td:first-child strong { color: #ea580c; }
        .price { font-weight: bold; color: #111827; font-size: 10pt; }
        .price-free { color: #059669; font-weight: bold; }

        /* CTA box */
        .cta-box { background: linear-gradient(135deg, #0d9488 0%, #06b6d4 100%); color: white; padding: 14px 16px; border-radius: 10px; margin-top: 10px; }
        .cta-box h3 { color: white; margin: 0 0 3px 0; font-size: 13pt; }
        .cta-box p { margin: 0; font-size: 9pt; opacity: 0.95; }
        .cta-grid { width: 100%; margin-top: 8px; }
        .cta-grid td { vertical-align: middle; }
        .cta-grid .qr { width: 100px; text-align: center; }
        .cta-grid .qr img { width: 94px; height: 94px; background: white; padding: 3px; border-radius: 5px; }
        .cta-grid .info { padding-left: 12px; font-size: 9pt; }
        .cta-grid .info strong { display: block; color: white; font-size: 10pt; margin-bottom: 2px; }
        .cta-grid .info a { color: white; text-decoration: none; }

        /* Badge row */
        .badges { margin: 6px 0; font-size: 8pt; color: #6b7280; }
        .badge { display: inline-block; background: #f0fdfa; color: #0d9488; padding: 3px 8px; border-radius: 10px; margin-right: 4px; font-weight: 600; }

        /* Footer */
        .footer { border-top: 1px solid #e5e7eb; margin-top: 10px; padding-top: 6px; font-size: 7.5pt; color: #9ca3af; text-align: center; }
        .footer a { color: #0d9488; text-decoration: none; }
    </style>
</head>
<body>

{{-- ============================================================ --}}
{{-- PÁGINA 1 — PROBLEMA, SOLUCIÓN Y PROOF VISUAL                  --}}
{{-- ============================================================ --}}
<div class="page">
    <div class="header">
        <div>
            <div class="brand">DocFácil</div>
            <small>Software para consultorios dentales</small>
        </div>
        <div class="header-right">
            Brief 2026<br>
            docfacil.tu-app.co
        </div>
    </div>

    <div class="hero">
        <h1>Su consultorio dental, en orden y en un solo lugar</h1>
        <p>Agenda, expediente, odontograma, recetas y cobros. Los recordatorios salen de su propio WhatsApp a 1 clic: DocFácil escribe el mensaje y usted da enviar.</p>
    </div>

    <img src="{{ $screens['dashboard'] }}" alt="Escritorio DocFácil" class="shot shot-hero">
    <p class="shot-cap">Su día en un vistazo: ingresos del mes, próximas citas y lo que pasó hoy.</p>

    <div class="row">
        <div class="col-2">
            <h2 style="margin-top:4px;">Lo que hoy cuesta trabajo</h2>
            <div class="pain-box"><strong>Citas que no llegan</strong><br>Al paciente se le olvidó y nadie le recordó.</div>
            <div class="pain-box"><strong>Tiempo en papeleo</strong><br>Buscar expedientes, escribir recetas a mano, llamar a confirmar.</div>
            <div class="pain-box"><strong>Cobros que se olvidan</strong><br>"Le pago después" que nunca regresó. Trabajo hecho sin cobrar.</div>
            <div class="pain-box"><strong>Decidir a ojo</strong><br>No sabe qué servicio le deja más ni cuánto le deben.</div>
        </div>
        <div class="col-2">
            <h2 style="margin-top:4px;">Lo que hace DocFácil</h2>
            <div class="solution-box"><strong>Recordatorio a 1 clic</strong><br>Abre su WhatsApp con el mensaje escrito; usted da enviar. El paciente confirma con un link.</div>
            <div class="solution-box"><strong>Todo en un solo lugar</strong><br>Agenda, expediente, odontograma y recetas PDF con su cédula, ligados a cada paciente.</div>
            <div class="solution-box"><strong>Cobro el mismo día</strong><br>Registra el cobro al terminar. Si queda saldo, abre su WhatsApp con el recordatorio escrito.</div>
            <div class="solution-box"><strong>Sabe cuánto gana cada día</strong><br>Ingresos, pendientes y gastos del mes al entrar, sin Excel.</div>
        </div>
    </div>

    <div class="stats">
        <table style="width:100%;">
            <tr>
                <td><div class="num">$0</div><div class="label">plan Free, para siempre</div></td>
                <td><div class="num">$499</div><div class="label">al mes, plan Básico</div></td>
                <td><div class="num">30 días</div><div class="label">garantía de devolución</div></td>
                <td><div class="num">15 días</div><div class="label">prueba gratis sin tarjeta</div></td>
            </tr>
        </table>
    </div>

    <div class="footer">
        Sigue en la página siguiente → Cómo se ve trabajando DocFácil en cada área del consultorio
    </div>
</div>

{{-- ============================================================ --}}
{{-- PÁGINA 2 — 2 FEATURES CLAVE CON SCREENSHOT GRANDE             --}}
{{-- ============================================================ --}}
<div class="page">
    <div class="header">
        <div>
            <div class="brand">DocFácil</div>
            <small>Dos funciones que marcan la diferencia</small>
        </div>
        <div class="header-right">
            Brief 2026<br>
            docfacil.tu-app.co
        </div>
    </div>

    <h2 style="margin-top:0;">Dos funciones que cambian el consultorio</h2>

    <div class="feat-big">
        <span class="feat-num">01</span>
        <h3>Odontograma interactivo</h3>
        <p>Diagrama dental FDI. Usted hace clic en el diente o en la cara, elige el estado y se guarda. Lo imprime en PDF para explicarle al paciente su tratamiento.</p>
        <img src="{{ $screens['odontograma'] }}" alt="Odontograma interactivo" class="shot">
    </div>

    <div class="feat-big">
        <span class="feat-num">02</span>
        <h3>Recetas PDF profesionales</h3>
        <p>Con su nombre, especialidad, cédula, datos del consultorio y espacio para su firma. Se descargan en PDF y el paciente también las ve en su portal.</p>
        <img src="{{ $screens['recetas'] }}" alt="Recetas PDF" class="shot">
    </div>

    <div class="badges" style="text-align:center; margin-top:10px;">
        <span class="badge">✓ Hecho en México</span>
        <span class="badge">✓ Datos en la nube</span>
        <span class="badge">✓ Cifrado TLS</span>
        <span class="badge">✓ Backups diarios</span>
        <span class="badge">✓ Soporte en español</span>
    </div>

    <div class="footer">
        Sigue en la página siguiente → Funciones, precios y cómo empezar
    </div>
</div>

{{-- ============================================================ --}}
{{-- PÁGINA 3 — FEATURES, PRECIOS Y CTA                            --}}
{{-- ============================================================ --}}
<div class="page">
    <div class="header">
        <div>
            <div class="brand">DocFácil</div>
            <small>Pensado para consultorios dentales</small>
        </div>
        <div class="header-right">
            Brief 2026<br>
            docfacil.tu-app.co
        </div>
    </div>

    <h2 style="margin-top:0;">Todo lo que incluye</h2>
    <table class="features">
        <tr>
            <td><span class="icon">✓</span> <strong>Agenda de citas</strong> Calendario visual, varios doctores, arrastrar y soltar</td>
            <td><span class="icon">✓</span> <strong>Recordatorios WhatsApp</strong> A 1 clic desde su WhatsApp; usted da enviar</td>
        </tr>
        <tr>
            <td><span class="icon">✓</span> <strong>Expediente clínico</strong> Motivo, diagnóstico CIE-10, tratamiento, signos vitales, alergias</td>
            <td><span class="icon">✓</span> <strong>Recetas PDF</strong> Con cédula y espacio para firma</td>
        </tr>
        <tr>
            <td><span class="icon">✓</span> <strong>Odontograma interactivo</strong> Diagrama FDI, se imprime en PDF</td>
            <td><span class="icon">✓</span> <strong>Cobro por WhatsApp</strong> Monto y saldo, a 1 clic</td>
        </tr>
        <tr>
            <td><span class="icon">✓</span> <strong>Check-in con QR</strong> Sin papeleo, el paciente escanea</td>
            <td><span class="icon">✓</span> <strong>Consentimientos</strong> Firmados en pantalla, con fecha y hora (Pro)</td>
        </tr>
        <tr>
            <td><span class="icon">✓</span> <strong>Portal del paciente</strong> Sus citas, recetas y pagos</td>
            <td><span class="icon">✓</span> <strong>Dashboard con gráficas</strong> Ingresos, citas, alertas</td>
        </tr>
        <tr>
            <td><span class="icon">✓</span> <strong>Alertas inteligentes</strong> Pacientes sin visita, pagos vencidos (Pro)</td>
            <td><span class="icon">✓</span> <strong>Varios doctores</strong> Producción por doctor (Pro hasta 3, Clínica ilimitados)</td>
        </tr>
    </table>

    <h2>Planes y precios</h2>

    <div style="background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%); border: 1px solid #f59e0b; border-radius: 6px; padding: 6px 10px; margin-bottom: 6px; font-size: 8.5pt;">
        <strong style="color: #92400e;">💡 Pague anual y ahorre 2 meses</strong> <span style="color:#78350f;">— el plan anual cuesta solo 10 meses (16.7% de descuento).</span>
    </div>

    <table class="pricing">
        <thead>
            <tr>
                <th style="width:16%;">Plan</th>
                <th style="width:20%;">Mensual</th>
                <th style="width:22%;">Anual (2 meses gratis)</th>
                <th>Ideal para</th>
            </tr>
        </thead>
        <tbody>
            @foreach(\App\Support\LoQueTraeCadaPlan::planes() as $p)
            <tr @if($p['popular']) class="popular" @endif>
                <td><strong>{{ $p['name'] }}{{ $p['popular'] ? ' ★' : '' }}</strong></td>
                @if($p['annual'] === 0)
                <td class="price-free">$0 / mes</td>
                <td style="color:#6b7280;">—</td>
                @else
                <td class="price">${{ $p['price'] }} / mes</td>
                <td class="price" style="color:#059669;">${{ number_format($p['annual']) }} / año</td>
                @endif
                <td>{{ $p['limits'] }}<br><span style="color:#6b7280;">{{ $p['lead'] ? $p['lead'] . ' ' : '' }}{{ implode(' · ', array_slice($p['features'], 0, 3)) }}</span></td>
            </tr>
            @endforeach
        </tbody>
    </table>
    <p style="font-size:8.5pt; color:#6b7280; margin:3px 0 0 0;">15 días gratis con todas las funciones. Sin tarjeta. Garantía de 30 días en su primer pago. Precios en MXN.</p>

    <div class="cta-box">
        <h3>Empiece gratis</h3>
        <p>Escanee el QR o visite <strong>docfacil.tu-app.co</strong> y cree su cuenta sin tarjeta.</p>
        <table class="cta-grid">
            <tr>
                <td class="qr">
                    <img src="{{ $qrDataUri }}" alt="QR registro">
                </td>
                <td class="info">
                    <strong>Omar Lerma · Fundador</strong>
                    <span style="display:inline-block; width:12px; font-weight:bold;">☎</span> <a href="{{ $whatsappLink }}">668 249 3398</a> (WhatsApp)<br>
                    <span style="display:inline-block; width:12px; font-weight:bold;">✉</span> <a href="mailto:contacto@docfacil.com">contacto@docfacil.com</a><br>
                    <span style="display:inline-block; width:12px; font-weight:bold;">⌂</span> <a href="{{ url('/') }}">docfacil.tu-app.co</a><br>
                    <span style="opacity:0.9;">Demo en vivo · Onboarding gratuito</span>
                </td>
            </tr>
        </table>
    </div>

    <div class="footer">
        DocFácil © {{ date('Y') }} · Software para consultorios dentales en México · <a href="{{ url('/') }}">docfacil.tu-app.co</a>
    </div>
</div>

</body>
</html>
