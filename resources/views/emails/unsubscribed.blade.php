<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Baja confirmada · DocFácil</title>
    <style>
        body { font-family: -apple-system, "Segoe UI", Arial, sans-serif; background: linear-gradient(135deg, #f0fdfa 0%, #e0f2fe 100%); margin: 0; padding: 40px 20px; color: #1f2937; min-height: 100vh; }
        .card { max-width: 480px; margin: 60px auto; background: #fff; border-radius: 16px; padding: 40px 32px; box-shadow: 0 8px 32px rgba(13, 148, 136, 0.12); text-align: center; }
        .icon { width: 64px; height: 64px; background: #d1fae5; border-radius: 50%; margin: 0 auto 20px; display: flex; align-items: center; justify-content: center; }
        h1 { font-size: 1.4rem; font-weight: 800; margin: 0 0 12px; color: #065f46; }
        p { line-height: 1.6; color: #4b5563; font-size: 0.95rem; margin: 0 0 14px; }
        .email { font-weight: 600; color: #0d9488; word-break: break-all; }
        a.home { display: inline-block; margin-top: 24px; color: #0d9488; text-decoration: none; font-weight: 600; font-size: 0.85rem; }
    </style>
</head>
<body>
    <div class="card">
        @if($success)
            <div class="icon">
                <svg width="32" height="32" fill="none" stroke="#059669" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
            </div>
            <h1>Listo, lo dimos de baja.</h1>
            @if(!empty($email))
                <p>Ya no enviaremos más correos a <span class="email">{{ $email }}</span>.</p>
            @else
                <p>Ya no le enviaremos más correos.</p>
            @endif
            <p style="font-size:0.85rem;color:#6b7280;">Si fue un error o quiere volver más adelante, escríbanos a <a href="mailto:omar@docfacil.tu-app.co" style="color:#0d9488;">omar@docfacil.tu-app.co</a> y lo arreglamos.</p>
            <p style="font-size:0.8rem;color:#9ca3af;margin-top:18px;">Gracias por su tiempo.<br>— Omar Lerma, DocFácil</p>
        @else
            <div class="icon" style="background:#fee2e2;">
                <svg width="32" height="32" fill="none" stroke="#dc2626" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <h1>El link no es válido</h1>
            <p>El link que usó expiró o no es correcto. Si quiere darse de baja, respóndanos al correo con "No me interesa" y lo sacamos de la lista a mano.</p>
            <p style="font-size:0.85rem;color:#6b7280;">O escríbanos directo a <a href="mailto:omar@docfacil.tu-app.co" style="color:#0d9488;">omar@docfacil.tu-app.co</a>.</p>
        @endif
        <a href="{{ url('/') }}" class="home">← Ir a docfacil.tu-app.co</a>
    </div>
</body>
</html>
