<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>DocFácil — Folleto</title>
    <style>
        @page { margin: 1cm; }
        * { box-sizing: border-box; }
        body {
            font-family: 'DejaVu Sans', sans-serif;
            color: #1f2937;
            font-size: 10pt;
            line-height: 1.45;
            margin: 0;
            padding: 0;
        }
        .page { page-break-after: always; padding: 0; }
        .page:last-child { page-break-after: auto; }

        /* PORTADA */
        .cover { background: linear-gradient(135deg, #0d9488 0%, #06b6d4 100%); color: white; padding: 20px 24px 18px 24px; text-align: center; border-radius: 10px; position: relative; }
        .cover .tag { display: inline-block; background: rgba(255,255,255,0.22); padding: 3px 11px; border-radius: 16px; font-size: 8pt; letter-spacing: 1px; margin-bottom: 8px; }
        .cover h1 { font-size: 30pt; font-weight: 800; margin: 0 0 4px 0; letter-spacing: -1px; line-height: 1; }
        .cover .sub { font-size: 10.5pt; opacity: 0.95; max-width: 90%; margin: 0 auto 8px auto; line-height: 1.4; }
        .cover .divider { width: 44px; height: 2px; background: white; margin: 6px auto; border-radius: 2px; }
        .cover .hero-shot { background: white; padding: 5px; border-radius: 6px; margin: 8px auto; box-shadow: 0 10px 20px rgba(0,0,0,0.2); display: block; width: 90%; }
        .cover .hero-shot img { width: 100%; display: block; border-radius: 3px; max-height: 260pt; object-fit: cover; object-position: top left; }
        .cover .value-props { width: 100%; margin: 8px 0 8px 0; border-collapse: collapse; }
        .cover .value-props td { width: 33%; padding: 0 6px; vertical-align: top; text-align: center; }
        .cover .value-props .vp-icon { font-size: 16pt; line-height: 1; margin-bottom: 3px; }
        .cover .value-props .vp-title { font-size: 9.5pt; font-weight: 700; margin-bottom: 1px; }
        .cover .value-props .vp-desc { font-size: 8pt; opacity: 0.92; line-height: 1.3; }
        .cover .stats-card { background: white; color: #1f2937; padding: 8px 14px; border-radius: 7px; display: inline-block; margin-top: 6px; box-shadow: 0 6px 12px rgba(0,0,0,0.15); }
        .cover .stats-card table { border-collapse: collapse; }
        .cover .stats-card td { padding: 2px 10px; text-align: center; border-right: 1px solid #e5e7eb; }
        .cover .stats-card td:last-child { border-right: none; }
        .cover .stats-card .num { font-size: 13pt; font-weight: 800; color: #0d9488; line-height: 1; }
        .cover .stats-card .label { font-size: 7pt; color: #6b7280; margin-top: 1px; }
        .cover .cover-footer { margin-top: 10px; font-size: 8.5pt; opacity: 0.85; }

        /* HEADERS */
        .header { border-bottom: 3px solid #14b8a6; padding-bottom: 8px; margin-bottom: 16px; }
        .header-brand { font-size: 15pt; font-weight: 800; color: #0d9488; letter-spacing: -0.5px; }
        .header-brand small { font-weight: normal; color: #6b7280; font-size: 9pt; display: block; margin-top: 1px; }
        .page-number { float: right; margin-top: -28px; font-size: 9pt; color: #9ca3af; }

        h2.section { font-size: 20pt; color: #0d9488; margin: 0 0 4px 0; letter-spacing: -0.5px; font-weight: 800; line-height: 1.15; }
        .section-sub { font-size: 10.5pt; color: #6b7280; margin: 0 0 14px 0; }

        /* ICP */
        .icp-card { background: #f0fdfa; border-left: 4px solid #14b8a6; padding: 12px 14px; border-radius: 8px; margin-bottom: 8px; }
        .icp-card h3 { margin: 0 0 3px 0; color: #0d9488; font-size: 10.5pt; }
        .icp-card p { margin: 0; font-size: 9pt; }

        /* Pains */
        .pain-grid { width: 100%; border-collapse: separate; border-spacing: 6px; margin: 6px 0; }
        .pain-cell { background: #fef2f2; border-left: 3px solid #ef4444; padding: 9px 11px; border-radius: 6px; width: 50%; vertical-align: top; }
        .pain-cell strong { color: #b91c1c; display: block; margin-bottom: 2px; font-size: 10pt; }
        .pain-cell span { font-size: 9pt; color: #4b5563; }

        /* Feature block with screenshot (side-by-side) */
        .feat-block { margin-bottom: 14px; background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 10px; padding: 12px; page-break-inside: avoid; }
        .feat-block table { width: 100%; border-collapse: collapse; }
        .feat-block .feat-img { width: 48%; vertical-align: top; padding-right: 12px; }
        .feat-block .feat-img img { width: 100%; display: block; border-radius: 6px; border: 1px solid #e5e7eb; }
        .feat-block .feat-text { vertical-align: top; }
        .feat-block .feat-num { display: inline-block; background: #0d9488; color: white; font-weight: bold; font-size: 9pt; padding: 2px 7px; border-radius: 5px; margin-bottom: 4px; }
        .feat-block h3 { margin: 0 0 4px 0; font-size: 12pt; color: #111827; letter-spacing: -0.2px; }
        .feat-block p { margin: 0 0 6px 0; font-size: 9pt; color: #4b5563; line-height: 1.45; }
        .feat-block ul { margin: 2px 0 0 0; padding-left: 14px; font-size: 8.5pt; color: #4b5563; }
        .feat-block li { margin-bottom: 1px; }

        /* Testimonials */
        .testimonial { background: #f9fafb; border-left: 3px solid #14b8a6; padding: 12px 16px; border-radius: 8px; margin-bottom: 10px; }
        .testimonial blockquote { margin: 0 0 6px 0; font-size: 10.5pt; color: #1f2937; font-style: italic; line-height: 1.45; }
        .testimonial .author { font-size: 9pt; color: #6b7280; }
        .testimonial .author strong { color: #0d9488; }

        /* Case stats */
        .case-stats { width: 100%; margin: 10px 0; background: linear-gradient(135deg, #0d9488 0%, #06b6d4 100%); color: white; padding: 16px; border-radius: 10px; }
        .case-stats td { text-align: center; padding: 4px; }
        .case-stats .num { font-size: 22pt; font-weight: 800; line-height: 1; }
        .case-stats .label { font-size: 8.5pt; opacity: 0.95; margin-top: 3px; }

        /* Before/after visual bar */
        .bar-compare { width: 100%; margin: 8px 0; border-collapse: collapse; }
        .bar-compare td { padding: 6px 8px; font-size: 9pt; vertical-align: middle; }
        .bar-compare .lbl { width: 18%; font-weight: 600; color: #374151; }
        .bar-compare .bar-wrap { width: 72%; padding-right: 8px; }
        .bar-compare .bar-bg { background: #f3f4f6; border-radius: 6px; height: 18px; position: relative; overflow: hidden; }
        .bar-compare .bar-fill-red { background: #ef4444; height: 18px; border-radius: 6px; }
        .bar-compare .bar-fill-green { background: #10b981; height: 18px; border-radius: 6px; }
        .bar-compare .val { width: 10%; text-align: right; font-weight: 700; font-size: 10pt; }

        /* Pricing */
        .pricing-grid { width: 100%; border-collapse: separate; border-spacing: 6px; }
        .plan { background: white; border: 2px solid #e5e7eb; border-radius: 10px; padding: 11px 12px; vertical-align: top; width: 25%; }
        .plan.popular { border-color: #ea580c; background: #fff7ed; }
        .plan h4 { margin: 0 0 2px 0; font-size: 11pt; color: #111827; }
        .plan .price { font-size: 15pt; font-weight: 800; color: #0d9488; margin: 3px 0; }
        .plan.popular .price { color: #ea580c; }
        .plan .ideal { font-size: 8pt; color: #6b7280; margin-bottom: 6px; min-height: 28px; }
        .plan ul { margin: 0; padding-left: 14px; font-size: 8.3pt; }
        .plan li { margin-bottom: 2px; }
        .popular-badge { display: inline-block; background: #ea580c; color: white; font-size: 7pt; padding: 1px 5px; border-radius: 4px; margin-left: 3px; vertical-align: middle; }

        /* Comparison */
        .compare { width: 100%; border-collapse: collapse; margin: 8px 0; font-size: 9pt; }
        .compare th { background: #0d9488; color: white; padding: 6px 8px; text-align: center; font-size: 9pt; }
        .compare th:first-child { text-align: left; }
        .compare td { padding: 5px 8px; border-bottom: 1px solid #e5e7eb; text-align: center; }
        .compare td:first-child { text-align: left; font-weight: 600; color: #374151; }
        .compare .yes { color: #059669; font-weight: bold; }
        .compare .no { color: #9ca3af; }

        /* Ecosystem / badges */
        .eco-grid { width: 100%; border-collapse: separate; border-spacing: 6px; margin: 8px 0; }
        .eco-card { background: #f0fdfa; border: 1px solid #ccfbf1; border-radius: 8px; padding: 10px 12px; vertical-align: top; width: 33%; }
        .eco-card .eco-icon { font-size: 22pt; line-height: 1; margin-bottom: 4px; color: #0d9488; }
        .eco-card strong { color: #0d9488; display: block; font-size: 10pt; margin-bottom: 2px; }
        .eco-card p { margin: 0; font-size: 8.5pt; color: #4b5563; line-height: 1.4; }

        .badges-row { text-align: center; margin: 10px 0; }
        .badge { display: inline-block; background: #f0fdfa; color: #0d9488; padding: 4px 10px; border-radius: 12px; margin: 2px 3px; font-weight: 600; font-size: 9pt; }

        /* Steps */
        .steps { width: 100%; margin: 14px 0; border-collapse: separate; border-spacing: 8px; }
        .step { display: table-cell; width: 33%; vertical-align: top; text-align: center; padding: 14px 10px; background: #f9fafb; border-radius: 10px; border: 1px solid #e5e7eb; }
        .step .num-circle { width: 36px; height: 36px; background: #14b8a6; color: white; border-radius: 50%; display: inline-block; line-height: 36px; text-align: center; font-size: 16pt; font-weight: 800; margin-bottom: 6px; }
        .step h4 { margin: 0 0 3px 0; font-size: 10.5pt; color: #111827; }
        .step p { margin: 0 0 6px 0; font-size: 8.5pt; color: #6b7280; }
        .step img { width: 100%; border-radius: 5px; border: 1px solid #e5e7eb; }

        /* Final CTA */
        .cta-final { background: linear-gradient(135deg, #0d9488 0%, #06b6d4 100%); color: white; padding: 22px 24px; border-radius: 12px; margin-top: 12px; }
        .cta-final h3 { margin: 0 0 4px 0; font-size: 17pt; }
        .cta-final p { margin: 0 0 10px 0; opacity: 0.95; font-size: 10pt; }
        .cta-contact { width: 100%; margin-top: 10px; }
        .cta-contact .qr { width: 130px; text-align: center; }
        .cta-contact .qr img { width: 120px; height: 120px; background: white; padding: 5px; border-radius: 6px; }
        .cta-contact .info { padding-left: 18px; vertical-align: middle; font-size: 10pt; }
        .cta-contact .info strong { display: block; font-size: 11.5pt; margin-bottom: 3px; }
        .cta-contact .info a { color: white; text-decoration: none; }
        .cta-contact .info div { margin-bottom: 2px; }

        .footer { border-top: 1px solid #e5e7eb; margin-top: 12px; padding-top: 6px; font-size: 8pt; color: #9ca3af; text-align: center; }

        .quote-line { font-size: 9.5pt; color: #6b7280; font-style: italic; margin: 8px 0 0 0; }
    </style>
</head>
<body>

{{-- ============================================================ --}}
{{-- PÁGINA 1 — PORTADA                                            --}}
{{-- ============================================================ --}}
<div class="page">
    <div class="cover">
        <div class="tag">FOLLETO · EDICIÓN 2026</div>
        <h1>DocFácil</h1>
        <div class="divider"></div>
        <p class="sub"><strong>Su consultorio dental, en orden y en un solo lugar.</strong><br>Agenda, expediente, odontograma, recetas y cobros. Los recordatorios salen de su propio WhatsApp con el mensaje ya escrito: usted solo da enviar.</p>

        <div class="hero-shot">
            <img src="{{ $screens['dashboard'] }}" alt="Panel de control DocFácil">
        </div>

        <table class="value-props">
            <tr>
                <td>
                    <div class="vp-title" style="color:#fff;">Recordatorios a 1 clic</div>
                    <div class="vp-desc">Se abre su WhatsApp con el mensaje listo; usted da enviar.</div>
                </td>
                <td>
                    <div class="vp-title" style="color:#fff;">Todo conectado</div>
                    <div class="vp-desc">Del odontograma sale el presupuesto; de la cita, el cobro.</div>
                </td>
                <td>
                    <div class="vp-title" style="color:#fff;">Sabe quién le debe</div>
                    <div class="vp-desc">Pagos y abonos anotados; el saldo se recuerda por WhatsApp.</div>
                </td>
            </tr>
        </table>

        <div class="stats-card">
            <table>
                <tr>
                    <td><div class="num">15 días</div><div class="label">de prueba con todo, sin tarjeta</div></td>
                    <td><div class="num">30 días</div><div class="label">de garantía en su primer pago</div></td>
                    <td><div class="num">$0</div><div class="label">plan Free, para siempre</div></td>
                    <td><div class="num">1 clic</div><div class="label">para mandar un recordatorio</div></td>
                </tr>
            </table>
        </div>

        <div class="cover-footer">docfacil.tu-app.co · Omar Lerma, Fundador · 668 249 3398</div>
    </div>
</div>

{{-- ============================================================ --}}
{{-- PÁGINA 2 — PARA QUIÉN Y QUÉ DOLOR RESUELVE                    --}}
{{-- ============================================================ --}}
<div class="page">
    <div class="header">
        <div class="header-brand">DocFácil <small>Para quién es</small></div>
        <div class="page-number">02</div>
    </div>

    <h2 class="section">Para dentistas que aún dependen del papel</h2>
    <p class="section-sub">Hecho para consultorios dentales en México que quieren dejar la libreta y el Excel sin complicarse.</p>

    <div class="icp-card">
        <h3><span style="display:inline-block; background:#0d9488; color:white; padding:2px 8px; border-radius:4px; font-size:9pt; margin-right:6px; vertical-align:middle;">1 DOCTOR</span> El dentista que trabaja solo</h3>
        <p>Odontología general, ortodoncia, endodoncia. Lleva su agenda, sus expedientes y sus cobros en un solo lugar.</p>
    </div>
    <div class="icp-card">
        <h3><span style="display:inline-block; background:#0891b2; color:white; padding:2px 8px; border-radius:4px; font-size:9pt; margin-right:6px; vertical-align:middle;">2 O 3 DOCTORES</span> Consultorios dentales de 2 o 3 doctores</h3>
        <p>Agenda compartida con un color por doctor, lista de espera y pacientes que agendan solos.</p>
    </div>
    <div class="icp-card">
        <h3><span style="display:inline-block; background:#7c3aed; color:white; padding:2px 8px; border-radius:4px; font-size:9pt; margin-right:6px; vertical-align:middle;">CLÍNICA</span> Clínicas dentales con varios doctores</h3>
        <p>Doctores ilimitados, producción individual y reportes por doctor.</p>
    </div>

    <h2 class="section" style="font-size:16pt; margin-top:14px;">Lo de todos los días</h2>
    <table class="pain-grid"><tr>
        <td class="pain-cell"><strong>Agenda caótica</strong><span>Papel y Excel: se pierden citas, cuesta encontrar a un paciente, cada cambio pesa.</span></td>
        <td class="pain-cell"><strong>Pacientes que no llegan</strong><span>Se les olvida la cita y el espacio se queda vacío.</span></td>
    </tr><tr>
        <td class="pain-cell"><strong>Recetas a mano</strong><span>Letra difícil de leer, sin copia, sin respaldo.</span></td>
        <td class="pain-cell"><strong>No sabe si gana</strong><span>Sin reportes ni control de cobros. Decisiones a ojo.</span></td>
    </tr></table>

    <p style="background:#f0fdfa; border-left:3px solid #14b8a6; padding:10px 14px; border-radius:6px; margin-top:10px; font-size:9.5pt;">
        <strong style="color:#0d9488;">→ Si se identifica con 2 o más de estos puntos, DocFácil se hizo para usted.</strong>
    </p>

    <div class="footer">Agenda, expediente, odontograma, recetas y cobros, conectados en un solo sistema.</div>
</div>

{{-- ============================================================ --}}
{{-- PÁGINA 3 — FEATURES 1: AGENDA + EXPEDIENTE + RECETAS          --}}
{{-- ============================================================ --}}
<div class="page">
    <div class="header">
        <div class="header-brand">DocFácil <small>Funciones clave · Parte 1 de 2</small></div>
        <div class="page-number">03</div>
    </div>

    <h2 class="section">Todo el consultorio, conectado</h2>
    <p class="section-sub">Agenda, expediente, odontograma, recetas y cobros hablan entre sí.</p>

    <div class="feat-block">
        <table><tr>
            <td class="feat-img"><img src="{{ $screens['calendario'] }}" alt="Agenda y calendario"></td>
            <td class="feat-text">
                <span class="feat-num">01</span>
                <h3>Agenda + recordatorios por WhatsApp a 1 clic</h3>
                <p>Vista diaria, semanal y mensual. Para las citas de mañana, se abre su WhatsApp con el recordatorio ya escrito y usted da enviar.</p>
                <ul>
                    <li>El paciente confirma su cita con un link</li>
                    <li>Desde computadora, tablet o celular</li>
                    <li>Un color por doctor (planes Pro y Clínica)</li>
                </ul>
            </td>
        </tr></table>
    </div>

    <div class="feat-block">
        <table><tr>
            <td class="feat-img"><img src="{{ $screens['expediente'] }}" alt="Expediente clínico"></td>
            <td class="feat-text">
                <span class="feat-num">02</span>
                <h3>Expediente clínico digital</h3>
                <p>Motivo, diagnóstico CIE-10, tratamiento, signos vitales y alergias. Las notas se bloquean a las 24 horas.</p>
                <ul>
                    <li>Todo organizado por paciente y consulta</li>
                    <li>Hasta 10 fotos por nota, de 5 MB cada una</li>
                    <li>Búsqueda por nombre o teléfono</li>
                </ul>
            </td>
        </tr></table>
    </div>

    <div class="feat-block">
        <table><tr>
            <td class="feat-img"><img src="{{ $screens['recetas'] }}" alt="Recetas PDF"></td>
            <td class="feat-text">
                <span class="feat-num">03</span>
                <h3>Recetas PDF claras y con cédula</h3>
                <p>Con su nombre, especialidad, cédula y datos del consultorio. Se descargan en PDF con un clic.</p>
                <ul>
                    <li>Historial de recetas por paciente</li>
                    <li>Se bloquean 24 horas después de creadas</li>
                    <li>El paciente las consulta en su portal</li>
                </ul>
            </td>
        </tr></table>
    </div>

    <div class="footer">Sigue en la siguiente página → Odontograma, cobros, escritorio y más</div>
</div>

{{-- ============================================================ --}}
{{-- PÁGINA 4 — FEATURES 2: ODONTOGRAMA + COBROS + DASHBOARD       --}}
{{-- ============================================================ --}}
<div class="page">
    <div class="header">
        <div class="header-brand">DocFácil <small>Funciones clave · Parte 2 de 2</small></div>
        <div class="page-number">04</div>
    </div>

    <div class="feat-block">
        <table><tr>
            <td class="feat-img"><img src="{{ $screens['odontograma'] }}" alt="Odontograma interactivo"></td>
            <td class="feat-text">
                <span class="feat-num">04</span>
                <h3>Odontograma FDI interactivo</h3>
                <p>Haga clic en el diente, elija el estado y se guarda solo. De ahí salen los presupuestos que el paciente acepta en línea. Viene desde el plan Básico.</p>
                <ul>
                    <li>Historial visual de cada pieza</li>
                    <li>Colores por tipo de tratamiento</li>
                    <li>Se imprime en PDF</li>
                </ul>
            </td>
        </tr></table>
    </div>

    <div class="feat-block">
        <table><tr>
            <td class="feat-img"><img src="{{ $screens['cobros'] }}" alt="Cobros e ingresos"></td>
            <td class="feat-text">
                <span class="feat-num">05</span>
                <h3>Cobros, abonos e ingresos del mes</h3>
                <p>Anote cada pago: efectivo, transferencia o tarjeta, con abonos. Con un clic se abre su WhatsApp con el recordatorio del saldo: monto, fecha y sus datos para pagar.</p>
                <ul>
                    <li>Ingresos del mes al día</li>
                    <li>Gastos y corte del mes</li>
                    <li>Pagos parciales y abonos</li>
                </ul>
            </td>
        </tr></table>
    </div>

    <div class="feat-block">
        <table><tr>
            <td class="feat-img"><img src="{{ $screens['dashboard'] }}" alt="Escritorio con métricas"></td>
            <td class="feat-text">
                <span class="feat-num">06</span>
                <h3>Escritorio con sus números y alertas</h3>
                <p>Ingresos del mes, próximas citas y cobros pendientes. Desde Pro, alertas: inactivos, cobros atrasados, huecos para la lista de espera, tratamientos por agendar y citas de mañana sin recordatorio.</p>
                <ul>
                    <li>Los cumpleaños del día</li>
                    <li>Producción y reportes por doctor (plan Clínica)</li>
                </ul>
            </td>
        </tr></table>
    </div>

    <div class="badges-row">
        <span class="badge">+ Check-in QR (Básico)</span>
        <span class="badge">+ Pantalla de la sala (Básico)</span>
        <span class="badge">+ Portal del paciente (Básico)</span>
        <span class="badge">+ Firma de consentimientos (Pro)</span>
    </div>

    <div class="footer">Check-in QR: el paciente escanea al llegar y ustedes ven que ya llegó.</div>
</div>

{{-- ============================================================ --}}
{{-- PÁGINA 5 — PRECIOS                                            --}}
{{-- ============================================================ --}}
<div class="page">
    <div class="header">
        <div class="header-brand">DocFácil <small>Precios en pesos mexicanos</small></div>
        <div class="page-number">05</div>
    </div>

    <h2 class="section">Planes pensados para cada consultorio</h2>
    <p class="section-sub">Sin contratos. Sin tarjeta para probar. Cancela cuando quiera.</p>

    <div style="background: #fef3c7; border: 1px solid #f59e0b; border-radius: 6px; padding: 5px 10px; margin-bottom: 6px; font-size: 8.5pt; text-align: center;">
        <strong style="color: #92400e;">Pague anual y ahorre 2 meses</strong> <span style="color:#78350f;">(el año cuesta lo de 10 meses).</span>
    </div>

    <table class="pricing-grid"><tr>
        @foreach ($pages['plans'] as $p)
        <td class="plan {{ !empty($p['popular']) ? 'popular' : '' }}">
            <h4>{{ $p['name'] }}{!! !empty($p['popular']) ? ' <span class="popular-badge">POPULAR</span>' : '' !!}</h4>
            <div class="price">${{ $p['price'] }}<span style="font-size:9pt; font-weight:normal; color:#6b7280;">/mes</span></div>
            @if ($p['annual'] > 0)
            <div style="font-size:7.5pt; color:#059669; margin:-2px 0 4px 0; font-weight:600;">o ${{ number_format($p['annual']) }}/año · 2 meses gratis</div>
            @else
            <div style="font-size:7.5pt; color:#6b7280; margin:-2px 0 4px 0;">para siempre · sin tarjeta</div>
            @endif
            <div class="ideal">{{ $p['ideal'] }}<br><strong style="color:#374151;">{{ $p['limits'] }}</strong></div>
            @if (!empty($p['lead']))
            <div style="font-size:7.5pt; color:#0d9488; font-weight:600; margin-bottom:3px;">{{ $p['lead'] }}</div>
            @endif
            <ul>
                @foreach ($p['features'] as $feat)
                <li>{{ $feat }}</li>
                @endforeach
            </ul>
        </td>
        @endforeach
    </tr></table>
    <p style="font-size:8.5pt; color:#6b7280; margin-top:4px;">15 días gratis con todas las funciones. Sin tarjeta. Garantía de 30 días en su primer pago. Precios en pesos mexicanos.</p>

    <h3 style="font-size:11pt; margin:12px 0 4px 0; color:#0d9488;">Lo que <em>NO</em> hace DocFácil (para que lo sepa desde hoy)</h3>
    <p style="font-size:9pt; color:#4b5563; margin:0;">Facturación CFDI, cobros en línea al paciente, envío automático de mensajes o correos, integración con laboratorios dentales externos, teleconsulta por video.</p>
</div>

{{-- ============================================================ --}}
{{-- PÁGINA 6 — INCLUIDO Y SEGURIDAD                               --}}
{{-- ============================================================ --}}
<div class="page">
    <div class="header">
        <div class="header-brand">DocFácil <small>Incluido, seguridad y confianza</small></div>
        <div class="page-number">06</div>
    </div>

    <h2 class="section">Todo lo que viene incluido</h2>
    <p class="section-sub">Sin configuraciones técnicas. Listo desde el primer día.</p>

    <table class="eco-grid"><tr>
        <td class="eco-card">
            <div class="eco-icon">💬</div>
            <strong>WhatsApp a 1 clic</strong>
            <p>Recordatorios y saldos: se abre su WhatsApp con el mensaje ya escrito y usted da enviar, desde su número.</p>
        </td>
        <td class="eco-card">
            <div class="eco-icon">🗓</div>
            <strong>Agenda en línea</strong>
            <p>Sus pacientes agendan solos, a cualquier hora (desde el plan Pro).</p>
        </td>
        <td class="eco-card">
            <div class="eco-icon">💳</div>
            <strong>Registro de pagos con abonos</strong>
            <p>Efectivo, transferencia o tarjeta. Cada abono queda anotado y sabe cuánto falta.</p>
        </td>
    </tr><tr>
        <td class="eco-card">
            <div class="eco-icon">📱</div>
            <strong>Se instala como app</strong>
            <p>En celular o tablet, con su ícono en la pantalla. Necesita internet para funcionar.</p>
        </td>
        <td class="eco-card">
            <div class="eco-icon">☁</div>
            <strong>Respaldo en la nube</strong>
            <p>Respaldo diario automático. Sin USBs ni archivos perdidos.</p>
        </td>
        <td class="eco-card">
            <div class="eco-icon">👥</div>
            <strong>Portal del paciente</strong>
            <p>Sus pacientes ven sus citas, recetas y pagos (desde el plan Básico).</p>
        </td>
    </tr></table>

    <h2 class="section" style="font-size:16pt; margin-top:14px;">Seguridad y privacidad</h2>
    <div style="background:#f9fafb; border-radius:10px; padding:14px 16px; border:1px solid #e5e7eb; font-size:9.5pt;">
        <table style="width:100%;">
            <tr>
                <td style="vertical-align:top; width:50%; padding-right:10px;">
                    <p style="margin:0 0 6px 0;"><strong style="color:#0d9488;">🔒 Conexión cifrada (HTTPS)</strong><br>La información viaja cifrada entre su navegador y nuestros servidores.</p>
                    <p style="margin:0 0 6px 0;"><strong style="color:#0d9488;">🗂 Cada consultorio aislado</strong><br>Sus datos nunca se mezclan con los de otro consultorio.</p>
                    <p style="margin:0;"><strong style="color:#0d9488;">📋 Pensado para la NOM-004</strong><br>Notas clínicas y recetas que se bloquean a las 24 horas, con historial de cambios.</p>
                </td>
                <td style="vertical-align:top; width:50%;">
                    <p style="margin:0 0 6px 0;"><strong style="color:#0d9488;">💾 Respaldo diario automático</strong><br>Cada día se respalda la base de datos.</p>
                    <p style="margin:0 0 6px 0;"><strong style="color:#0d9488;">🔐 Roles y permisos</strong><br>Cada usuario ve solo lo que necesita ver. Verificación en dos pasos opcional.</p>
                    <p style="margin:0;"><strong style="color:#0d9488;">📤 Sus datos son suyos</strong><br>Recetas, consentimientos y presupuestos en PDF; la copia completa se pide a soporte.</p>
                </td>
            </tr>
        </table>
    </div>

    <div class="badges-row" style="margin-top:14px;">
        <span class="badge">✓ Hecho en México</span>
        <span class="badge">✓ Soporte en español</span>
        <span class="badge">✓ Se instala como app</span>
        <span class="badge">✓ Sin anuncios</span>
        <span class="badge">✓ Código propio</span>
    </div>
</div>

{{-- ============================================================ --}}
{{-- PÁGINA 7 — CÓMO EMPEZAR Y CTA FINAL                           --}}
{{-- ============================================================ --}}
<div class="page">
    <div class="header">
        <div class="header-brand">DocFácil <small>Cómo empezar hoy</small></div>
        <div class="page-number">07</div>
    </div>

    <h2 class="section">Empiece en 3 pasos</h2>
    <p class="section-sub">Sin instalaciones. Sin tarjeta.</p>

    <table class="steps"><tr>
        <td class="step">
            <div class="num-circle">1</div>
            <h4>Regístrese</h4>
            <p>Cree su cuenta sin tarjeta. 15 días gratis con todas las funciones.</p>
            <img src="{{ $screens['landing'] }}" alt="Registro">
        </td>
        <td class="step">
            <div class="num-circle">2</div>
            <h4>Cargue sus pacientes</h4>
            <p>Suba su Excel o captúrelos a mano. Si son muchos, le ayudamos.</p>
            <img src="{{ $screens['pacientes'] }}" alt="Pacientes">
        </td>
        <td class="step">
            <div class="num-circle">3</div>
            <h4>Agende su primer día</h4>
            <p>Abra la agenda, cree su primera cita y mande su primer recordatorio con un clic.</p>
            <img src="{{ $screens['calendario'] }}" alt="Agenda">
        </td>
    </tr></table>

    <div class="cta-final">
        <h3>Empiece gratis hoy</h3>
        <p>Escanee el QR o hable directamente con Omar, el fundador.</p>
        <table class="cta-contact"><tr>
            <td class="qr"><img src="{{ $qrDataUri }}" alt="QR registro DocFácil"></td>
            <td class="info">
                <strong>Omar Lerma · Fundador</strong>
                <div><span style="display:inline-block; width:16px; font-weight:bold;">☎</span> <a href="{{ $whatsappLink }}">668 249 3398</a> (WhatsApp)</div>
                <div><span style="display:inline-block; width:16px; font-weight:bold;">✉</span> <a href="mailto:contacto@docfacil.com">contacto@docfacil.com</a></div>
                <div><span style="display:inline-block; width:16px; font-weight:bold;">⌂</span> <a href="{{ url('/') }}">docfacil.tu-app.co</a></div>
                <div style="margin-top:5px; opacity:0.9;">Demo en vivo · Le ayudamos a empezar · Soporte por WhatsApp</div>
            </td>
        </tr></table>
    </div>

    <div class="footer">
        DocFácil © {{ date('Y') }} · Hecho en México para dentistas mexicanos · docfacil.tu-app.co
    </div>
</div>

</body>
</html>
