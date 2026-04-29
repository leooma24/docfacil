<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lead caliente</title>
</head>
<body style="margin:0;padding:24px;background:#f8fafc;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif;color:#0f172a;">
    <div style="max-width:560px;margin:0 auto;background:white;border-radius:16px;padding:32px;box-shadow:0 4px 20px rgba(0,0,0,0.06);">

        <div style="display:inline-block;padding:6px 14px;background:linear-gradient(135deg,#dc2626,#ea580c);color:white;font-size:11px;font-weight:800;text-transform:uppercase;letter-spacing:0.1em;border-radius:999px;margin-bottom:16px;">
            🔥 Lead caliente
        </div>

        <h1 style="font-size:24px;font-weight:800;margin:0 0 8px;color:#0f172a;">
            {{ $prospect->cleanName() ?: 'Prospecto sin nombre' }}
        </h1>
        <p style="font-size:14px;color:#64748b;margin:0 0 24px;">
            Acaba de cruzar el umbral 80. Score actual: <strong style="color:#dc2626;">{{ $score }}</strong>.
        </p>

        <table style="width:100%;border-collapse:collapse;font-size:14px;margin-bottom:24px;">
            @if($prospect->specialty)
            <tr>
                <td style="padding:8px 0;border-bottom:1px solid #e2e8f0;color:#64748b;width:40%;">Especialidad</td>
                <td style="padding:8px 0;border-bottom:1px solid #e2e8f0;font-weight:600;">{{ $prospect->specialty }}</td>
            </tr>
            @endif
            @if($prospect->clinic_name)
            <tr>
                <td style="padding:8px 0;border-bottom:1px solid #e2e8f0;color:#64748b;">Consultorio</td>
                <td style="padding:8px 0;border-bottom:1px solid #e2e8f0;font-weight:600;">{{ $prospect->clinic_name }}</td>
            </tr>
            @endif
            @if($prospect->city)
            <tr>
                <td style="padding:8px 0;border-bottom:1px solid #e2e8f0;color:#64748b;">Ciudad</td>
                <td style="padding:8px 0;border-bottom:1px solid #e2e8f0;font-weight:600;">{{ $prospect->city }}</td>
            </tr>
            @endif
            @if($prospect->phone)
            <tr>
                <td style="padding:8px 0;border-bottom:1px solid #e2e8f0;color:#64748b;">Teléfono</td>
                <td style="padding:8px 0;border-bottom:1px solid #e2e8f0;font-weight:600;">
                    <a href="https://wa.me/52{{ preg_replace('/\D/', '', $prospect->phone) }}" style="color:#0d9488;text-decoration:none;">{{ $prospect->phone }} 💬</a>
                </td>
            </tr>
            @endif
            @if($prospect->email)
            <tr>
                <td style="padding:8px 0;border-bottom:1px solid #e2e8f0;color:#64748b;">Email</td>
                <td style="padding:8px 0;border-bottom:1px solid #e2e8f0;font-weight:600;">
                    <a href="mailto:{{ $prospect->email }}" style="color:#0d9488;text-decoration:none;">{{ $prospect->email }}</a>
                </td>
            </tr>
            @endif
            <tr>
                <td style="padding:8px 0;border-bottom:1px solid #e2e8f0;color:#64748b;">Status</td>
                <td style="padding:8px 0;border-bottom:1px solid #e2e8f0;font-weight:600;">{{ $prospect->status }}</td>
            </tr>
            @if($prospect->emailEvents()->where('event_type', 'click')->count() > 0)
            <tr>
                <td style="padding:8px 0;border-bottom:1px solid #e2e8f0;color:#64748b;">Clicks correo</td>
                <td style="padding:8px 0;border-bottom:1px solid #e2e8f0;font-weight:600;color:#0d9488;">
                    {{ $prospect->emailEvents()->where('event_type', 'click')->count() }} 🔥
                </td>
            </tr>
            @endif
        </table>

        <div style="background:linear-gradient(135deg,#fef3c7,#fde68a);border:1px solid #f59e0b;border-radius:12px;padding:16px;margin-bottom:24px;">
            <div style="font-size:12px;font-weight:700;color:#92400e;text-transform:uppercase;letter-spacing:0.06em;margin-bottom:6px;">⚡ Acción sugerida</div>
            <div style="font-size:14px;color:#78350f;line-height:1.5;">
                Contacta dentro de las próximas <strong>2 horas</strong>. Speed-to-lead es el factor #1 de conversión —
                un caliente atendido hoy convierte 21× más que mañana.
            </div>
        </div>

        <a href="{{ $panelUrl }}" style="display:block;text-align:center;padding:14px;background:linear-gradient(135deg,#0d9488,#0891b2);color:white;text-decoration:none;font-weight:700;border-radius:12px;font-size:15px;">
            Abrir prospect en panel ventas →
        </a>

        <p style="font-size:12px;color:#94a3b8;margin:24px 0 0;text-align:center;line-height:1.5;">
            Esta alerta se envía automáticamente cuando un prospect cruza score 80.
            Se manda una sola vez por evento de calentamiento — si después se enfría y vuelve
            a calentar, te llega otra alerta.
        </p>
    </div>
</body>
</html>
