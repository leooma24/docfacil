<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>DocFácil — Brochure</title>
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
        <div class="tag">BROCHURE · EDICIÓN 2026</div>
        <h1>DocFácil</h1>
        <div class="divider"></div>
        <p class="sub"><strong>Tu consultorio, organizado y al día.</strong><br>Software mexicano para consultorios médicos y dentales: agenda, expediente clínico, recetas PDF, recordatorios por WhatsApp y cobros — todo en un solo lugar.</p>

        <div class="hero-shot">
            <img src="{{ $screens['dashboard'] }}" alt="Panel de control DocFácil">
        </div>

        <table class="value-props">
            <tr>
                <td>
                    <div class="vp-icon">📅</div>
                    <div class="vp-title">Menos inasistencias</div>
                    <div class="vp-desc">Recordatorios por WhatsApp bajan las faltas de 30% a 8%.</div>
                </td>
                <td>
                    <div class="vp-icon">📋</div>
                    <div class="vp-title">Todo digital, en la nube</div>
                    <div class="vp-desc">Expedientes, recetas y cobros accesibles desde cualquier dispositivo.</div>
                </td>
                <td>
                    <div class="vp-icon">💰</div>
                    <div class="vp-title">Control de ingresos</div>
                    <div class="vp-desc">Cobros, pendientes y reportes del consultorio en tiempo real.</div>
                </td>
            </tr>
        </table>

        <div class="stats-card">
            <table>
                <tr>
                    <td><div class="num">500+</div><div class="label">consultorios activos</div></td>
                    <td><div class="num">15K+</div><div class="label">citas gestionadas</div></td>
                    <td><div class="num">40%</div><div class="label">menos inasistencias</div></td>
                    <td><div class="num">4.9</div><div class="label">satisfacción / 5</div></td>
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

    <h2 class="section">Para doctores que aún dependen del papel</h2>
    <p class="section-sub">Diseñado para consultorios pequeños y medianos en México que quieren digitalizarse sin complicarse.</p>

    <div class="icp-card">
        <h3><span style="display:inline-block; background:#0d9488; color:white; padding:2px 8px; border-radius:4px; font-size:9pt; margin-right:6px; vertical-align:middle;">DENTAL</span> Consultorios dentales de 1 a 3 doctores</h3>
        <p>Odontólogos generales, ortodoncistas, endodoncistas. Atienden 30-200 pacientes/mes y necesitan dejar el papel, Excel o la agenda de pared.</p>
    </div>
    <div class="icp-card">
        <h3><span style="display:inline-block; background:#0891b2; color:white; padding:2px 8px; border-radius:4px; font-size:9pt; margin-right:6px; vertical-align:middle;">MÉDICO</span> Consultorios médicos generales y de especialidad</h3>
        <p>Médicos generales, pediatras, ginecólogos, dermatólogos. Facturan $20K-$200K/mes y pierden tiempo en tareas administrativas.</p>
    </div>
    <div class="icp-card">
        <h3><span style="display:inline-block; background:#7c3aed; color:white; padding:2px 8px; border-radius:4px; font-size:9pt; margin-right:6px; vertical-align:middle;">CLÍNICA</span> Clínicas pequeñas con varios doctores</h3>
        <p>Clínicas multidisciplinarias con 3-10 doctores. Necesitan agenda compartida, comisiones entre doctores y reportes por profesional.</p>
    </div>

    <h2 class="section" style="font-size:16pt; margin-top:14px;">4 dolores que vivimos todos los días</h2>
    <table class="pain-grid"><tr>
        <td class="pain-cell"><strong>Agenda caótica</strong><span>Papel y Excel: pierdes citas, no buscas rápido, cada cambio pesa.</span></td>
        <td class="pain-cell"><strong>Pacientes no llegan</strong><span>El 30% no se presenta. Consultas perdidas que no regresan.</span></td>
    </tr><tr>
        <td class="pain-cell"><strong>Recetas a mano</strong><span>Letra ilegible, sin copia, sin respaldo. Riesgo legal y profesional.</span></td>
        <td class="pain-cell"><strong>No sabes si ganas</strong><span>Sin reportes ni control de cobros. Decisiones a ojo.</span></td>
    </tr></table>

    <p style="background:#f0fdfa; border-left:3px solid #14b8a6; padding:10px 14px; border-radius:6px; margin-top:10px; font-size:9.5pt;">
        <strong style="color:#0d9488;">→ Si te identificas con 2 o más de estos puntos, DocFácil fue pensado para ti.</strong>
    </p>

    <div class="footer">DocFácil es la única plataforma mexicana que integra todos estos dolores en una sola solución.</div>
</div>

{{-- ============================================================ --}}
{{-- PÁGINA 3 — FEATURES 1: AGENDA + EXPEDIENTE + RECETAS          --}}
{{-- ============================================================ --}}
<div class="page">
    <div class="header">
        <div class="header-brand">DocFácil <small>Funciones clave · Parte 1 de 2</small></div>
        <div class="page-number">03</div>
    </div>

    <h2 class="section">Todo el flujo del consultorio</h2>
    <p class="section-sub">No contrates 5 apps distintas. DocFácil integra todo en una sola plataforma.</p>

    <div class="feat-block">
        <table><tr>
            <td class="feat-img"><img src="{{ $screens['calendario'] }}" alt="Agenda y calendario"></td>
            <td class="feat-text">
                <span class="feat-num">01</span>
                <h3>Agenda inteligente + recordatorios WhatsApp</h3>
                <p>Calendario visual multi-doctor, arrastrar y soltar, vista diaria/semanal/mensual. Recordatorios WhatsApp automáticos 24h y 2h antes, o con un clic manual desde tu agenda.</p>
                <ul>
                    <li>Hasta 40% menos inasistencias</li>
                    <li>Acceso desde PC, tablet o celular</li>
                    <li>Colores por estado y doctor</li>
                </ul>
            </td>
        </tr></table>
    </div>

    <div class="feat-block">
        <table><tr>
            <td class="feat-img"><img src="{{ $screens['expediente'] }}" alt="Expediente clínico"></td>
            <td class="feat-text">
                <span class="feat-num">02</span>
                <h3>Expediente clínico digital completo</h3>
                <p>Historial por paciente, alergias, padecimientos, notas SOAP, fotos clínicas. Búsqueda instantánea y cumplimiento con NOM-004-SSA3.</p>
                <ul>
                    <li>Todo organizado por paciente y consulta</li>
                    <li>Fotos antes/después sin límite</li>
                    <li>Acceso rápido en consulta</li>
                </ul>
            </td>
        </tr></table>
    </div>

    <div class="feat-block">
        <table><tr>
            <td class="feat-img"><img src="{{ $screens['recetas'] }}" alt="Recetas PDF"></td>
            <td class="feat-text">
                <span class="feat-num">03</span>
                <h3>Recetas PDF profesionales</h3>
                <p>Generadas con logo, cédula profesional y firma digital. Se descargan en un clic y se envían al paciente por WhatsApp o email.</p>
                <ul>
                    <li>Plantilla personalizada por doctor</li>
                    <li>Historial de recetas por paciente</li>
                    <li>Firma digital con validez legal</li>
                </ul>
            </td>
        </tr></table>
    </div>

    <div class="footer">Sigue en la siguiente página → Odontograma, cobros, portal del paciente y más</div>
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
                <h3>Odontograma interactivo (dental)</h3>
                <p>Diagrama dental con 13 condiciones. Haces clic en el diente, eliges el estado, se guarda automático. Compartible con el paciente por WhatsApp.</p>
                <ul>
                    <li>Historial visual de cada pieza</li>
                    <li>Colores por tipo de tratamiento</li>
                    <li>Exportable como PDF</li>
                </ul>
            </td>
        </tr></table>
    </div>

    <div class="feat-block">
        <table><tr>
            <td class="feat-img"><img src="{{ $screens['cobros'] }}" alt="Cobros e ingresos"></td>
            <td class="feat-text">
                <span class="feat-num">05</span>
                <h3>Cobros, pagos y reportes de ingresos</h3>
                <p>Registro de cada pago con método (efectivo, transferencia, tarjeta). Control automático de pendientes por paciente. Envía el link de cobro por WhatsApp.</p>
                <ul>
                    <li>Reporte de ingresos del mes en tiempo real</li>
                    <li>Alertas de cobros vencidos</li>
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
                <h3>Escritorio con métricas y alertas</h3>
                <p>Al entrar ves: ingresos del mes, próximas citas, pacientes activos, cobros pendientes. Alertas inteligentes para pacientes inactivos, cumpleaños y recetas vencidas.</p>
                <ul>
                    <li>Comparativa mes vs mes anterior</li>
                    <li>Accesos rápidos a acciones frecuentes</li>
                    <li>Reportes por doctor (plan Clínica)</li>
                </ul>
            </td>
        </tr></table>
    </div>

    <div class="badges-row">
        <span class="badge">+ Check-in con QR</span>
        <span class="badge">+ Firma digital</span>
        <span class="badge">+ Portal paciente</span>
        <span class="badge">+ Multi-sede</span>
        <span class="badge">+ Comisiones entre doctores</span>
    </div>

    <div class="footer">12 módulos en total. Todas las funciones disponibles en el plan Pro.</div>
</div>

{{-- ============================================================ --}}
{{-- PÁGINA 5 — CASO DE ÉXITO + TESTIMONIOS                        --}}
{{-- ============================================================ --}}
<div class="page">
    <div class="header">
        <div class="header-brand">DocFácil <small>Casos reales</small></div>
        <div class="page-number">05</div>
    </div>

    <h2 class="section">Caso: Dra. Fernández (CDMX)</h2>
    <p class="section-sub">Consultorio dental individual. Atendía 80 pacientes/mes con 30% de inasistencia. Esto cambió con DocFácil:</p>

    <table class="case-stats"><tr>
        <td><div class="num">8%</div><div class="label">inasistencia final<br>(antes 30%)</div></td>
        <td><div class="num">+22</div><div class="label">citas atendidas<br>al mes</div></td>
        <td><div class="num">$17K</div><div class="label">ingreso adicional<br>al mes</div></td>
        <td><div class="num">2h</div><div class="label">ahorradas al día<br>en recordatorios</div></td>
    </tr></table>

    <h3 style="font-size:11pt; margin:14px 0 4px 0; color:#0d9488;">Antes vs. después de DocFácil</h3>
    <table class="bar-compare">
        <tr>
            <td class="lbl">Antes</td>
            <td class="bar-wrap"><div class="bar-bg"><div class="bar-fill-red" style="width:95%;"></div></div></td>
            <td class="val" style="color:#ef4444;">30%</td>
        </tr>
        <tr>
            <td class="lbl">Después</td>
            <td class="bar-wrap"><div class="bar-bg"><div class="bar-fill-green" style="width:25%;"></div></div></td>
            <td class="val" style="color:#10b981;">8%</td>
        </tr>
    </table>
    <p class="quote-line">"Lo que pago por DocFácil lo recupero en 2 días de consulta extra al mes. Es la mejor inversión que he hecho." — Dra. M. Fernández</p>

    <h2 class="section" style="font-size:16pt; margin-top:16px;">Lo que dicen otros doctores</h2>
    @foreach ($pages['testimonials'] as $t)
    <div class="testimonial">
        <blockquote>"{{ $t['quote'] }}"</blockquote>
        <div class="author"><strong>{{ $t['name'] }}</strong> · {{ $t['specialty'] }} · {{ $t['city'] }}</div>
    </div>
    @endforeach
</div>

{{-- ============================================================ --}}
{{-- PÁGINA 6 — PRECIOS Y COMPARATIVA                              --}}
{{-- ============================================================ --}}
<div class="page">
    <div class="header">
        <div class="header-brand">DocFácil <small>Precios en pesos mexicanos</small></div>
        <div class="page-number">06</div>
    </div>

    <h2 class="section">Planes pensados para cada consultorio</h2>
    <p class="section-sub">Sin contratos. Sin tarjeta para probar. Cancela cuando quieras.</p>

    <div style="background: #fef3c7; border: 1px solid #f59e0b; border-radius: 6px; padding: 5px 10px; margin-bottom: 6px; font-size: 8.5pt; text-align: center;">
        <strong style="color: #92400e;">💡 Paga anual y ahorra 2 meses</strong> <span style="color:#78350f;">(16.7% de descuento).</span>
    </div>

    <table class="pricing-grid"><tr>
        @foreach ($pages['plans'] as $p)
        @php $visible = array_slice($p['features'], 0, 4); $extraCount = max(0, count($p['features']) - 4); @endphp
        <td class="plan {{ !empty($p['popular']) ? 'popular' : '' }}">
            <h4>{{ $p['name'] }}{!! !empty($p['popular']) ? ' <span class="popular-badge">POPULAR</span>' : '' !!}</h4>
            <div class="price">${{ number_format($p['price']) }}<span style="font-size:9pt; font-weight:normal; color:#6b7280;">/mes</span></div>
            @if ($p['annual'] > 0)
            <div style="font-size:7.5pt; color:#059669; margin:-2px 0 4px 0; font-weight:600;">o ${{ number_format($p['annual']) }}/año · 2 meses gratis</div>
            @else
            <div style="font-size:7.5pt; color:#6b7280; margin:-2px 0 4px 0;">sin tarjeta · sin compromiso</div>
            @endif
            <div class="ideal">{{ $p['ideal'] }}</div>
            <ul>
                @foreach ($visible as $feat)
                <li>{{ $feat }}</li>
                @endforeach
                @if ($extraCount > 0)
                <li style="color:#0d9488; font-weight:600; list-style:none; margin-left:-14px;">+ {{ $extraCount }} features más</li>
                @endif
            </ul>
        </td>
        @endforeach
    </tr></table>
    <p style="font-size:8.5pt; color:#6b7280; margin-top:4px;">14 días gratis con todas las funciones del plan Pro. Sin tarjeta. Sin compromiso. Precios en pesos mexicanos.</p>

    <h2 class="section" style="font-size:14pt; margin-top:14px;">Vs. la competencia</h2>
    <table class="compare">
        <thead>
            <tr>
                <th>Característica</th>
                <th>DocFácil</th>
                <th>Dentrix</th>
                <th>Eaglesoft</th>
                <th>DentalIntel</th>
            </tr>
        </thead>
        <tbody>
            <tr><td>Precio (MXN/mes)</td><td><span class="yes">$149-499</span></td><td>~$3,000</td><td>~$2,500</td><td>~$4,000</td></tr>
            <tr><td>100% en la nube</td><td><span class="yes">✓</span></td><td><span class="no">—</span></td><td><span class="no">—</span></td><td><span class="yes">✓</span></td></tr>
            <tr><td>Soporte en español</td><td><span class="yes">✓</span></td><td><span class="no">—</span></td><td><span class="no">—</span></td><td><span class="no">—</span></td></tr>
            <tr><td>Recordatorios WhatsApp</td><td><span class="yes">✓</span></td><td><span class="no">—</span></td><td><span class="no">—</span></td><td><span class="no">—</span></td></tr>
            <tr><td>Onboarding gratuito</td><td><span class="yes">✓</span></td><td><span class="no">—</span></td><td><span class="no">—</span></td><td><span class="no">—</span></td></tr>
            <tr><td>Portal del paciente</td><td><span class="yes">✓</span></td><td><span class="yes">✓</span></td><td><span class="no">—</span></td><td><span class="yes">✓</span></td></tr>
            <tr><td>Sin contrato anual</td><td><span class="yes">✓</span></td><td><span class="no">—</span></td><td><span class="no">—</span></td><td><span class="no">—</span></td></tr>
        </tbody>
    </table>

    <h3 style="font-size:11pt; margin:12px 0 4px 0; color:#0d9488;">Lo que <em>NO</em> hace DocFácil (transparencia total)</h3>
    <p style="font-size:9pt; color:#4b5563; margin:0;">Facturación CFDI (por ahora), integración con laboratorios dentales externos, teleconsulta por video. Si necesitas alguna de estas, avísanos — está en el roadmap.</p>
</div>

{{-- ============================================================ --}}
{{-- PÁGINA 7 — ECOSISTEMA Y SEGURIDAD                             --}}
{{-- ============================================================ --}}
<div class="page">
    <div class="header">
        <div class="header-brand">DocFácil <small>Ecosistema, seguridad y confianza</small></div>
        <div class="page-number">07</div>
    </div>

    <h2 class="section">Todo lo que viene integrado</h2>
    <p class="section-sub">Sin configuraciones técnicas. Sin hablar con 5 proveedores. Todo listo desde el primer día.</p>

    <table class="eco-grid"><tr>
        <td class="eco-card">
            <div class="eco-icon">💬</div>
            <strong>WhatsApp Business</strong>
            <p>Envía recordatorios, recetas y links de cobro directo al chat del paciente desde tu número.</p>
        </td>
        <td class="eco-card">
            <div class="eco-icon">✉</div>
            <strong>Correo automático</strong>
            <p>Confirmaciones de cita, recibos de pago y seguimientos post-consulta automáticos por email.</p>
        </td>
        <td class="eco-card">
            <div class="eco-icon">💳</div>
            <strong>Pagos en línea</strong>
            <p>Tu paciente paga con tarjeta o transferencia desde el link que le envías por WhatsApp.</p>
        </td>
    </tr><tr>
        <td class="eco-card">
            <div class="eco-icon">📱</div>
            <strong>App PWA instalable</strong>
            <p>Se instala en celular o tablet como app nativa. Funciona incluso con internet intermitente.</p>
        </td>
        <td class="eco-card">
            <div class="eco-icon">☁</div>
            <strong>Respaldo en la nube</strong>
            <p>Backups diarios automáticos. Tus datos viajan contigo sin USBs ni archivos perdidos.</p>
        </td>
        <td class="eco-card">
            <div class="eco-icon">👥</div>
            <strong>Portal del paciente</strong>
            <p>Tus pacientes ven sus citas, recetas e historial. Reduce llamadas rutinarias.</p>
        </td>
    </tr></table>

    <h2 class="section" style="font-size:16pt; margin-top:14px;">Seguridad y cumplimiento</h2>
    <div style="background:#f9fafb; border-radius:10px; padding:14px 16px; border:1px solid #e5e7eb; font-size:9.5pt;">
        <table style="width:100%;">
            <tr>
                <td style="vertical-align:top; width:50%; padding-right:10px;">
                    <p style="margin:0 0 6px 0;"><strong style="color:#0d9488;">🔒 Cifrado TLS 1.3</strong><br>Todas las conexiones y datos en tránsito viajan cifrados.</p>
                    <p style="margin:0 0 6px 0;"><strong style="color:#0d9488;">🇲🇽 Datos en servidores mexicanos</strong><br>Cumplimiento con LFPDPPP (Ley Federal de Protección de Datos).</p>
                    <p style="margin:0;"><strong style="color:#0d9488;">📋 NOM-004-SSA3</strong><br>Expediente clínico estructurado conforme a norma oficial.</p>
                </td>
                <td style="vertical-align:top; width:50%;">
                    <p style="margin:0 0 6px 0;"><strong style="color:#0d9488;">💾 Backups diarios automáticos</strong><br>Restauración punto-en-el-tiempo hasta 30 días atrás.</p>
                    <p style="margin:0 0 6px 0;"><strong style="color:#0d9488;">🔐 Roles y permisos</strong><br>Cada usuario ve solo lo que necesita ver.</p>
                    <p style="margin:0;"><strong style="color:#0d9488;">📤 Exportación libre</strong><br>Descarga todos tus datos cuando quieras, en CSV o PDF.</p>
                </td>
            </tr>
        </table>
    </div>

    <div class="badges-row" style="margin-top:14px;">
        <span class="badge">✓ Hecho en México</span>
        <span class="badge">✓ Soporte en español</span>
        <span class="badge">✓ PWA instalable</span>
        <span class="badge">✓ Sin anuncios</span>
        <span class="badge">✓ Código propio</span>
    </div>
</div>

{{-- ============================================================ --}}
{{-- PÁGINA 8 — CÓMO EMPEZAR Y CTA FINAL                           --}}
{{-- ============================================================ --}}
<div class="page">
    <div class="header">
        <div class="header-brand">DocFácil <small>Cómo empezar hoy</small></div>
        <div class="page-number">08</div>
    </div>

    <h2 class="section">Empieza en 3 pasos</h2>
    <p class="section-sub">Sin instalaciones. Sin tarjeta. Sin perder un solo paciente.</p>

    <table class="steps"><tr>
        <td class="step">
            <div class="num-circle">1</div>
            <h4>Regístrate</h4>
            <p>Crea tu cuenta en 2 minutos. Sin tarjeta. 14 días gratis con plan Pro.</p>
            <img src="{{ $screens['landing'] }}" alt="Registro">
        </td>
        <td class="step">
            <div class="num-circle">2</div>
            <h4>Carga tus pacientes</h4>
            <p>Sube tu Excel o captura manualmente. Te ayudamos si tienes más de 200.</p>
            <img src="{{ $screens['pacientes'] }}" alt="Pacientes">
        </td>
        <td class="step">
            <div class="num-circle">3</div>
            <h4>Agenda tu primer día</h4>
            <p>Abre la agenda, crea tu primera cita, recibe tu primer recordatorio WhatsApp.</p>
            <img src="{{ $screens['calendario'] }}" alt="Agenda">
        </td>
    </tr></table>

    <div class="cta-final">
        <h3>Empieza gratis ahora mismo</h3>
        <p>Escanea el QR o habla directamente con Omar, el fundador.</p>
        <table class="cta-contact"><tr>
            <td class="qr"><img src="{{ $qrDataUri }}" alt="QR registro DocFácil"></td>
            <td class="info">
                <strong>Omar Lerma · Fundador</strong>
                <div><span style="display:inline-block; width:16px; font-weight:bold;">☎</span> <a href="{{ $whatsappLink }}">668 249 3398</a> (WhatsApp)</div>
                <div><span style="display:inline-block; width:16px; font-weight:bold;">✉</span> <a href="mailto:contacto@docfacil.com">contacto@docfacil.com</a></div>
                <div><span style="display:inline-block; width:16px; font-weight:bold;">⌂</span> <a href="{{ url('/') }}">docfacil.tu-app.co</a></div>
                <div style="margin-top:5px; opacity:0.9;">Demo en vivo · Onboarding gratuito · Soporte por WhatsApp</div>
            </td>
        </tr></table>
    </div>

    <div class="footer">
        DocFácil © {{ date('Y') }} · Hecho en México con cariño para doctores mexicanos · docfacil.tu-app.co
    </div>
</div>

</body>
</html>
