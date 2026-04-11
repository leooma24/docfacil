<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>¡Listo! — {{ $clinic->name }}</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background: linear-gradient(135deg, #f0fdfa 0%, #e0f2fe 100%); min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 20px; color: #1f2937; }
        .container { max-width: 440px; text-align: center; background: white; border-radius: 24px; padding: 40px 28px; box-shadow: 0 10px 40px rgba(0,0,0,0.08); }
        .check-icon { width: 90px; height: 90px; background: #dcfce7; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; margin-bottom: 20px; animation: pop 0.4s cubic-bezier(0.34, 1.56, 0.64, 1); }
        .check-icon svg { width: 50px; height: 50px; color: #16a34a; }
        @keyframes pop { 0% { transform: scale(0); } 100% { transform: scale(1); } }
        h1 { font-size: 26px; color: #0f766e; margin-bottom: 8px; }
        p { color: #6b7280; font-size: 15px; line-height: 1.5; margin-bottom: 20px; }
        .info-box { background: #f0fdfa; border: 1px solid #99f6e4; border-radius: 14px; padding: 16px; margin: 20px 0; text-align: left; }
        .info-box strong { color: #0f766e; font-size: 13px; display: block; margin-bottom: 6px; }
        .info-box p { color: #374151; font-size: 14px; margin: 0; }
        .clinic { font-weight: 700; color: #111; font-size: 16px; margin-top: 8px; }
        .footer { margin-top: 24px; font-size: 12px; color: #9ca3af; }
    </style>
</head>
<body>
    <div class="container">
        <div class="check-icon">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
        </div>
        @if($returning)
        <h1>¡Hola de nuevo!</h1>
        <p>Ya tenemos tus datos. El doctor te atenderá en un momento.</p>
        @else
        <h1>¡Check-in completado!</h1>
        <p>Toma asiento, el doctor te atenderá en un momento.</p>
        @endif

        <div class="info-box">
            <strong>Estás en:</strong>
            <p>{{ $clinic->name }}</p>
        </div>

        <div class="footer">Gracias por elegirnos</div>
    </div>
</body>
</html>
