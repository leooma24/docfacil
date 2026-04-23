<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cita solicitada · {{ $clinic->name }}</title>
    <link rel="icon" type="image/png" href="{{ asset('favicon-32x32.png') }}">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, "Segoe UI", Arial, sans-serif;
            background: linear-gradient(135deg, #f0fdfa 0%, #ffffff 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            color: #1f2937;
        }
        .card {
            background: #ffffff;
            border-radius: 20px;
            padding: 40px 32px;
            box-shadow: 0 20px 40px -8px rgba(13, 148, 136, 0.15);
            max-width: 440px;
            text-align: center;
            border: 1px solid rgba(13, 148, 136, 0.1);
        }
        .icon-wrap {
            width: 80px; height: 80px;
            margin: 0 auto 24px;
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            background: #d1fae5;
        }
        h1 { font-size: 24px; font-weight: 700; color: #1f2937; margin-bottom: 12px; }
        .subtitle { color: #6b7280; font-size: 15px; line-height: 1.55; margin-bottom: 16px; }
        .details { background: #f9fafb; border-radius: 12px; padding: 18px 20px; margin: 20px 0; text-align: left; font-size: 14px; line-height: 1.7; color: #374151; }
        .footer { margin-top: 28px; padding-top: 20px; border-top: 1px solid #e5e7eb; color: #9ca3af; font-size: 13px; }
        .brand { color: #14b8a6; font-weight: 700; }
    </style>
</head>
<body>
    <div class="card">
        <div class="icon-wrap">
            <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" fill="none" stroke="#059669" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
        </div>
        <h1>¡Solicitud enviada!</h1>
        <p class="subtitle">Recibimos tu solicitud. El equipo de <strong>{{ $clinic->name }}</strong> te va a confirmar el horario por WhatsApp lo antes posible.</p>

        @if(isset($appointment))
        <div class="details">
            <strong>Horario solicitado:</strong><br>
            {{ $appointment->starts_at->translatedFormat('l d \d\e F, Y') }}<br>
            {{ $appointment->starts_at->format('H:i') }} hrs
        </div>
        @endif

        <p class="subtitle" style="margin-top:8px;">Si no recibes confirmación en unas horas, puedes llamar al consultorio directo.</p>

        <div class="footer">
            Reserva vía <span class="brand">DocFácil</span>
        </div>
    </div>
</body>
</html>
