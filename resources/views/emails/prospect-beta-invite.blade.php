<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: -apple-system, "Segoe UI", Arial, sans-serif; background: #f4f4f5; margin: 0; padding: 20px; color: #2d2d2d; }
        .container { max-width: 560px; margin: 0 auto; background: #fff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 4px rgba(0,0,0,0.04); }
        .content { padding: 32px 28px; line-height: 1.6; font-size: 15px; }
        .content p { margin: 0 0 14px; }
        .btn { display: inline-block; background: #14b8a6; color: #ffffff !important; padding: 12px 24px; border-radius: 8px; text-decoration: none; font-weight: 600; font-size: 14px; margin: 6px 0 14px; }
        .signature { margin-top: 22px; color: #2d2d2d; font-size: 14px; }
        .signature a { color: #14b8a6; text-decoration: none; }
        .footer { padding: 16px 28px; background: #f9fafb; color: #888; font-size: 12px; text-align: center; line-height: 1.5; }
        .footer a { color: #14b8a6; }
    </style>
</head>
<body>
    <div class="container">
        <div class="content">
            <p>Hola Dr. <strong>{{ $firstName }}</strong>,</p>

            <p>Vi su consultorio{{ $cityPart }}. Le escribo breve.</p>

            <p>Si esta semana se le quedó algún paciente sin llegar, ya sabe el costo: $500-$1,500 por hueco, sin contar el tratamiento que no siguió.</p>

            <p>Hice <strong>DocFácil</strong> para que eso pase menos. A 1 clic desde su agenda manda el recordatorio por WhatsApp; el paciente confirma con un link. Hecho en México, sin tarjeta para probar 15 días.</p>

            <p><a href="{{ $ctaUrl ?? url('/doctor/register') }}" class="btn">¿Le paso la liga? →</a></p>

            <p class="signature">
                — <strong>Omar Lerma</strong>, fundador de DocFácil<br>
                WhatsApp directo: <a href="https://wa.me/526682493398">668 249 3398</a>
            </p>
        </div>
        <div class="footer">
            DocFácil · <a href="{{ url('/') }}">docfacil.tu-app.co</a><br>
            @if(!empty($unsubscribeUrl))
                <small>¿Ya no desea recibir correos? <a href="{{ $unsubscribeUrl }}" style="color:#6b7280;text-decoration:underline;">Dése de baja aquí</a> · 1 clic.</small>
            @endif
        </div>
    </div>
</body>
</html>
