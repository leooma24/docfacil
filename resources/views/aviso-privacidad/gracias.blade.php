<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex">
    <title>Gracias · {{ $clinic->name }}</title>
    <style>
        body { margin: 0; font-family: -apple-system, "Segoe UI", Arial, sans-serif; background: linear-gradient(135deg, #f0fdfa 0%, #ffffff 100%); color: #1f2937; padding: 40px 16px; }
        .card { max-width: 460px; margin: 40px auto; background: #fff; border-radius: 18px; padding: 32px 24px; text-align: center; box-shadow: 0 16px 36px -10px rgba(13,148,136,.15); }
        .icono { width: 60px; height: 60px; border-radius: 50%; background: #d1fae5; display: flex; align-items: center; justify-content: center; margin: 0 auto 16px; }
        h1 { font-size: 20px; color: #065f46; margin: 0 0 8px; }
        p { font-size: 14px; color: #4b5563; line-height: 1.6; margin: 0; }
    </style>
</head>
<body>
    <div class="card">
        <div class="icono">
            <svg width="30" height="30" fill="none" stroke="#059669" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
        </div>
        <h1>Listo, {{ $paciente->first_name }}</h1>
        <p>Quedó registrado que aceptaste el aviso de privacidad de {{ $clinic->name }}. Ya puedes cerrar esta página.</p>
    </div>
</body>
</html>
