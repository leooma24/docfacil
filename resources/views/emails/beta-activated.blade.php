<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: Arial, sans-serif; background: #f4f4f5; margin: 0; padding: 20px; }
        .container { max-width: 600px; margin: 0 auto; background: #fff; border-radius: 8px; overflow: hidden; }
        .header { background: linear-gradient(135deg, #0d9488, #0891b2); padding: 30px; text-align: center; color: #fff; }
        .header h1 { margin: 0; font-size: 24px; }
        .content { padding: 30px; color: #333; line-height: 1.6; }
        .btn { display: inline-block; background: #14b8a6; color: #fff; padding: 12px 30px; border-radius: 6px; text-decoration: none; font-weight: bold; margin: 15px 0; }
        .highlight { background: #f0fdfa; border: 1px solid #99f6e4; padding: 15px; border-radius: 6px; margin: 15px 0; }
        .footer { padding: 20px 30px; background: #f9fafb; color: #666; font-size: 12px; text-align: center; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Bienvenido al programa beta!</h1>
        </div>
        <div class="content">
            <p>Hola <strong>{{ $doctorName }}</strong>,</p>
            <p>Tu consultorio <strong>{{ $clinic->name }}</strong> ha sido activado como beta tester de DocFacil.</p>

            <div class="highlight">
                <strong>Lo que incluye tu beta:</strong>
                <ul>
                    <li>Plan Pro completo — <strong>6 meses gratis</strong></li>
                    <li>IA integrada: dictado inteligente, sugerencias de diagnóstico, consentimientos automáticos</li>
                    <li>Cobro por WhatsApp + Check-in con QR + Análisis del consultorio</li>
                    <li>Precio de fundador de por vida: <strong>$299/mes</strong> (cuando decidas pagar)</li>
                </ul>
                <p>Tu beta vence el: <strong>{{ $clinic->beta_ends_at?->format('d/m/Y') }}</strong></p>
            </div>

            <p style="text-align: center;">
                <a href="https://docfacil.tu-app.co/doctor" style="display:inline-block;background:#14b8a6;color:#ffffff!important;padding:14px 32px;border-radius:8px;text-decoration:none;font-weight:700;font-size:15px;">Ir a mi consultorio</a>
            </p>

            <p>Si tienes dudas, respondenos por WhatsApp al <strong>668 249 3398</strong>.</p>

            <p>— El equipo de DocFacil</p>
        </div>
        <div class="footer">
            &copy; {{ date('Y') }} DocFacil. Todos los derechos reservados.<br><a href="https://docfacil.tu-app.co" style="color:#14b8a6;">docfacil.tu-app.co</a> — Software para consultorios medicos y dentales
        </div>
    </div>
</body>
</html>
