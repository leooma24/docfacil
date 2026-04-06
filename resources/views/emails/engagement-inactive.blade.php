<!DOCTYPE html>
<html lang="es">
<head><meta charset="utf-8">
<style>
    body { font-family: Arial, sans-serif; background: #f4f4f5; margin: 0; padding: 20px; }
    .container { max-width: 600px; margin: 0 auto; background: #fff; border-radius: 8px; overflow: hidden; }
    .header { background: #14b8a6; padding: 30px; text-align: center; color: #fff; }
    .content { padding: 30px; color: #333; line-height: 1.6; }
    .btn { display: inline-block; background: #14b8a6; color: #fff; padding: 12px 30px; border-radius: 6px; text-decoration: none; font-weight: bold; }
    .tip { background: #f0fdfa; border: 1px solid #99f6e4; padding: 15px; border-radius: 6px; margin: 15px 0; }
    .footer { padding: 20px 30px; background: #f9fafb; color: #666; font-size: 12px; text-align: center; }
</style>
</head>
<body>
<div class="container">
    <div class="header"><h1>Te extrañamos!</h1></div>
    <div class="content">
        <p>Hola <strong>{{ $doctorName }}</strong>,</p>
        <p>Notamos que no has usado DocFacil en los ultimos dias. Tu consultorio <strong>{{ $clinic->name }}</strong> esta listo y esperandote.</p>

        <div class="tip">
            <strong>¿Necesitas ayuda para empezar?</strong>
            <p>Respondenos este correo o escribenos por WhatsApp al <strong>668 249 3398</strong> y te ayudamos a configurar todo en 15 minutos.</p>
        </div>

        <p>Recuerda que tu beta incluye:</p>
        <ul>
            <li>Citas ilimitadas y recordatorios WhatsApp</li>
            <li>Expediente clinico y recetas PDF</li>
            <li>Cobros y reportes en tiempo real</li>
        </ul>

        <p style="text-align:center;"><a href="https://docfacil.tu-app.co/doctor" class="btn">Ir a mi consultorio</a></p>

        <p>— El equipo de DocFacil</p>
    </div>
    <div class="footer">&copy; {{ date('Y') }} DocFacil.<br><a href="https://docfacil.tu-app.co" style="color:#14b8a6;">docfacil.tu-app.co</a></div>
</div>
</body>
</html>
