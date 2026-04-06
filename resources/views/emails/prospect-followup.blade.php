<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: Arial, sans-serif; background: #f4f4f5; margin: 0; padding: 20px; }
        .container { max-width: 600px; margin: 0 auto; background: #fff; border-radius: 8px; overflow: hidden; }
        .header { background: #14b8a6; padding: 30px; text-align: center; color: #fff; }
        .header h1 { margin: 0; font-size: 22px; }
        .content { padding: 30px; color: #333; line-height: 1.6; }
        .btn { display: inline-block; background: #14b8a6; color: #ffffff !important; padding: 14px 32px; border-radius: 8px; text-decoration: none; font-weight: 700; font-size: 15px; margin: 15px 0; }
        .testimonial { background: #f0fdfa; border-left: 4px solid #14b8a6; padding: 15px; margin: 20px 0; border-radius: 4px; font-style: italic; }
        .footer { padding: 20px 30px; background: #f9fafb; color: #666; font-size: 12px; text-align: center; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>¿Sigues usando agenda de papel?</h1>
        </div>
        <div class="content">
            <p>Hola <strong>{{ $prospectName }}</strong>,</p>

            <p>Te escribí hace unos días sobre DocFácil y quería darte seguimiento.</p>

            <p>Entiendo que como {{ $specialty ?? 'profesional de la salud' }} tu tiempo es valioso. Por eso DocFácil está diseñado para que en <strong>menos de 5 minutos</strong> tengas tu consultorio digital funcionando.</p>

            <div class="testimonial">
                "Antes perdía 30 minutos al día buscando expedientes. Con DocFácil todo está a un clic."
                <br>— Dr. en fase beta, Culiacán
            </div>

            <p><strong>Lo que otros consultorios ya están logrando:</strong></p>
            <ul>
                <li>📉 <strong>50% menos</strong> citas perdidas gracias a recordatorios WhatsApp</li>
                <li>⏱️ <strong>20 min/día</strong> que se ahorran en papelería</li>
                <li>💰 <strong>Mejor control</strong> de pagos pendientes</li>
            </ul>

            <p style="text-align: center; margin: 25px 0;">
                <a href="{{ url('/register') }}" class="btn">Activar mi prueba gratuita</a>
            </p>

            <p>¿Tienes dudas? Responde este correo o márcame al <strong>668 249 3398</strong>. Con gusto te hago una demo personalizada.</p>

            <p>Saludos,<br><strong>Omar Lerma</strong><br>Fundador de DocFácil</p>
        </div>
        <div class="footer">
            &copy; {{ date('Y') }} DocFácil. Todos los derechos reservados.<br>
            <a href="{{ url('/') }}" style="color:#14b8a6;">docfacil.tu-app.co</a> — Software para consultorios médicos y dentales<br>
            <small>Si no deseas recibir más correos, responde con "No me interesa".</small>
        </div>
    </div>
</body>
</html>
