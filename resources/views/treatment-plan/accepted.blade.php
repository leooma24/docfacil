<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Plan aceptado · {{ $plan->clinic->name }}</title>
    <link rel="icon" type="image/png" href="{{ asset('favicon-32x32.png') }}">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: -apple-system, "Segoe UI", Arial, sans-serif; background: linear-gradient(135deg, #f0fdfa 0%, #ffffff 100%); min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 20px; }
        .card { background: #ffffff; border-radius: 20px; padding: 40px 32px; box-shadow: 0 20px 40px -8px rgba(13, 148, 136, 0.15); max-width: 440px; text-align: center; border: 1px solid rgba(13, 148, 136, 0.1); }
        .icon-wrap { width: 88px; height: 88px; margin: 0 auto 24px; border-radius: 50%; display: flex; align-items: center; justify-content: center; background: #d1fae5; }
        h1 { font-size: 26px; font-weight: 700; color: #065f46; margin-bottom: 12px; }
        .sub { color: #4b5563; font-size: 15px; line-height: 1.6; margin-bottom: 16px; }
        .plan-box { background: #f9fafb; border-radius: 12px; padding: 18px; margin: 20px 0; text-align: left; font-size: 14px; }
        .total { font-size: 22px; font-weight: 700; color: #0d9488; margin-top: 6px; }
        .footer { margin-top: 28px; padding-top: 20px; border-top: 1px solid #e5e7eb; color: #9ca3af; font-size: 13px; }
        .brand { color: #14b8a6; font-weight: 700; }
    </style>
</head>
<body>
    <div class="card">
        <div class="icon-wrap">
            <svg xmlns="http://www.w3.org/2000/svg" width="44" height="44" fill="none" stroke="#059669" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
        </div>
        <h1>¡Plan aceptado!</h1>
        <p class="sub">Gracias por aceptar tu plan con <strong>{{ $plan->clinic->name }}</strong>. El equipo te contactará pronto por WhatsApp para agendar tu primera cita.</p>

        <div class="plan-box">
            <div style="color:#6b7280;font-size:12px;margin-bottom:4px;">PLAN ACEPTADO</div>
            <div style="font-weight:700;color:#1f2937;">{{ $plan->title }}</div>
            <div class="total">${{ number_format($plan->total, 2) }} MXN</div>
        </div>

        <p class="sub" style="font-size:13px;">Aceptado el {{ $plan->accepted_at->translatedFormat('l d \d\e F, H:i') }}</p>

        <div class="footer">
            Plan de tratamiento vía <span class="brand">DocFácil</span>
        </div>
    </div>
</body>
</html>
