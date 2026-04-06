<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: Arial, sans-serif; background: #f4f4f5; margin: 0; padding: 20px; }
        .container { max-width: 600px; margin: 0 auto; background: #fff; border-radius: 8px; overflow: hidden; }
        .header { background: #ef4444; padding: 30px; text-align: center; color: #fff; }
        .header h1 { margin: 0; font-size: 22px; }
        .content { padding: 30px; color: #333; line-height: 1.6; }
        .btn { display: inline-block; background: #14b8a6; color: #fff; padding: 12px 30px; border-radius: 6px; text-decoration: none; font-weight: bold; margin: 15px 0; }
        .highlight { background: #fef2f2; border: 1px solid #fecaca; padding: 15px; border-radius: 6px; margin: 15px 0; }
        .footer { padding: 20px 30px; background: #f9fafb; color: #666; font-size: 12px; text-align: center; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Tu prueba gratuita ha terminado</h1>
        </div>
        <div class="content">
            <p>Hola,</p>
            <p>La prueba gratuita de <strong>{{ $clinic->name }}</strong> en DocFácil ha expirado.</p>

            <div class="highlight">
                <strong>Tu información está segura.</strong> Tus pacientes, citas y expedientes siguen guardados. Activa un plan para volver a acceder.
            </div>

            <p>Activa hoy y obtén un <strong>20% de descuento</strong> en tu primer mes.</p>

            <p style="text-align: center;">
                <a href="{{ url('/doctor') }}" style="display:inline-block;background:#14b8a6;color:#ffffff!important;padding:14px 32px;border-radius:8px;text-decoration:none;font-weight:700;font-size:15px;">Activar mi plan ahora</a>
            </p>

            <p>— El equipo de DocFácil</p>
        </div>
        <div class="footer">
            &copy; {{ date('Y') }} DocFácil. Todos los derechos reservados.<br><a href="https://docfacil.tu-app.co" style="color:#14b8a6;">docfacil.tu-app.co</a> — Software para consultorios médicos y dentales
        </div>
    </div>
</body>
</html>
