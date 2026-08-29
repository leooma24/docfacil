<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: -apple-system, "Segoe UI", Arial, sans-serif; background: #f4f4f5; margin: 0; padding: 20px; color: #2d2d2d; }
        .container { max-width: 560px; margin: 0 auto; background: #fff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 4px rgba(0,0,0,0.04); }
        .content { padding: 32px 28px; line-height: 1.6; font-size: 15px; }
        .content p { margin: 0 0 14px; }
        .btn { display: inline-block; background: #14b8a6; color: #ffffff !important; padding: 12px 28px; border-radius: 8px; text-decoration: none; font-weight: 700; font-size: 15px; margin: 6px 0 14px; }
        .info { background: #f0fdfa; border-left: 3px solid #14b8a6; padding: 12px 16px; margin: 16px 0; font-size: 14px; }
        .footer { padding: 16px 28px; background: #f9fafb; color: #888; font-size: 12px; text-align: center; }
        .footer a { color: #14b8a6; }
    </style>
</head>
<body>
    <div class="container">
        <div class="content">
            <p>Hola <strong>{{ $nombre }}</strong>,</p>

            <p>
                En <strong>{{ $clinica }}</strong> ya puedes consultar tus citas,
                tus recetas y tus pagos en línea, cuando te acomode.
            </p>

            <p>Entra aquí y elige tu contraseña:</p>

            <p><a href="{{ $link }}" class="btn">Activar mi acceso</a></p>

            <div class="info">
                La liga vence en {{ $dias }} días. Si se te pasa, pídele otra a tu
                consultorio y con gusto te la mandan.
            </div>

            <p>Si no esperabas este correo, puedes ignorarlo.</p>
        </div>
        <div class="footer">
            {{ $clinica }} · Tu información médica solo la ve tu consultorio.
        </div>
    </div>
</body>
</html>
