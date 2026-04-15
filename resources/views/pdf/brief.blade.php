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
        @page { margin: 1.2cm; }
        * { box-sizing: border-box; }
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            color: #1f2937;
            font-size: 10.5pt;
            line-height: 1.45;
            margin: 0;
            padding: 0;
        }
        .page { page-break-after: always; }
        .page:last-child { page-break-after: auto; }

        /* Header */
        .header { display: flex; align-items: center; justify-content: space-between; border-bottom: 3px solid #14b8a6; padding-bottom: 10px; margin-bottom: 14px; }
        .brand { font-size: 22pt; font-weight: bold; color: #0d9488; letter-spacing: -0.5px; }
        .brand small { font-weight: normal; color: #6b7280; font-size: 10pt; display: block; margin-top: 2px; }
        .header-right { text-align: right; font-size: 8.5pt; color: #6b7280; }

        /* Hero */
        .hero { background: linear-gradient(135deg, #0d9488 0%, #06b6d4 100%); color: white; padding: 18px 20px; border-radius: 12px; margin-bottom: 16px; }
        .hero h1 { margin: 0 0 6px 0; font-size: 20pt; font-weight: 800; letter-spacing: -0.5px; }
        .hero p { margin: 0; font-size: 10.5pt; opacity: 0.95; }

        /* Sections */
        h2 { font-size: 13pt; color: #0d9488; margin: 14px 0 8px 0; border-bottom: 1px solid #e5e7eb; padding-bottom: 4px; }
        h3 { font-size: 10.5pt; font-weight: 700; color: #111827; margin: 0 0 3px 0; }

        /* 2 column grid */
        .row { width: 100%; margin-bottom: 12px; }
        .col-2 { width: 48%; display: inline-block; vertical-align: top; margin-right: 3%; }
        .col-2:last-child { margin-right: 0; }

        /* Pain / Solution boxes */
        .pain-box, .solution-box { padding: 10px 12px; border-radius: 8px; margin-bottom: 8px; font-size: 9.5pt; }
        .pain-box { background: #fef2f2; border-left: 3px solid #ef4444; }
        .solution-box { background: #f0fdfa; border-left: 3px solid #14b8a6; }
        .pain-box strong { color: #b91c1c; }
        .solution-box strong { color: #0d9488; }

        /* Features grid */
        .features { width: 100%; border-collapse: collapse; margin: 8px 0; }
        .features td { padding: 6px 8px; vertical-align: top; font-size: 9pt; border-bottom: 1px solid #f3f4f6; width: 50%; }
        .features td strong { color: #0d9488; display: block; margin-bottom: 1px; }
        .icon { display: inline-block; width: 14px; color: #14b8a6; font-weight: bold; }

        /* Stats */
        .stats { width: 100%; background: #f9fafb; border-radius: 10px; padding: 12px; margin: 10px 0; }
        .stats td { text-align: center; padding: 4px 2px; }
        .stats .num { font-size: 18pt; font-weight: 800; color: #0d9488; line-height: 1; }
        .stats .label { font-size: 8.5pt; color: #6b7280; margin-top: 2px; }

        /* Pricing */
        .pricing { width: 100%; border-collapse: collapse; margin: 10px 0; }
        .pricing th { background: #f0fdfa; color: #0d9488; padding: 8px; font-size: 9.5pt; text-align: left; border-bottom: 2px solid #14b8a6; }
        .pricing td { padding: 8px; font-size: 9.5pt; border-bottom: 1px solid #e5e7eb; }
        .pricing .popular { background: #fff7ed; }
        .pricing .popular td:first-child strong { color: #ea580c; }
        .price { font-weight: bold; color: #111827; font-size: 11pt; }
        .price-free { color: #059669; font-weight: bold; }

        /* CTA box */
        .cta-box { background: linear-gradient(135deg, #0d9488 0%, #06b6d4 100%); color: white; padding: 16px 20px; border-radius: 12px; margin-top: 12px; }
        .cta-box h3 { color: white; margin: 0 0 4px 0; font-size: 14pt; }
        .cta-box p { margin: 0; font-size: 9.5pt; opacity: 0.95; }
        .cta-grid { width: 100%; margin-top: 10px; }
        .cta-grid td { vertical-align: middle; }
        .cta-grid .qr { width: 110px; text-align: center; }
        .cta-grid .qr img { width: 100px; height: 100px; background: white; padding: 4px; border-radius: 6px; }
        .cta-grid .info { padding-left: 14px; font-size: 9.5pt; }
        .cta-grid .info strong { display: block; color: white; font-size: 10.5pt; margin-bottom: 2px; }
        .cta-grid .info a { color: white; text-decoration: none; }

        /* Footer */
        .footer { border-top: 1px solid #e5e7eb; margin-top: 14px; padding-top: 8px; font-size: 8pt; color: #9ca3af; text-align: center; }
        .footer a { color: #0d9488; text-decoration: none; }
    </style>
</head>
<body>

{{-- ============================================================ --}}
{{-- PÁGINA 1 — FRENTE                                             --}}
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

    <h2>El problema que vives hoy</h2>
    <div class="row">
        <div class="col-2">
            <div class="pain-box">
                <strong>Agenda en papel o Excel</strong><br>
                Pierdes citas, no puedes buscar rápido, y cada cambio cuesta tiempo.
            </div>
            <div class="pain-box">
                <strong>Pacientes que no llegan</strong><br>
                El 30% no se presenta. Son consultas perdidas que no vuelven.
            </div>
        </div>
        <div class="col-2">
            <div class="pain-box">
                <strong>Recetas a mano</strong><br>
                Letra ilegible, sin copia, sin respaldo, riesgo de errores.
            </div>
            <div class="pain-box">
                <strong>No sabes cuánto ganas</strong><br>
                Sin reportes, sin control de cobros pendientes, sin datos.
            </div>
        </div>
    </div>

    <h2>Cómo lo resuelve DocFácil</h2>
    <div class="row">
        <div class="col-2">
            <div class="solution-box">
                <strong>Agenda inteligente en la nube</strong><br>
                Multi-doctor, arrastrar y soltar, acceso desde cualquier dispositivo.
            </div>
            <div class="solution-box">
                <strong>Recordatorios WhatsApp automáticos</strong><br>
                Reduce inasistencias hasta 40%. 24h y 2h antes de la cita.
            </div>
        </div>
        <div class="col-2">
            <div class="solution-box">
                <strong>Recetas PDF profesionales</strong><br>
                Con cédula, firma digital, descargables. El paciente las recibe por WhatsApp.
            </div>
            <div class="solution-box">
                <strong>Reportes en tiempo real</strong><br>
                Ingresos, cobros pendientes, pacientes activos, alertas inteligentes.
            </div>
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
        Sigue en la página siguiente → Funcionalidades completas, precios y cómo empezar
    </div>
</div>

{{-- ============================================================ --}}
{{-- PÁGINA 2 — REVERSO                                            --}}
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

    <h2>Todo lo que incluye</h2>
    <table class="features">
        <tr>
            <td><span class="icon">✓</span> <strong>Agenda de citas</strong> Calendario visual, multi-doctor, drag & drop</td>
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
    <table class="pricing">
        <thead>
            <tr>
                <th style="width:18%;">Plan</th>
                <th style="width:15%;">Precio</th>
                <th>Ideal para</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><strong>Free</strong></td>
                <td class="price-free">$0 / mes</td>
                <td>1 doctor · hasta 30 pacientes · agenda básica · probar sin tarjeta</td>
            </tr>
            <tr>
                <td><strong>Básico</strong></td>
                <td class="price">$149 / mes</td>
                <td>1 doctor · 200 pacientes · WhatsApp · recetas PDF · check-in QR</td>
            </tr>
            <tr class="popular">
                <td><strong>Pro ★</strong></td>
                <td class="price">$299 / mes</td>
                <td>3 doctores · pacientes ilimitados · odontograma · portal paciente · soporte prioritario</td>
            </tr>
            <tr>
                <td><strong>Clínica</strong></td>
                <td class="price">$499 / mes</td>
                <td>Doctores ilimitados · multi-sucursal · comisiones entre doctores · onboarding 1 a 1</td>
            </tr>
        </tbody>
    </table>
    <p style="font-size:9pt; color:#6b7280; margin:4px 0 0 0;">14 días gratis con todas las funciones. Sin tarjeta. Sin compromiso. Precios en MXN.</p>

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
                    <span style="display:inline-block; width:14px; font-weight:bold;">☎</span> <a href="{{ $whatsappLink }}">668 249 3398</a> (WhatsApp)<br>
                    <span style="display:inline-block; width:14px; font-weight:bold;">✉</span> <a href="mailto:contacto@docfacil.com">contacto@docfacil.com</a><br>
                    <span style="display:inline-block; width:14px; font-weight:bold;">⌂</span> <a href="{{ url('/') }}">docfacil.tu-app.co</a><br>
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
