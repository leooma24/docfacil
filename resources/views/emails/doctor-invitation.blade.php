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
            <p>Hola <strong>{{ $name }}</strong>,</p>

            <p><strong>{{ $inviterName }}</strong> te invitó a unirte a <strong>{{ $clinicName }}</strong> en DocFácil — el sistema que usan para agenda, expedientes, recetas PDF y recordatorios por WhatsApp.</p>

            <div class="info">
                <strong>Lo que vas a poder hacer:</strong><br>
                ▸ Ver y gestionar tu propia agenda dentro de la clínica<br>
                ▸ Llevar expedientes y recetas con tu cédula<br>
                @if($specialty)
                ▸ Consulta como <strong>{{ $specialty }}</strong><br>
                @endif
                ▸ Mandar recordatorios WhatsApp a 1 clic
            </div>

            <p>Acepta la invitación y crea tu contraseña aquí:</p>

            <p style="text-align:center;">
                <a href="{{ $acceptUrl }}" class="btn">Aceptar invitación →</a>
            </p>

            <p style="font-size:13px;color:#888;">
                La invitación expira el <strong>{{ $expiresAt }}</strong>. Si no la usas para esa fecha, pídele a {{ $inviterName }} que te mande una nueva.
            </p>

            <p style="font-size:13px;color:#888;">
                Si el botón no funciona, copia y pega esta liga en tu navegador:<br>
                <span style="color:#14b8a6;word-break:break-all;">{{ $acceptUrl }}</span>
            </p>
        </div>
        <div class="footer">
            DocFácil · <a href="{{ url('/') }}">docfacil.tu-app.co</a>
        </div>
    </div>
</body>
</html>
