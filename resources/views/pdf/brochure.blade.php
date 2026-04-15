<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>DocFácil — Brochure</title>
    <style>
        @page { margin: 1.1cm; }
        * { box-sizing: border-box; }
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            color: #1f2937;
            font-size: 10pt;
            line-height: 1.5;
            margin: 0;
            padding: 0;
        }
        .page { page-break-after: always; padding: 0; }
        .page:last-child { page-break-after: auto; }

        /* PORTADA */
        .cover { background: linear-gradient(135deg, #0d9488 0%, #06b6d4 100%); color: white; padding: 80px 40px; text-align: center; border-radius: 14px; min-height: 720px; position: relative; }
        .cover .tag { display: inline-block; background: rgba(255,255,255,0.2); padding: 6px 16px; border-radius: 20px; font-size: 9.5pt; letter-spacing: 1px; margin-bottom: 24px; }
        .cover h1 { font-size: 42pt; font-weight: 800; margin: 0 0 12px 0; letter-spacing: -1px; line-height: 1.05; }
        .cover .sub { font-size: 14pt; opacity: 0.95; max-width: 80%; margin: 0 auto 36px auto; line-height: 1.4; }
        .cover .divider { width: 60px; height: 3px; background: white; margin: 20px auto; border-radius: 2px; }
        .cover .card { background: white; color: #1f2937; padding: 18px 24px; border-radius: 12px; display: inline-block; margin-top: 40px; box-shadow: 0 20px 40px rgba(0,0,0,0.2); }
        .cover .card strong { display: block; color: #0d9488; font-size: 16pt; margin-bottom: 4px; }
        .cover .card small { color: #6b7280; font-size: 9pt; }
        .cover .year { position: absolute; bottom: 24px; right: 32px; font-size: 9pt; opacity: 0.7; }

        /* HEADERS GENERALES */
        .header { border-bottom: 3px solid #14b8a6; padding-bottom: 10px; margin-bottom: 22px; }
        .header-brand { font-size: 16pt; font-weight: 800; color: #0d9488; letter-spacing: -0.5px; }
        .header-brand small { font-weight: normal; color: #6b7280; font-size: 9pt; display: block; margin-top: 2px; }
        .page-number { float: right; margin-top: -32px; font-size: 9pt; color: #9ca3af; }

        h2.section { font-size: 22pt; color: #0d9488; margin: 0 0 6px 0; letter-spacing: -0.5px; font-weight: 800; line-height: 1.15; }
        .section-sub { font-size: 11pt; color: #6b7280; margin: 0 0 20px 0; }

        /* ICP */
        .icp-grid { width: 100%; margin: 16px 0; }
        .icp-card { background: #f0fdfa; border-left: 4px solid #14b8a6; padding: 14px 16px; border-radius: 8px; margin-bottom: 10px; }
        .icp-card h3 { margin: 0 0 4px 0; color: #0d9488; font-size: 11pt; }
        .icp-card p { margin: 0; font-size: 9.5pt; }

        /* Pains */
        .pain-grid { width: 100%; border-collapse: separate; border-spacing: 8px; margin: 10px 0; }
        .pain-cell { background: #fef2f2; border-left: 3px solid #ef4444; padding: 10px 12px; border-radius: 6px; width: 50%; vertical-align: top; }
        .pain-cell strong { color: #b91c1c; display: block; margin-bottom: 2px; font-size: 10pt; }
        .pain-cell span { font-size: 9pt; color: #4b5563; }

        /* Features page */
        .feat-grid { width: 100%; border-collapse: separate; border-spacing: 6px; }
        .feat-cell { background: #f9fafb; border-radius: 8px; padding: 10px 12px; vertical-align: top; width: 50%; border: 1px solid #e5e7eb; }
        .feat-cell .feat-icon { font-size: 16pt; display: block; margin-bottom: 4px; }
        .feat-cell strong { color: #0d9488; display: block; font-size: 10.5pt; margin-bottom: 2px; }
        .feat-cell p { margin: 0; font-size: 8.5pt; color: #4b5563; line-height: 1.4; }

        /* Testimonials */
        .testimonial { background: #f9fafb; border-left: 3px solid #14b8a6; padding: 14px 18px; border-radius: 8px; margin-bottom: 12px; }
        .testimonial blockquote { margin: 0 0 8px 0; font-size: 11pt; color: #1f2937; font-style: italic; line-height: 1.5; }
        .testimonial .author { font-size: 9.5pt; color: #6b7280; }
        .testimonial .author strong { color: #0d9488; }

        /* Case stats */
        .case-stats { width: 100%; margin: 14px 0; background: linear-gradient(135deg, #0d9488 0%, #06b6d4 100%); color: white; padding: 18px; border-radius: 12px; }
        .case-stats td { text-align: center; padding: 6px; }
        .case-stats .num { font-size: 24pt; font-weight: 800; line-height: 1; }
        .case-stats .label { font-size: 9pt; opacity: 0.95; margin-top: 4px; }

        /* Pricing */
        .pricing-grid { width: 100%; border-collapse: separate; border-spacing: 8px; }
        .plan { background: white; border: 2px solid #e5e7eb; border-radius: 10px; padding: 12px 14px; vertical-align: top; width: 25%; }
        .plan.popular { border-color: #ea580c; background: #fff7ed; }
        .plan h4 { margin: 0 0 2px 0; font-size: 11pt; color: #111827; }
        .plan .price { font-size: 16pt; font-weight: 800; color: #0d9488; margin: 4px 0; }
        .plan.popular .price { color: #ea580c; }
        .plan .ideal { font-size: 8pt; color: #6b7280; margin-bottom: 8px; min-height: 28px; }
        .plan ul { margin: 0; padding-left: 14px; font-size: 8.5pt; }
        .plan li { margin-bottom: 2px; }
        .popular-badge { display: inline-block; background: #ea580c; color: white; font-size: 7.5pt; padding: 1px 6px; border-radius: 4px; margin-left: 4px; vertical-align: middle; }

        /* Comparison */
        .compare { width: 100%; border-collapse: collapse; margin: 10px 0; font-size: 9pt; }
        .compare th { background: #0d9488; color: white; padding: 6px 8px; text-align: center; font-size: 9pt; }
        .compare th:first-child { text-align: left; }
        .compare td { padding: 5px 8px; border-bottom: 1px solid #e5e7eb; text-align: center; }
        .compare td:first-child { text-align: left; font-weight: 600; color: #374151; }
        .compare .yes { color: #059669; font-weight: bold; }
        .compare .no { color: #9ca3af; }

        /* Final CTA */
        .steps { width: 100%; margin: 20px 0; }
        .step { display: inline-block; width: 31%; vertical-align: top; text-align: center; padding: 16px 10px; margin-right: 2%; background: #f9fafb; border-radius: 10px; }
        .step:last-child { margin-right: 0; }
        .step .num { font-size: 24pt; font-weight: 800; color: #14b8a6; line-height: 1; margin-bottom: 6px; }
        .step h4 { margin: 0 0 4px 0; font-size: 11pt; color: #111827; }
        .step p { margin: 0; font-size: 9pt; color: #6b7280; }

        .cta-final { background: linear-gradient(135deg, #0d9488 0%, #06b6d4 100%); color: white; padding: 26px 28px; border-radius: 14px; margin-top: 16px; }
        .cta-final h3 { margin: 0 0 6px 0; font-size: 18pt; }
        .cta-final p { margin: 0 0 12px 0; opacity: 0.95; }
        .cta-contact { width: 100%; margin-top: 14px; }
        .cta-contact .qr { width: 150px; text-align: center; }
        .cta-contact .qr img { width: 140px; height: 140px; background: white; padding: 6px; border-radius: 8px; }
        .cta-contact .info { padding-left: 20px; vertical-align: middle; font-size: 10pt; }
        .cta-contact .info strong { display: block; font-size: 12pt; margin-bottom: 4px; }
        .cta-contact .info a { color: white; text-decoration: none; }
        .cta-contact .info div { margin-bottom: 3px; }

        .footer { border-top: 1px solid #e5e7eb; margin-top: 18px; padding-top: 8px; font-size: 8pt; color: #9ca3af; text-align: center; }
    </style>
</head>
<body>

{{-- ============================================================ --}}
{{-- PÁGINA 1 — PORTADA                                           --}}
{{-- ============================================================ --}}
<div class="page">
    <div class="cover">
        <div class="tag">BROCHURE · VERSIÓN 2026</div>
        <h1>DocFácil</h1>
        <div class="divider"></div>
        <p class="sub">Software para consultorios médicos y dentales.<br>Agenda, expedientes, recetas PDF, recordatorios WhatsApp y cobros — todo en un solo lugar.</p>
        <div class="card">
            <strong>Tu consultorio, organizado y al día</strong>
            <small>500+ consultorios activos · 15,000+ citas gestionadas · 4.9/5 satisfacción</small>
        </div>
        <div class="year">docfacil.tu-app.co</div>
    </div>
</div>

{{-- ============================================================ --}}
{{-- PÁGINA 2 — PARA QUIÉN ES DOCFÁCIL                            --}}
{{-- ============================================================ --}}
<div class="page">
    <div class="header">
        <div class="header-brand">DocFácil <small>Para quién es</small></div>
        <div class="page-number">02</div>
    </div>

    <h2 class="section">Para doctores que aún dependen del papel</h2>
    <p class="section-sub">DocFácil está diseñado para consultorios pequeños y medianos en México que quieren digitalizarse sin complicarse.</p>

    <div class="icp-grid">
        <div class="icp-card">
            <h3>🦷 Consultorios dentales de 1 a 3 doctores</h3>
            <p>Odontólogos generales, ortodoncistas, endodoncistas. Consultorios que atienden 30-200 pacientes al mes y necesitan dejar el papel, Excel o la agenda de pared.</p>
        </div>
        <div class="icp-card">
            <h3>🩺 Consultorios médicos generales y de especialidad</h3>
            <p>Médicos generales, pediatras, ginecólogos, dermatólogos. Consultorios que facturan entre $20K y $200K al mes y pierden tiempo en tareas administrativas.</p>
        </div>
        <div class="icp-card">
            <h3>🏥 Clínicas pequeñas con varios doctores</h3>
            <p>Clínicas multidisciplinarias con 3-10 doctores que necesitan agenda compartida, comisiones entre doctores y reportes por profesional.</p>
        </div>
    </div>

    <h2 class="section" style="font-size:16pt; margin-top:20px;">4 dolores que vivimos todos los días</h2>
    <table class="pain-grid"><tr>
        <td class="pain-cell">
            <strong>📋 Agenda caótica</strong>
            <span>Papel y Excel: pierdes citas, no buscas rápido, cada cambio pesa.</span>
        </td>
        <td class="pain-cell">
            <strong>📞 Pacientes no llegan</strong>
            <span>El 30% no se presenta. Consultas perdidas que no regresan.</span>
        </td>
    </tr><tr>
        <td class="pain-cell">
            <strong>✍ Recetas a mano</strong>
            <span>Letra ilegible, sin copia, sin respaldo. Riesgo legal y profesional.</span>
        </td>
        <td class="pain-cell">
            <strong>💸 No sabes si ganas</strong>
            <span>Sin reportes ni control de cobros. Decisiones a ojo.</span>
        </td>
    </tr></table>

    <div class="footer">Si te identificaste con 2 o más de estos puntos, DocFácil fue pensado para ti.</div>
</div>

{{-- ============================================================ --}}
{{-- PÁGINA 3 — MÓDULOS (12 FEATURES)                             --}}
{{-- ============================================================ --}}
<div class="page">
    <div class="header">
        <div class="header-brand">DocFácil <small>12 módulos en un solo sistema</small></div>
        <div class="page-number">03</div>
    </div>

    <h2 class="section">Todo lo que necesitas</h2>
    <p class="section-sub">No contrates 5 apps distintas. DocFácil integra todo el flujo del consultorio en una sola plataforma.</p>

    <table class="feat-grid">
        @foreach (array_chunk($pages['features'], 2) as $row)
        <tr>
            @foreach ($row as $f)
            <td class="feat-cell">
                <span class="feat-icon">{{ $f['icon'] }}</span>
                <strong>{{ $f['title'] }}</strong>
                <p>{{ $f['desc'] }}</p>
            </td>
            @endforeach
        </tr>
        @endforeach
    </table>

    <div class="footer">Todas las funciones disponibles en el plan Pro. Algunas limitadas en planes inferiores.</div>
</div>

{{-- ============================================================ --}}
{{-- PÁGINA 4 — TESTIMONIOS + CASOS DE USO                        --}}
{{-- ============================================================ --}}
<div class="page">
    <div class="header">
        <div class="header-brand">DocFácil <small>Doctores que ya lo usan</small></div>
        <div class="page-number">04</div>
    </div>

    <h2 class="section">Lo que dicen los doctores</h2>
    <p class="section-sub">500+ consultorios confían en DocFácil en México. Estos son algunos de sus resultados.</p>

    @foreach ($pages['testimonials'] as $t)
    <div class="testimonial">
        <blockquote>"{{ $t['quote'] }}"</blockquote>
        <div class="author"><strong>{{ $t['name'] }}</strong> · {{ $t['specialty'] }} · {{ $t['city'] }}</div>
    </div>
    @endforeach

    <h2 class="section" style="font-size:16pt; margin-top:20px;">Caso: Dra. Fernández (CDMX)</h2>
    <p style="font-size:10pt; margin:0 0 10px 0;">
        Consultorio dental individual. Atendía 80 pacientes/mes con 30% de inasistencia.
        Después de implementar DocFácil con recordatorios WhatsApp:
    </p>
    <table class="case-stats"><tr>
        <td><div class="num">8%</div><div class="label">inasistencia final<br>(antes 30%)</div></td>
        <td><div class="num">+22</div><div class="label">citas atendidas<br>al mes</div></td>
        <td><div class="num">$17K</div><div class="label">ingreso adicional<br>al mes</div></td>
        <td><div class="num">2h</div><div class="label">ahorradas al día<br>en recordatorios</div></td>
    </tr></table>

    <p style="font-size:9.5pt; color:#6b7280; margin:10px 0 0 0; font-style:italic;">
        "Lo que pago por DocFácil lo recupero en 2 días de consulta extra al mes. Es la mejor inversión que he hecho."
    </p>
</div>

{{-- ============================================================ --}}
{{-- PÁGINA 5 — PRECIOS Y COMPARATIVA                             --}}
{{-- ============================================================ --}}
<div class="page">
    <div class="header">
        <div class="header-brand">DocFácil <small>Precios en pesos mexicanos</small></div>
        <div class="page-number">05</div>
    </div>

    <h2 class="section">Planes pensados para cada consultorio</h2>
    <p class="section-sub">Sin contratos. Sin tarjeta para probar. Cancela cuando quieras.</p>

    <table class="pricing-grid"><tr>
        @foreach ($pages['plans'] as $p)
        <td class="plan {{ !empty($p['popular']) ? 'popular' : '' }}">
            <h4>{{ $p['name'] }}{!! !empty($p['popular']) ? ' <span class="popular-badge">POPULAR</span>' : '' !!}</h4>
            <div class="price">${{ number_format($p['price']) }}<span style="font-size:10pt; font-weight:normal; color:#6b7280;">/mes</span></div>
            <div class="ideal">{{ $p['ideal'] }}</div>
            <ul>
                @foreach ($p['features'] as $feat)
                <li>{{ $feat }}</li>
                @endforeach
            </ul>
        </td>
        @endforeach
    </tr></table>
    <p style="font-size:8.5pt; color:#6b7280; margin-top:6px;">14 días gratis con todas las funciones del plan Pro. Sin tarjeta. Sin compromiso.</p>

    <h2 class="section" style="font-size:14pt; margin-top:20px;">Vs. la competencia</h2>
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
</div>

{{-- ============================================================ --}}
{{-- PÁGINA 6 — CÓMO EMPEZAR                                      --}}
{{-- ============================================================ --}}
<div class="page">
    <div class="header">
        <div class="header-brand">DocFácil <small>Cómo empezar hoy</small></div>
        <div class="page-number">06</div>
    </div>

    <h2 class="section">Empieza en 3 pasos</h2>
    <p class="section-sub">Sin instalaciones. Sin tarjeta. Sin perder un solo paciente.</p>

    <div class="steps">
        <div class="step">
            <div class="num">1</div>
            <h4>Regístrate</h4>
            <p>Crea tu cuenta en 2 minutos. Sin tarjeta de crédito. 14 días gratis con todo el plan Pro.</p>
        </div>
        <div class="step">
            <div class="num">2</div>
            <h4>Importa pacientes</h4>
            <p>Sube tu Excel o carga manualmente. Nuestro equipo te ayuda si tienes más de 200 pacientes.</p>
        </div>
        <div class="step">
            <div class="num">3</div>
            <h4>Úsalo en consulta</h4>
            <p>Abre DocFácil en tu celular, tablet o PC. Desde el primer día ya estás digitalizado.</p>
        </div>
    </div>

    <div class="cta-final">
        <h3>Empieza gratis ahora</h3>
        <p>Escanea el QR o habla directamente con Omar, el fundador.</p>
        <table class="cta-contact"><tr>
            <td class="qr">
                <img src="{{ $qrDataUri }}" alt="QR registro DocFácil">
            </td>
            <td class="info">
                <strong>Omar Lerma · Fundador</strong>
                <div>📱 <a href="{{ $whatsappLink }}">668 249 3398</a> (WhatsApp)</div>
                <div>✉ <a href="mailto:contacto@docfacil.com">contacto@docfacil.com</a></div>
                <div>🌐 <a href="{{ url('/') }}">docfacil.tu-app.co</a></div>
                <div style="margin-top:6px; opacity:0.9;">Demo en vivo · Onboarding gratuito · Soporte por WhatsApp</div>
            </td>
        </tr></table>
    </div>

    <div class="footer">
        DocFácil © {{ date('Y') }} · Hecho en México con cariño para doctores mexicanos · docfacil.tu-app.co
    </div>
</div>

</body>
</html>
