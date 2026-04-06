<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Arial, sans-serif; background: #f4f4f5; margin: 0; padding: 20px; }
        .container { max-width: 600px; margin: 0 auto; background: #fff; border-radius: 12px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.06); }
        .header { background: linear-gradient(135deg, #3b82f6, #2563eb); padding: 32px 30px; text-align: center; }
        .header img { height: 36px; margin-bottom: 12px; }
        .header h1 { color: #fff; font-size: 22px; font-weight: 700; margin: 0; }
        .header p { color: rgba(255,255,255,0.85); font-size: 14px; margin-top: 4px; }
        .content { padding: 32px 30px; color: #333; line-height: 1.7; font-size: 15px; }
        .btn { display: inline-block; background: linear-gradient(135deg, #0d9488, #0891b2); color: #fff; padding: 14px 32px; border-radius: 8px; text-decoration: none; font-weight: 700; font-size: 15px; }
        .steps { margin: 20px 0; }
        .step { display: flex; align-items: flex-start; gap: 12px; padding: 12px 16px; margin-bottom: 8px; background: #f8fafc; border-radius: 8px; border-left: 3px solid #14b8a6; }
        .step-num { width: 28px; height: 28px; background: #14b8a6; color: #fff; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 13px; flex-shrink: 0; }
        .step-text { font-size: 14px; color: #374151; }
        .step-text strong { color: #111; }
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
        <h1>Registra tu primer paciente</h1>
        <p>Toma menos de 1 minuto</p>
    </div>
    <div class="content">
        <p>Hola <strong>{{ $doctorName }}</strong>,</p>
        <p style="margin-top:12px;">Tu consultorio <strong>{{ $clinic->name }}</strong> esta listo. Solo falta lo mas importante: tu primer paciente. Es muy facil:</p>

        <div class="steps">
            <div class="step">
                <div class="step-num">1</div>
                <div class="step-text"><strong>Entra a "Consulta"</strong> en el menu lateral</div>
            </div>
            <div class="step">
                <div class="step-num">2</div>
                <div class="step-text"><strong>Click en "Paciente nuevo"</strong> y llena nombre y telefono</div>
            </div>
            <div class="step">
                <div class="step-num">3</div>
                <div class="step-text"><strong>Selecciona un servicio</strong> y dale "Iniciar consulta"</div>
            </div>
            <div class="step">
                <div class="step-num">4</div>
                <div class="step-text"><strong>Listo!</strong> El sistema guarda expediente, receta y cobro automaticamente</div>
            </div>
        </div>

        <p style="text-align:center;margin-top:24px;">
            <a href="https://docfacil.tu-app.co/doctor/consultation" class="btn">Registrar primer paciente &rarr;</a>
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
