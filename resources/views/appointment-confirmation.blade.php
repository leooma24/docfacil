<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>
        @if($action === 'cancel') Cita cancelada
        @else Cita confirmada
        @endif
        · DocFácil
    </title>
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
        }
        .card {
            background: #ffffff;
            border-radius: 20px;
            padding: 40px 32px;
            box-shadow: 0 20px 40px -8px rgba(13, 148, 136, 0.15);
            max-width: 440px;
            width: 100%;
            text-align: center;
            border: 1px solid rgba(13, 148, 136, 0.1);
        }
        .icon-wrap {
            width: 80px;
            height: 80px;
            margin: 0 auto 24px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .icon-wrap.success { background: #d1fae5; }
        .icon-wrap.cancel { background: #fef3c7; }
        .icon-wrap.info { background: #dbeafe; }
        h1 {
            font-size: 24px;
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 12px;
        }
        .subtitle { color: #6b7280; font-size: 15px; line-height: 1.55; margin-bottom: 24px; }
        .details {
            background: #f9fafb;
            border-radius: 12px;
            padding: 18px 20px;
            margin: 20px 0;
            text-align: left;
        }
        .detail-row {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            padding: 6px 0;
            font-size: 14px;
        }
        .detail-label { color: #6b7280; font-weight: 500; }
        .detail-value { color: #1f2937; font-weight: 600; text-align: right; }
        .footer {
            margin-top: 28px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
            color: #9ca3af;
            font-size: 13px;
        }
        .brand { color: #14b8a6; font-weight: 700; }
    </style>
</head>
<body>
    <div class="card">
        @if($alreadyHandled)
            <div class="icon-wrap info">
                <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" fill="none" stroke="#2563eb" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <h1>Esta cita ya fue procesada</h1>
            <p class="subtitle">El estado actual es: <strong>{{ $appointment->status_label ?? $appointment->status }}</strong>. Si necesitas hacer un cambio, comunícate directo con el consultorio.</p>
        @elseif($action === 'cancel')
            <div class="icon-wrap cancel">
                <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" fill="none" stroke="#d97706" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
            </div>
            <h1>Cita cancelada</h1>
            <p class="subtitle">Gracias por avisarnos. Si quieres reagendar, responde por WhatsApp al consultorio y con gusto buscamos un nuevo horario.</p>
        @else
            <div class="icon-wrap success">
                <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" fill="none" stroke="#059669" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
            </div>
            <h1>¡Cita confirmada!</h1>
            <p class="subtitle">Te esperamos puntual. Aquí los detalles:</p>
        @endif

        <div class="details">
            <div class="detail-row">
                <span class="detail-label">Paciente</span>
                <span class="detail-value">{{ $appointment->patient->first_name }} {{ $appointment->patient->last_name }}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Fecha</span>
                <span class="detail-value">{{ $appointment->starts_at->translatedFormat('l d \d\e F, Y') }}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Hora</span>
                <span class="detail-value">{{ $appointment->starts_at->format('H:i') }} hrs</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Doctor</span>
                <span class="detail-value">{{ $appointment->doctor->user->name ?? '—' }}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Consultorio</span>
                <span class="detail-value">{{ $appointment->clinic->name ?? '—' }}</span>
            </div>
        </div>

        <div class="footer">
            Confirmación vía <span class="brand">DocFácil</span>
        </div>
    </div>
</body>
</html>
