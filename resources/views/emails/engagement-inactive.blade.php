<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Arial, sans-serif; background: #f4f4f5; margin: 0; padding: 20px; }
        .container { max-width: 600px; margin: 0 auto; background: #fff; border-radius: 12px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.06); }
        .header { background: linear-gradient(135deg, #0d9488, #0891b2); padding: 32px 30px; text-align: center; }
        .header img { height: 36px; margin-bottom: 12px; }
        .header h1 { color: #fff; font-size: 22px; font-weight: 700; margin: 0; }
        .header p { color: rgba(255,255,255,0.85); font-size: 14px; margin-top: 4px; }
        .content { padding: 32px 30px; color: #333; line-height: 1.7; font-size: 15px; }
        .btn { display: inline-block; background: linear-gradient(135deg, #0d9488, #0891b2); color: #fff; padding: 14px 32px; border-radius: 8px; text-decoration: none; font-weight: 700; font-size: 15px; }
        .tip-box { background: #f0fdfa; border-left: 4px solid #14b8a6; padding: 16px 20px; border-radius: 0 8px 8px 0; margin: 20px 0; }
        .tip-box strong { color: #0d9488; }
        .feature-list { margin: 16px 0; padding: 0; list-style: none; }
        .feature-list li { padding: 8px 0 8px 28px; position: relative; color: #374151; font-size: 14px; }
        .feature-list li:before { content: "✓"; position: absolute; left: 0; color: #14b8a6; font-weight: 700; font-size: 16px; }
        .whatsapp-box { background: #dcfce7; border-radius: 8px; padding: 16px 20px; margin: 20px 0; text-align: center; }
        .whatsapp-box a { color: #166534; font-weight: 700; text-decoration: none; font-size: 16px; }
        .divider { height: 1px; background: #e5e7eb; margin: 24px 0; }
        .footer { padding: 24px 30px; background: #f9fafb; text-align: center; border-top: 1px solid #f0f0f0; }
        .footer p { color: #9ca3af; font-size: 12px; line-height: 1.6; }
        .footer a { color: #14b8a6; text-decoration: none; font-weight: 600; }
        .social-links { margin-top: 12px; }
        .social-links a { display: inline-block; margin: 0 6px; color: #9ca3af; text-decoration: none; font-size: 12px; }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <img src="https://docfacil.tu-app.co/images/logo_doc_facil_white.png" alt="DocFácil">
        <h1>Te extrañamos!</h1>
        <p>Tu consultorio te esta esperando</p>
    </div>
    <div class="content">
        <p>Hola <strong>{{ $doctorName }}</strong>,</p>
        <p style="margin-top:12px;">Notamos que no has usado DocFacil en los ultimos dias. Tu consultorio <strong>{{ $clinic->name }}</strong> esta configurado y listo para que empieces a atender pacientes.</p>

        <div class="tip-box">
            <strong>¿Necesitas ayuda para empezar?</strong>
            <p style="margin-top:6px;font-size:14px;color:#374151;">Estamos para ayudarte. Te guiamos paso a paso en 15 minutos.</p>
        </div>

        <p><strong>Recuerda que tu plan incluye:</strong></p>
        <ul class="feature-list">
            <li>Agenda de citas con recordatorios WhatsApp</li>
            <li>Expediente clinico y recetas PDF profesionales</li>
            <li>Cobros, reportes e ingresos en tiempo real</li>
            <li>Consulta rapida: registra paciente + diagnostico + receta en 2 minutos</li>
        </ul>

        <p style="text-align:center;margin-top:24px;">
            <a href="https://docfacil.tu-app.co/doctor" style="display:inline-block;background:linear-gradient(135deg,#0d9488,#0891b2);color:#ffffff!important;padding:14px 32px;border-radius:8px;text-decoration:none;font-weight:700;font-size:15px;">Ir a mi consultorio &rarr;</a>
        </p>

        <div class="whatsapp-box">
            <p style="font-size:13px;color:#166534;margin-bottom:6px;">¿Prefieres que te ayudemos?</p>
            <a href="https://wa.me/526682493398?text={{ urlencode('Hola, necesito ayuda para empezar a usar DocFácil. Mi consultorio: ' . $clinic->name) }}">Escribenos por WhatsApp &rarr; 668 249 3398</a>
        </div>

        <div class="divider"></div>
        <p style="color:#6b7280;font-size:13px;">— El equipo de DocFacil</p>
    </div>
    <div class="footer">
        <p><a href="https://docfacil.tu-app.co">docfacil.tu-app.co</a> — Software para consultorios medicos y dentales</p>
        <p>&copy; {{ date('Y') }} DocFacil. Todos los derechos reservados.</p>
    </div>
</div>
</body>
</html>
