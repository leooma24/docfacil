<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Plan rechazado · {{ $plan->clinic->name }}</title>
    <link rel="icon" type="image/png" href="{{ asset('favicon-32x32.png') }}">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: -apple-system, "Segoe UI", Arial, sans-serif; background: linear-gradient(135deg, #fef3c7 0%, #ffffff 100%); min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 20px; }
        .card { background: #ffffff; border-radius: 20px; padding: 40px 32px; box-shadow: 0 20px 40px -8px rgba(0,0,0,0.08); max-width: 440px; text-align: center; border: 1px solid #f3f4f6; }
        .icon-wrap { width: 80px; height: 80px; margin: 0 auto 24px; border-radius: 50%; display: flex; align-items: center; justify-content: center; background: #fef3c7; }
        h1 { font-size: 22px; font-weight: 700; color: #1f2937; margin-bottom: 12px; }
        .sub { color: #4b5563; font-size: 14px; line-height: 1.6; margin-bottom: 16px; }
        .footer { margin-top: 28px; padding-top: 20px; border-top: 1px solid #e5e7eb; color: #9ca3af; font-size: 13px; }
        .brand { color: #14b8a6; font-weight: 700; }
    </style>
</head>
<body>
    <div class="card">
        <div class="icon-wrap">
            <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" fill="none" stroke="#d97706" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
        </div>
        <h1>Entendido</h1>
        <p class="sub">Marcamos tu plan como no aceptado. Si cambias de opinión o tienes dudas, comunícate directamente con <strong>{{ $plan->clinic->name }}</strong> — ellos pueden ajustarlo o armarte una alternativa.</p>

        <div class="footer">
            Vía <span class="brand">DocFácil</span>
        </div>
    </div>
</body>
</html>
