<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Arial, sans-serif; background: #f4f4f5; margin: 0; padding: 20px; }
        .container { max-width: 600px; margin: 0 auto; background: #fff; border-radius: 12px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.06); }
        .header { background: linear-gradient(135deg, #8b5cf6, #7c3aed); padding: 32px 30px; text-align: center; }
        .header img { height: 36px; margin-bottom: 12px; }
        .header h1 { color: #fff; font-size: 22px; font-weight: 700; margin: 0; }
        .header p { color: rgba(255,255,255,0.85); font-size: 14px; margin-top: 4px; }
        .content { padding: 32px 30px; color: #333; line-height: 1.7; font-size: 15px; }
        .btn { display: inline-block; background: linear-gradient(135deg, #0d9488, #0891b2); color: #fff; padding: 14px 32px; border-radius: 8px; text-decoration: none; font-weight: 700; font-size: 15px; }
        .stat-box { background: linear-gradient(135deg, #f0fdfa, #ecfdf5); border: 1px solid #99f6e4; border-radius: 12px; padding: 20px; margin: 20px 0; text-align: center; }
        .stat-box .number { font-size: 36px; font-weight: 800; color: #0d9488; }
        .stat-box .label { font-size: 14px; color: #6b7280; margin-top: 4px; }
        .divider { height: 1px; background: #e5e7eb; margin: 24px 0; }
        .footer { padding: 24px 30px; background: #f9fafb; text-align: center; border-top: 1px solid #f0f0f0; }
        .footer p { color: #9ca3af; font-size: 12px; line-height: 1.6; }
        .footer a { color: #14b8a6; text-decoration: none; font-weight: 600; }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <img src="https://docfacil.tu-app.co/images/logo_doc_facil_white.png" alt="DocFácil">
        <h1>Agenda tu primera cita</h1>
        <p>Y activa los recordatorios automaticos</p>
    </div>
    <div class="content">
        <p>Hola <strong>{{ $doctorName }}</strong>,</p>
        <p style="margin-top:12px;">Ya tienes pacientes registrados en <strong>{{ $clinic->name }}</strong>. El siguiente paso es agendar tu primera cita y ver la magia de los recordatorios automaticos.</p>

        <div class="stat-box">
            <div class="number">40%</div>
            <div class="label">menos inasistencias con recordatorios WhatsApp</div>
        </div>

        <p><strong>3 formas de agendar:</strong></p>
        <table width="100%" cellpadding="0" cellspacing="0" style="margin:16px 0;">
            <tr>
                <td style="padding:8px;text-align:center;background:#f0fdfa;border-radius:8px;">
                    <div style="font-size:24px;">&#9889;</div>
                    <div style="font-weight:700;font-size:12px;color:#111;margin-top:4px;">Consulta rapida</div>
                    <div style="font-size:11px;color:#6b7280;">Desde el dashboard</div>
                </td>
                <td style="width:12px;"></td>
                <td style="padding:8px;text-align:center;background:#eff6ff;border-radius:8px;">
                    <div style="font-size:24px;">&#128197;</div>
                    <div style="font-weight:700;font-size:12px;color:#111;margin-top:4px;">Calendario</div>
                    <div style="font-size:11px;color:#6b7280;">Arrastra y suelta</div>
                </td>
                <td style="width:12px;"></td>
                <td style="padding:8px;text-align:center;background:#faf5ff;border-radius:8px;">
                    <div style="font-size:24px;">&#10133;</div>
                    <div style="font-weight:700;font-size:12px;color:#111;margin-top:4px;">Nueva cita</div>
                    <div style="font-size:11px;color:#6b7280;">Boton en dashboard</div>
                </td>
            </tr>
        </table>

        <p style="text-align:center;margin-top:24px;">
            <a href="https://docfacil.tu-app.co/doctor" class="btn">Agendar primera cita &rarr;</a>
        </p>

        <div class="divider"></div>
        <p style="color:#6b7280;font-size:13px;">¿Dudas? Escribenos al <a href="https://wa.me/526682493398" style="color:#14b8a6;font-weight:600;">668 249 3398</a> por WhatsApp.</p>
        <p style="color:#6b7280;font-size:13px;margin-top:8px;">— El equipo de DocFacil</p>
    </div>
    <div class="footer">
        <p><a href="https://docfacil.tu-app.co">docfacil.tu-app.co</a> — Software para consultorios medicos y dentales</p>
        <p>&copy; {{ date('Y') }} DocFacil. Todos los derechos reservados.</p>
    </div>
</div>
</body>
</html>
