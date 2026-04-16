<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>DocFácil — Brief para consultorios médicos y dentales</title>
    <meta name="description" content="Brief de DocFácil: agenda, expedientes, recetas PDF, recordatorios WhatsApp y cobros. Empieza gratis en 2 minutos.">

    {{-- OpenGraph --}}
    <meta property="og:title" content="DocFácil — Brief para consultorios médicos y dentales">
    <meta property="og:description" content="Agenda, expedientes, recetas PDF, recordatorios WhatsApp y cobros — todo en un solo lugar.">
    <meta property="og:image" content="https://docfacil.tu-app.co/images/og-image.png">
    <meta property="og:image:secure_url" content="https://docfacil.tu-app.co/images/og-image.png">
    <meta property="og:image:type" content="image/png">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:image:alt" content="DocFácil — Software para consultorios médicos y dentales">
    <meta property="og:url" content="{{ url('/brief') }}">
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="DocFácil">
    <meta property="og:locale" content="es_MX">

    {{-- Twitter --}}
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="DocFácil — Brief para consultorios">
    <meta name="twitter:description" content="Agenda, expedientes, recetas PDF, recordatorios WhatsApp y cobros.">
    <meta name="twitter:image" content="https://docfacil.tu-app.co/images/og-image.png">

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
        .hero { background: linear-gradient(135deg, #0d9488 0%, #06b6d4 100%); color: white; padding: 14px 18px; border-radius: 10px; margin-bottom: 12px; }
        .hero h1 { margin: 0 0 4px 0; font-size: 17pt; font-weight: 800; letter-spacing: -0.5px; }
        .hero p { margin: 0; font-size: 9.5pt; opacity: 0.95; }

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
            <small>Software para consultorios médicos y dentales</small>
        </div>
        <div class="header-right">
            Brief 2026<br>
            docfacil.tu-app.co
        </div>
    </div>

    <div class="hero">
        <h1>Tu consultorio, organizado y al día</h1>
        <p>Agenda, expedientes, recetas PDF, recordatorios por WhatsApp y cobros — todo en un solo lugar. Olvídate del papel y del Excel.</p>
    </div>

    <img src="{{ $screens['dashboard'] }}" alt="Escritorio DocFácil" class="shot shot-hero">
    <p class="shot-cap">Así se ve tu escritorio al entrar — ingresos del mes, próximas citas y accesos rápidos.</p>

    <div class="row">
        <div class="col-2">
            <h2 style="margin-top:4px;">Lo que vives hoy</h2>
            <div class="pain-box"><strong>Agenda en papel o Excel</strong><br>Pierdes citas, no puedes buscar rápido, y cada cambio cuesta tiempo.</div>
            <div class="pain-box"><strong>Pacientes que no llegan</strong><br>El 30% no se presenta. Son consultas perdidas que no vuelven.</div>
            <div class="pain-box"><strong>Recetas a mano, letra ilegible</strong><br>Sin copia, sin respaldo, riesgo de error.</div>
            <div class="pain-box"><strong>No sabes cuánto ganas</strong><br>Sin reportes, sin control de cobros pendientes.</div>
        </div>
        <div class="col-2">
            <h2 style="margin-top:4px;">Cómo lo resuelve DocFácil</h2>
            <div class="solution-box"><strong>Agenda inteligente en la nube</strong><br>Multi-doctor, arrastra y suelta, desde cualquier dispositivo.</div>
            <div class="solution-box"><strong>Recordatorios WhatsApp — auto + 1 clic</strong><br>Automáticos 24h y 2h antes, o manuales con un clic. Reduce inasistencias 40%.</div>
            <div class="solution-box"><strong>Recetas PDF profesionales</strong><br>Con cédula y firma. El paciente las recibe por WhatsApp.</div>
            <div class="solution-box"><strong>Reportes en tiempo real</strong><br>Ingresos, pendientes, pacientes activos, alertas.</div>
        </div>
    </div>

    <div class="stats">
        <table style="width:100%;">
            <tr>
                <td><div class="num">500+</div><div class="label">consultorios</div></td>
                <td><div class="num">15K+</div><div class="label">citas gestionadas</div></td>
                <td><div class="num">40%</div><div class="label">menos inasistencias</div></td>
                <td><div class="num">4.9</div><div class="label">satisfacción / 5</div></td>
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
        <p>Diagrama dental con 13 condiciones. Haces clic en el diente, eliges el estado, se guarda automático. El paciente lo recibe por WhatsApp y entiende su tratamiento al instante.</p>
        <img src="{{ $screens['odontograma'] }}" alt="Odontograma interactivo" class="shot">
    </div>

    <div class="feat-big">
        <span class="feat-num">02</span>
        <h3>Recetas PDF profesionales</h3>
        <p>Con logo del consultorio, cédula y firma digital. El paciente las recibe por WhatsApp en un clic — adiós letra ilegible y papeles perdidos.</p>
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
        Sigue en la página siguiente → Funciones completas, precios y cómo empezar en 2 minutos
    </div>
</div>

{{-- ============================================================ --}}
{{-- PÁGINA 3 — FEATURES, PRECIOS Y CTA                            --}}
{{-- ============================================================ --}}
<div class="page">
    <div class="header">
        <div>
            <div class="brand">DocFácil</div>
            <small>12 funciones pensadas para doctores reales</small>
        </div>
        <div class="header-right">
            Brief 2026<br>
            docfacil.tu-app.co
        </div>
    </div>

    <h2 style="margin-top:0;">Todo lo que incluye</h2>
    <table class="features">
        <tr>
            <td><span class="icon">✓</span> <strong>Agenda de citas</strong> Calendario visual, multi-doctor, drag &amp; drop</td>
            <td><span class="icon">✓</span> <strong>Recordatorios WhatsApp</strong> Automáticos 24h y 2h antes</td>
        </tr>
        <tr>
            <td><span class="icon">✓</span> <strong>Expediente clínico</strong> Historial, alergias, notas SOAP</td>
            <td><span class="icon">✓</span> <strong>Recetas PDF</strong> Con cédula y firma digital</td>
        </tr>
        <tr>
            <td><span class="icon">✓</span> <strong>Odontograma interactivo</strong> 13 condiciones, compartible</td>
            <td><span class="icon">✓</span> <strong>Cobro por WhatsApp</strong> Monto + link de pago</td>
        </tr>
        <tr>
            <td><span class="icon">✓</span> <strong>Check-in con QR</strong> Sin papeleo, el paciente escanea</td>
            <td><span class="icon">✓</span> <strong>Firma digital</strong> En tablet o celular, timestamp legal</td>
        </tr>
        <tr>
            <td><span class="icon">✓</span> <strong>Portal del paciente</strong> Citas, recetas, pagos</td>
            <td><span class="icon">✓</span> <strong>Dashboard con gráficas</strong> Ingresos, citas, alertas</td>
        </tr>
        <tr>
            <td><span class="icon">✓</span> <strong>Alertas inteligentes</strong> Inactivos, vencidos, cumpleaños</td>
            <td><span class="icon">✓</span> <strong>Multi-doctor / Multi-sede</strong> Con comisiones automáticas</td>
        </tr>
    </table>

    <h2>Planes y precios</h2>

    <div style="background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%); border: 1px solid #f59e0b; border-radius: 6px; padding: 6px 10px; margin-bottom: 6px; font-size: 8.5pt;">
        <strong style="color: #92400e;">💡 Paga anual y ahorra 2 meses</strong> <span style="color:#78350f;">— el plan anual cuesta solo 10 meses (16.7% de descuento).</span>
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
            <tr>
                <td><strong>Free</strong></td>
                <td class="price-free">$0 / mes</td>
                <td style="color:#6b7280;">—</td>
                <td>1 doctor · hasta 30 pacientes · sin tarjeta</td>
            </tr>
            <tr>
                <td><strong>Básico</strong></td>
                <td class="price">$149 / mes</td>
                <td class="price" style="color:#059669;">$1,490 / año</td>
                <td>1 doctor · 200 pacientes · WhatsApp · recetas PDF</td>
            </tr>
            <tr class="popular">
                <td><strong>Pro ★</strong></td>
                <td class="price">$299 / mes</td>
                <td class="price" style="color:#059669;">$2,990 / año</td>
                <td>3 doctores · ilimitados · odontograma · portal paciente</td>
            </tr>
            <tr>
                <td><strong>Clínica</strong></td>
                <td class="price">$499 / mes</td>
                <td class="price" style="color:#059669;">$4,990 / año</td>
                <td>Doctores ilimitados · multi-sucursal · comisiones</td>
            </tr>
        </tbody>
    </table>
    <p style="font-size:8.5pt; color:#6b7280; margin:3px 0 0 0;">14 días gratis con todas las funciones. Sin tarjeta. Sin compromiso. Precios en MXN.</p>

    <div class="cta-box">
        <h3>Empieza gratis en 2 minutos</h3>
        <p>Escanea el QR o visita <strong>docfacil.tu-app.co</strong> y crea tu cuenta sin tarjeta.</p>
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
        DocFácil © {{ date('Y') }} · Software para consultorios médicos y dentales en México · <a href="{{ url('/') }}">docfacil.tu-app.co</a>
    </div>
</div>

</body>
</html>
