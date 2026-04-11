<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: Arial, sans-serif; background: #f4f4f5; margin: 0; padding: 20px; }
        .container { max-width: 600px; margin: 0 auto; background: #fff; border-radius: 8px; overflow: hidden; }
        .header { background: #f59e0b; padding: 30px; text-align: center; color: #fff; }
        .header h1 { margin: 0; font-size: 22px; }
        .content { padding: 30px; color: #333; line-height: 1.6; }
        .btn { display: inline-block; background: #14b8a6; color: #fff; padding: 12px 30px; border-radius: 6px; text-decoration: none; font-weight: bold; margin: 15px 0; }
        .highlight { background: #fffbeb; border: 1px solid #fde68a; padding: 15px; border-radius: 6px; margin: 15px 0; }
        .founder { background: #f0fdfa; border: 2px solid #14b8a6; padding: 15px; border-radius: 6px; margin: 15px 0; }
        .footer { padding: 20px 30px; background: #f9fafb; color: #666; font-size: 12px; text-align: center; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Tu beta termina en {{ $daysLeft }} dias</h1>
        </div>
        <div class="content">
            <p>Hola,</p>
            <p>Tu periodo beta de <strong>{{ $clinic->name }}</strong> en DocFacil termina en <strong>{{ $daysLeft }} dias</strong> ({{ $clinic->beta_ends_at?->format('d/m/Y') }}).</p>

            <div class="highlight">
                <strong>No pierdas tu informacion.</strong> Tus pacientes, expedientes, recetas y citas siguen guardados. Activa tu plan para seguir usandolos.
            </div>

            <div class="founder">
                <strong>Tu precio de fundador de por vida:</strong>
                <p style="margin: 10px 0;">
                    <span style="text-decoration: line-through; color: #999;">$499/mes</span>
                    <span style="font-size: 24px; font-weight: bold; color: #0d9488; margin-left: 10px;">${{ number_format($clinic->founder_price ?? 249, 0) }}/mes</span>
                </p>
                <p style="font-size: 13px; color: #666;">50% de descuento permanente por ser beta tester fundador.</p>
            </div>

            <p style="text-align: center;">
                <a href="https://wa.me/526682493398?text={{ urlencode('Hola, quiero activar mi plan de fundador en DocFacil. Mi consultorio: ' . $clinic->name) }}" style="display:inline-block;background:#14b8a6;color:#ffffff!important;padding:14px 32px;border-radius:8px;text-decoration:none;font-weight:700;font-size:15px;">Activar plan por WhatsApp</a>
            </p>

            <p>— El equipo de DocFacil</p>
        </div>
        <div class="footer">
            &copy; {{ date('Y') }} DocFacil. Todos los derechos reservados.<br><a href="https://docfacil.tu-app.co" style="color:#14b8a6;">docfacil.tu-app.co</a> — Software para consultorios medicos y dentales
        </div>
    </div>
</body>
</html>
