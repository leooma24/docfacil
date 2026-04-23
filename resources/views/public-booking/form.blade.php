<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agendar cita · {{ $clinic->name }}</title>
    <link rel="icon" type="image/png" href="{{ asset('favicon-32x32.png') }}">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, "Segoe UI", Arial, sans-serif;
            background: linear-gradient(135deg, #f0fdfa 0%, #ffffff 100%);
            min-height: 100vh;
            padding: 20px;
            color: #1f2937;
        }
        .card {
            background: #ffffff;
            border-radius: 20px;
            padding: 36px 32px;
            box-shadow: 0 20px 40px -8px rgba(13, 148, 136, 0.12);
            max-width: 520px;
            margin: 0 auto;
            border: 1px solid rgba(13, 148, 136, 0.1);
        }
        h1 { font-size: 22px; font-weight: 700; color: #0d9488; margin-bottom: 6px; }
        .subtitle { color: #6b7280; font-size: 14px; margin-bottom: 24px; }
        label { display: block; font-size: 14px; font-weight: 600; color: #374151; margin-bottom: 6px; margin-top: 14px; }
        input[type=text], input[type=email], input[type=tel], input[type=datetime-local], select, textarea {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #d1d5db;
            border-radius: 10px;
            font-size: 15px;
            transition: border-color 0.15s;
            font-family: inherit;
        }
        input:focus, select:focus, textarea:focus {
            outline: none;
            border-color: #14b8a6;
            box-shadow: 0 0 0 3px rgba(20, 184, 166, 0.1);
        }
        .row { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
        .required { color: #dc2626; }
        button {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #14b8a6, #0d9488);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 700;
            margin-top: 24px;
            cursor: pointer;
            transition: transform 0.15s, box-shadow 0.15s;
        }
        button:hover { transform: translateY(-1px); box-shadow: 0 10px 20px -5px rgba(13, 148, 136, 0.4); }
        .error { background: #fef2f2; color: #991b1b; padding: 12px; border-radius: 10px; margin-bottom: 16px; border: 1px solid #fecaca; font-size: 14px; }
        .honeypot { position: absolute; left: -9999px; top: -9999px; height: 0; width: 0; overflow: hidden; }
        .footer { text-align: center; margin-top: 24px; color: #9ca3af; font-size: 13px; }
        .brand { color: #14b8a6; font-weight: 700; }
    </style>
</head>
<body>
    <div class="card">
        <h1>{{ $clinic->name }}</h1>
        <p class="subtitle">Llena este formulario para solicitar tu cita. El consultorio te confirmará el horario exacto por WhatsApp.</p>

        @if ($errors->any())
            <div class="error">
                @foreach ($errors->all() as $err) <div>{{ $err }}</div> @endforeach
            </div>
        @endif

        <form action="{{ route('public.booking.store', $clinic->slug) }}" method="POST">
            @csrf
            <div class="honeypot" aria-hidden="true">
                <label>No llenar<input type="text" name="honeypot" tabindex="-1" autocomplete="off"></label>
            </div>

            <div class="row">
                <div>
                    <label>Nombre <span class="required">*</span></label>
                    <input type="text" name="first_name" value="{{ old('first_name') }}" required maxlength="100">
                </div>
                <div>
                    <label>Apellido <span class="required">*</span></label>
                    <input type="text" name="last_name" value="{{ old('last_name') }}" required maxlength="100">
                </div>
            </div>

            <label>Teléfono / WhatsApp <span class="required">*</span></label>
            <input type="tel" name="phone" value="{{ old('phone') }}" required maxlength="20" placeholder="Ej: 668 249 3398">

            <label>Email (opcional)</label>
            <input type="email" name="email" value="{{ old('email') }}" maxlength="100">

            @if($services->isNotEmpty())
            <label>¿Qué necesitas?</label>
            <select name="service_id">
                <option value="">Lo que me recomienden</option>
                @foreach($services as $svc)
                <option value="{{ $svc->id }}" {{ old('service_id') == $svc->id ? 'selected' : '' }}>
                    {{ $svc->name }} — ${{ number_format($svc->price, 0) }}
                </option>
                @endforeach
            </select>
            @endif

            @if($doctors->count() > 1)
            <label>¿Doctor preferido? (opcional)</label>
            <select name="doctor_id">
                <option value="">Cualquiera</option>
                @foreach($doctors as $doc)
                <option value="{{ $doc['id'] }}" {{ old('doctor_id') == $doc['id'] ? 'selected' : '' }}>
                    {{ $doc['name'] }}@if($doc['specialty']) — {{ $doc['specialty'] }}@endif
                </option>
                @endforeach
            </select>
            @endif

            <label>Fecha y hora preferida <span class="required">*</span></label>
            <input type="datetime-local" name="preferred_at" value="{{ old('preferred_at') }}" required
                   min="{{ now()->addHour()->format('Y-m-d\TH:i') }}"
                   max="{{ now()->addMonths(3)->format('Y-m-d\TH:i') }}">

            <label>Notas (opcional)</label>
            <textarea name="notes" rows="3" maxlength="500" placeholder="Algo que el doctor debe saber antes de la cita...">{{ old('notes') }}</textarea>

            <button type="submit">Solicitar cita</button>
        </form>

        <p class="footer">
            Reserva vía <span class="brand">DocFácil</span>
        </p>
    </div>
</body>
</html>
