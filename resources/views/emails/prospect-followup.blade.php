<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: -apple-system, "Segoe UI", Arial, sans-serif; background: #f4f4f5; margin: 0; padding: 20px; color: #2d2d2d; }
        .container { max-width: 560px; margin: 0 auto; background: #fff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 4px rgba(0,0,0,0.04); }
        .content { padding: 32px 28px; line-height: 1.6; font-size: 15px; }
        .content p { margin: 0 0 14px; }
        .math { font-family: ui-monospace, "SF Mono", Menlo, monospace; background: #f0fdfa; border-left: 3px solid #14b8a6; padding: 12px 16px; margin: 16px 0; font-size: 14px; }
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
            @if($followCall)
                <p>{{ $followCall }}, le escribo de nuevo.</p>
            @else
                <p>Le escribo de nuevo.</p>
            @endif

            <p>Sé que están saturados, no le robo tiempo. La cuenta rápida del consultorio promedio:</p>

            <div class="math">
                8 pacientes que no llegan al mes × $1,000 = <strong>$8,000</strong><br>
                En un año: <strong>$96,000</strong> que se evaporan.
            </div>

            <p>Por <strong>$499 al mes</strong>, DocFácil le manda recordatorios WhatsApp a sus pacientes con un solo clic. Si recupera 1 cita al mes ya pagó el plan; las demás son ganancia neta.</p>

            <p>15 días gratis con todas las funciones, sin tarjeta. Si no le sirve, un clic y lo cancela.</p>

            <p><a href="{{ $ctaUrl ?? url('/doctor/register') }}" class="btn">Probar 15 días gratis</a></p>

            <p class="signature">
                — <strong>Omar Lerma</strong><br>
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
