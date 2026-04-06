<!DOCTYPE html>
<html lang="es">
<head><meta charset="utf-8">
<style>
    body { font-family: Arial, sans-serif; background: #f4f4f5; margin: 0; padding: 20px; }
    .container { max-width: 600px; margin: 0 auto; background: #fff; border-radius: 8px; overflow: hidden; }
    .header { background: #8b5cf6; padding: 30px; text-align: center; color: #fff; }
    .content { padding: 30px; color: #333; line-height: 1.6; }
    .btn { display: inline-block; background: #14b8a6; color: #fff; padding: 12px 30px; border-radius: 6px; text-decoration: none; font-weight: bold; }
    .tip { background: #faf5ff; border: 1px solid #d8b4fe; padding: 15px; border-radius: 6px; margin: 15px 0; }
    .footer { padding: 20px 30px; background: #f9fafb; color: #666; font-size: 12px; text-align: center; }
</style>
</head>
<body>
<div class="container">
    <div class="header"><h1>Agenda tu primera cita</h1></div>
    <div class="content">
        <p>Hola <strong>{{ $doctorName }}</strong>,</p>
        <p>Ya tienes pacientes registrados en DocFacil. El siguiente paso es agendar tu primera cita.</p>

        <div class="tip">
            <strong>¿Sabias que...?</strong>
            <p>Los consultorios que usan DocFacil reducen inasistencias un 40% gracias a los recordatorios por WhatsApp. Agenda una cita y pruebalo!</p>
        </div>

        <p>Puedes hacerlo de 3 formas:</p>
        <ul>
            <li><strong>Consulta rapida:</strong> Desde el dashboard, registra todo en un solo flujo</li>
            <li><strong>Calendario:</strong> Arrastra y suelta en la vista semanal</li>
            <li><strong>Nueva cita:</strong> Desde el boton en el dashboard</li>
        </ul>

        <p style="text-align:center;"><a href="https://docfacil.tu-app.co/doctor" class="btn">Ir a mi consultorio</a></p>

        <p>— El equipo de DocFacil</p>
    </div>
    <div class="footer">&copy; {{ date('Y') }} DocFacil.<br><a href="https://docfacil.tu-app.co" style="color:#14b8a6;">docfacil.tu-app.co</a></div>
</div>
</body>
</html>
