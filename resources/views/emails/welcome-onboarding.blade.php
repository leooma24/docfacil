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
        .btn { display: inline-block; background: #14b8a6; color: #fff; padding: 12px 30px; border-radius: 6px; text-decoration: none; font-weight: bold; margin: 15px 0; }
        .steps { margin: 20px 0; }
        .step { padding: 10px 0; border-bottom: 1px solid #eee; }
        .step-num { display: inline-block; width: 28px; height: 28px; background: #14b8a6; color: #fff; border-radius: 50%; text-align: center; line-height: 28px; margin-right: 10px; font-size: 14px; font-weight: bold; }
        .footer { padding: 20px 30px; background: #f9fafb; color: #666; font-size: 12px; text-align: center; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>¡Bienvenido a DocFácil!</h1>
        </div>
        <div class="content">
            <p>Hola <strong>{{ $user->name }}</strong>,</p>
            <p>Gracias por registrarte en DocFácil. Tu consultorio digital está listo para empezar a trabajar.</p>

            <p><strong>Tienes 15 días de prueba gratuita</strong> con todas las funciones disponibles.</p>

            <div class="steps">
                <p><strong>Primeros pasos (15 min en total):</strong></p>
                <div class="step"><span class="step-num">1</span> Agrega tus servicios y precios (marca con recall los que necesitan seguimiento — limpiezas, revisiones)</div>
                <div class="step"><span class="step-num">2</span> Registra a tus primeros pacientes o súbelos de Excel por WhatsApp</div>
                <div class="step"><span class="step-num">3</span> Crea tu primera cita y manda el recordatorio por WhatsApp a 1 clic</div>
                <div class="step"><span class="step-num">4</span> Genera tu primera receta PDF con tu cédula y firma</div>
            </div>

            <p style="text-align: center;">
                <a href="{{ url('/doctor') }}" style="display:inline-block;background:#14b8a6;color:#ffffff!important;padding:14px 32px;border-radius:8px;text-decoration:none;font-weight:700;font-size:15px;">Ir a mi consultorio</a>
            </p>

            <div style="background:#f0fdfa;border-left:3px solid #14b8a6;padding:14px 16px;margin:20px 0;border-radius:4px;">
                <p style="margin:0 0 8px;font-weight:700;color:#0d9488;">🎁 Como beta tester tienes acceso gratis a los nuevos add-ons por 30 días:</p>
                <p style="margin:0;font-size:14px;color:#4b5563;"><strong>Recall automático</strong> ($49/mes): pacientes que no regresan aparecen listados cada semana.<br><strong>Plan de tratamiento</strong> ($129/mes): arma presupuestos con PDF bonito y el paciente acepta en línea.</p>
            </div>

            <p>Si tienes dudas, responde este correo o escríbeme al WhatsApp <strong>668 249 3398</strong>. Contesto yo personalmente.</p>

            <p>— Omar Lerma, fundador de DocFácil</p>
        </div>
        <div class="footer">
            &copy; {{ date('Y') }} DocFácil. Todos los derechos reservados.<br><a href="https://docfacil.tu-app.co" style="color:#14b8a6;">docfacil.tu-app.co</a> — Software para consultorios médicos y dentales
        </div>
    </div>
</body>
</html>
