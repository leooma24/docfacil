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
        .btn { display: inline-block; background: #14b8a6; color: #ffffff !important; padding: 14px 32px; border-radius: 8px; text-decoration: none; font-weight: 700; font-size: 15px; margin: 15px 0; }
        .urgency { background: #fffbeb; border: 2px solid #f59e0b; padding: 15px; margin: 20px 0; border-radius: 8px; text-align: center; }
        .footer { padding: 20px 30px; background: #f9fafb; color: #666; font-size: 12px; text-align: center; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Últimos lugares en el Beta de DocFácil</h1>
        </div>
        <div class="content">
            <p>Hola <strong>{{ $prospectName }}</strong>,</p>

            <p>Este es mi último correo sobre el programa beta de DocFácil. No quiero ser insistente, solo asegurarme de que no te pierdas esta oportunidad.</p>

            <div class="urgency">
                <strong>🔥 Quedan pocos lugares en el beta gratuito</strong><br>
                Los consultorios que se registren ahora obtienen acceso de por vida al precio beta cuando lancemos.
            </div>

            <p><strong>Resumen rápido de lo que obtienes GRATIS:</strong></p>
            <ul>
                <li>Agenda digital con recordatorios WhatsApp</li>
                <li>Expedientes clínicos organizados</li>
                <li>Recetas PDF profesionales</li>
                <li>Control de cobros y pagos</li>
                <li>Soporte directo conmigo por WhatsApp</li>
            </ul>

            <p style="text-align: center; margin: 25px 0;">
                <a href="{{ url('/register') }}" class="btn">Registrarme ahora (es gratis)</a>
            </p>

            <p>Si no es para ti, no hay problema. Te deseo mucho éxito con tu consultorio. 🙌</p>

            <p>Saludos,<br><strong>Omar Lerma</strong><br>Fundador de DocFácil<br>WhatsApp: 668 249 3398</p>
        </div>
        <div class="footer">
            &copy; {{ date('Y') }} DocFácil. Todos los derechos reservados.<br>
            <a href="{{ url('/') }}" style="color:#14b8a6;">docfacil.tu-app.co</a> — Software para consultorios médicos y dentales<br>
            <small>Este es el último correo que recibirás de nosotros.</small>
        </div>
    </div>
</body>
</html>
