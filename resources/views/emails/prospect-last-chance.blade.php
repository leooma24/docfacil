<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: -apple-system, "Segoe UI", Arial, sans-serif; background: #f4f4f5; margin: 0; padding: 20px; color: #2d2d2d; }
        .container { max-width: 560px; margin: 0 auto; background: #fff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 4px rgba(0,0,0,0.04); }
        .content { padding: 32px 28px; line-height: 1.6; font-size: 15px; }
        .content p { margin: 0 0 14px; }
        .content ol { padding-left: 22px; margin: 14px 0; }
        .content ol li { margin-bottom: 10px; }
        .signature { margin-top: 24px; color: #2d2d2d; font-size: 14px; }
        .signature a { color: #14b8a6; text-decoration: none; }
        .footer { padding: 16px 28px; background: #f9fafb; color: #888; font-size: 12px; text-align: center; line-height: 1.5; }
        .footer a { color: #14b8a6; }
    </style>
</head>
<body>
    <div class="container">
        <div class="content">
            @if($followCall)
                <p>{{ $followCall }}, último mensaje y le dejo en paz.</p>
            @else
                <p>Último mensaje y le dejo en paz.</p>
            @endif

            <p>No le sigo escribiendo, no quiero robarle más tiempo.</p>

            <p>Dos cosas antes de cerrar:</p>

            <ol>
                <li>Si algún día el cuaderno o el Excel deja de servirle, aquí estoy: <a href="https://wa.me/526682493398" style="color:#14b8a6;">668 249 3398</a> (mi WhatsApp directo).</li>
                <li>Si no es para usted pero conoce a un colega al que le pueda servir, le regalo <strong>1 mes gratis</strong> cuando se suscriba con su recomendación.</li>
            </ol>

            <p>Gracias por leerme.</p>

            <p class="signature">
                — <strong>Omar Lerma</strong><br>
                Fundador de DocFácil
            </p>
        </div>
        <div class="footer">
            DocFácil · <a href="{{ url('/') }}">docfacil.tu-app.co</a><br>
            <small>Este es el último correo automático que recibirá de nosotros.</small>
            @if(!empty($unsubscribeUrl))
                <br><small>O <a href="{{ $unsubscribeUrl }}" style="color:#6b7280;text-decoration:underline;">déme de baja ya mismo</a> si prefiere.</small>
            @endif
        </div>
    </div>
</body>
</html>
