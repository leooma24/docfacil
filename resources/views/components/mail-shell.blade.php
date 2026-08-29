{{-- Cascaron compartido de los correos de autenticacion.

     Mismo estilo que las plantillas escritas a mano (doctor-invitation y
     compañia). Existe porque Laravel manda su plantilla por defecto, en
     ingles y sin marca, y un correo asi de un producto en español se ve
     como phishing. --}}
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $titulo ?? 'DocFácil' }}</title>
    <style>
        body { font-family: -apple-system, "Segoe UI", Arial, sans-serif; background: #f4f4f5; margin: 0; padding: 20px; color: #2d2d2d; }
        .container { max-width: 560px; margin: 0 auto; background: #fff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 4px rgba(0,0,0,0.04); }
        .marca { background: linear-gradient(135deg, #0d9488, #0891b2); padding: 22px 28px; text-align: center; }
        .marca span { color: #fff; font-size: 1.25rem; font-weight: 800; letter-spacing: -0.02em; }
        .content { padding: 30px 28px; line-height: 1.6; font-size: 15px; }
        .content p { margin: 0 0 14px; }
        .btn { display: inline-block; background: #14b8a6; color: #ffffff !important; padding: 13px 30px; border-radius: 8px; text-decoration: none; font-weight: 700; font-size: 15px; margin: 8px 0 16px; }
        .info { background: #f0fdfa; border-left: 3px solid #14b8a6; padding: 12px 16px; margin: 16px 0; font-size: 14px; }
        .liga-larga { font-size: 13px; color: #888; }
        .liga-larga span { color: #14b8a6; word-break: break-all; }
        .footer { padding: 16px 28px; background: #f9fafb; color: #888; font-size: 12px; text-align: center; }
        .footer a { color: #14b8a6; }
    </style>
</head>
<body>
    <div class="container">
        <div class="marca"><span>DocFácil</span></div>
        <div class="content">
            {{ $slot }}
        </div>
        <div class="footer">
            DocFácil · <a href="{{ url('/') }}">docfacil.tu-app.co</a>
        </div>
    </div>
</body>
</html>
