<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: Arial, sans-serif; background: #f4f4f5; margin: 0; padding: 20px; }
        .container { max-width: 600px; margin: 0 auto; background: #fff; border-radius: 8px; overflow: hidden; }
        .header { background: #14b8a6; padding: 30px; text-align: center; color: #fff; }
        .header h1 { margin: 0; font-size: 24px; }
        .content { padding: 30px; color: #333; line-height: 1.6; }
        .btn { display: inline-block; background: #14b8a6; color: #ffffff !important; padding: 14px 32px; border-radius: 8px; text-decoration: none; font-weight: 700; font-size: 15px; margin: 15px 0; }
        .benefit { padding: 8px 0; }
        .benefit strong { color: #14b8a6; }
        .footer { padding: 20px 30px; background: #f9fafb; color: #666; font-size: 12px; text-align: center; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>DocFácil — Invitación Beta Exclusiva</h1>
        </div>
        <div class="content">
            <p>Hola <strong>{{ $prospectName }}</strong>,</p>

            <p>Soy Omar del equipo de <strong>DocFácil</strong>, un software diseñado especialmente para consultorios médicos y dentales en Sinaloa.</p>

            <p>Estamos seleccionando consultorios para nuestro <strong>programa beta gratuito</strong> y {{ $clinicName ? 'pensamos que ' . $clinicName . ' sería ideal' : 'nos encantaría contar contigo' }}.</p>

            <p><strong>¿Qué incluye el beta gratuito?</strong></p>
            <div class="benefit">✅ <strong>Agenda de citas</strong> con calendario visual</div>
            <div class="benefit">✅ <strong>Expediente clínico</strong> digital de pacientes</div>
            <div class="benefit">✅ <strong>Recetas PDF</strong> profesionales con tu firma</div>
            <div class="benefit">✅ <strong>Recordatorios WhatsApp</strong> automáticos a pacientes</div>
            <div class="benefit">✅ <strong>Control de cobros</strong> y pagos pendientes</div>
            <div class="benefit">✅ <strong>Sin costo</strong> durante todo el periodo beta</div>

            <p style="text-align: center; margin: 25px 0;">
                <a href="{{ url('/register') }}" class="btn">Quiero probar DocFácil gratis</a>
            </p>

            <p>Solo estamos aceptando <strong>100 consultorios</strong> en esta primera fase. Si te interesa, responde este correo o regístrate directamente.</p>

            <p>Saludos,<br><strong>Omar Lerma</strong><br>Fundador de DocFácil<br>Tel: 668 249 3398</p>
        </div>
        <div class="footer">
            &copy; {{ date('Y') }} DocFácil. Todos los derechos reservados.<br>
            <a href="{{ url('/') }}" style="color:#14b8a6;">docfacil.tu-app.co</a> — Software para consultorios médicos y dentales<br>
            <small>Si no deseas recibir más correos, responde con "No me interesa".</small>
        </div>
    </div>
</body>
</html>
