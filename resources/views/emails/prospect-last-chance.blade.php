<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: -apple-system, "Segoe UI", Arial, sans-serif; background: #f4f4f5; margin: 0; padding: 20px; color: #333; }
        .container { max-width: 600px; margin: 0 auto; background: #fff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 4px rgba(0,0,0,0.04); }
        .content { padding: 36px 32px; line-height: 1.65; font-size: 15px; }
        .content p { margin: 0 0 16px; }
        .list { margin: 18px 0; }
        .list-item { padding: 10px 0; border-top: 1px solid #f0f0f0; }
        .list-item:last-child { border-bottom: 1px solid #f0f0f0; }
        .signature { margin-top: 28px; color: #2d2d2d; }
        .footer { padding: 18px 30px; background: #f9fafb; color: #888; font-size: 12px; text-align: center; }
        a { color: #14b8a6; }
    </style>
</head>
<body>
    <div class="container">
        <div class="content">
            <p>Hola Dr. <strong>{{ $prospectName }}</strong>,</p>

            <p>Este es el último correo que recibirá de mi parte sobre DocFácil. No me gusta insistir, y respeto que tal vez no es el momento, o simplemente no es para su consultorio. Cualquiera de las dos razones está perfectamente bien.</p>

            <p>Le dejo tres cosas por si algún día cambian las cosas:</p>

            <div class="list">
                <div class="list-item">
                    <strong>1. El link para empezar sigue activo.</strong><br>
                    Sin letra chiquita: <a href="{{ $ctaUrl ?? url('/register') }}">docfacil.tu-app.co/register</a> — son 15 días con todo, sin tarjeta, y después queda en plan gratis permanente.
                </div>
                <div class="list-item">
                    <strong>2. Mi WhatsApp personal: 668 249 3398.</strong><br>
                    Si algún día quiere preguntarme cualquier cosa del sistema — incluso una opinión sobre otro software que esté evaluando — aquí estoy. Sin pitch.
                </div>
                <div class="list-item">
                    <strong>3. Un favor (si le nace).</strong><br>
                    Si conoce a un colega que batalle con las citas perdidas o con el expediente en papel, páseme su contacto. Le regalo un mes de cualquier plan en cuanto ese colega se suscriba.
                </div>
            </div>

            <p>Le deseo que le siga yendo muy bien en su consultorio. De verdad.</p>

            <p class="signature">
                <strong>Omar Lerma</strong><br>
                DocFácil · Culiacán, Sinaloa
            </p>
        </div>
        <div class="footer">
            DocFácil · <a href="{{ url('/') }}">docfacil.tu-app.co</a><br>
            <small>Este es el último correo automático que recibirás de nosotros.</small>
            @if(!empty($unsubscribeUrl))
                <br><small>O <a href="{{ $unsubscribeUrl }}" style="color:#6b7280;text-decoration:underline;">déme de baja ya mismo</a> si prefiere.</small>
            @endif
        </div>
    </div>
</body>
</html>
