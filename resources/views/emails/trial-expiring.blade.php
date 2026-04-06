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
        .footer { padding: 20px 30px; background: #f9fafb; color: #666; font-size: 12px; text-align: center; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Tu prueba gratuita termina pronto</h1>
        </div>
        <div class="content">
            <p>Hola,</p>
            <p>Tu prueba gratuita de <strong>{{ $clinic->name }}</strong> en DocFácil termina en <strong>{{ $daysLeft }} días</strong>.</p>

            <div class="highlight">
                <strong>No pierdas tu información.</strong> Actualiza tu plan para seguir gestionando tu consultorio sin interrupciones.
            </div>

            <p>Planes desde <strong>$149/mes</strong> con citas ilimitadas y recordatorios WhatsApp.</p>

            <p style="text-align: center;">
                <a href="{{ url('/doctor') }}" class="btn">Actualizar mi plan</a>
            </p>

            <p>— El equipo de DocFácil</p>
        </div>
        <div class="footer">
            &copy; {{ date('Y') }} DocFácil. Todos los derechos reservados.
        </div>
    </div>
</body>
</html>
