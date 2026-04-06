<!DOCTYPE html>
<html lang="es">
<head><meta charset="utf-8">
<style>
    body { font-family: Arial, sans-serif; background: #f4f4f5; margin: 0; padding: 20px; }
    .container { max-width: 600px; margin: 0 auto; background: #fff; border-radius: 8px; overflow: hidden; }
    .header { background: #3b82f6; padding: 30px; text-align: center; color: #fff; }
    .content { padding: 30px; color: #333; line-height: 1.6; }
    .btn { display: inline-block; background: #14b8a6; color: #fff; padding: 12px 30px; border-radius: 6px; text-decoration: none; font-weight: bold; }
    .steps { margin: 15px 0; }
    .step { padding: 10px; border-left: 3px solid #14b8a6; margin-bottom: 8px; background: #f0fdfa; border-radius: 0 6px 6px 0; }
    .footer { padding: 20px 30px; background: #f9fafb; color: #666; font-size: 12px; text-align: center; }
</style>
</head>
<body>
<div class="container">
    <div class="header"><h1>Registra tu primer paciente</h1></div>
    <div class="content">
        <p>Hola <strong>{{ $doctorName }}</strong>,</p>
        <p>Ya tienes tu consultorio <strong>{{ $clinic->name }}</strong> listo en DocFacil. El siguiente paso es registrar tu primer paciente — toma menos de 1 minuto.</p>

        <div class="steps">
            <div class="step"><strong>1.</strong> Entra a tu consultorio</div>
            <div class="step"><strong>2.</strong> Click en "Consulta" en el menu</div>
            <div class="step"><strong>3.</strong> Click en "Paciente nuevo" y llena nombre y telefono</div>
            <div class="step"><strong>4.</strong> Listo! Ya puedes agendar citas y crear expedientes</div>
        </div>

        <p>O si prefieres, ve a "Consulta rapida" y registra al paciente ahi mismo mientras lo atiendes.</p>

        <p style="text-align:center;"><a href="https://docfacil.tu-app.co/doctor/consultation" class="btn">Registrar primer paciente</a></p>

        <p>— El equipo de DocFacil</p>
    </div>
    <div class="footer">&copy; {{ date('Y') }} DocFacil.<br><a href="https://docfacil.tu-app.co" style="color:#14b8a6;">docfacil.tu-app.co</a></div>
</div>
</body>
</html>
