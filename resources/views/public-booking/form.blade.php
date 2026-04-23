<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agendar cita · {{ $clinic->name }}</title>
    <meta name="description" content="Agenda tu cita en {{ $clinic->name }} en línea. @if($clinic->city){{ $clinic->city }}@endif — reserva fácil y rápida.">
    <link rel="icon" type="image/png" href="{{ asset('favicon-32x32.png') }}">

    {{-- OpenGraph / Twitter Cards para que se vea bonito cuando lo compartan en WA, Facebook, Instagram --}}
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ route('public.booking.show', $clinic->slug) }}">
    <meta property="og:title" content="Agenda tu cita en {{ $clinic->name }}">
    <meta property="og:description" content="Reserva tu cita en línea de forma fácil. @if($clinic->city){{ $clinic->city }}@endif @if($clinic->phone)· Tel: {{ $clinic->phone }}@endif">
    <meta property="og:image" content="@if($clinic->logo){{ asset('storage/' . $clinic->logo) }}@else{{ asset('images/og-default.png') }}@endif">
    <meta property="og:site_name" content="{{ $clinic->name }}">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="Agenda tu cita en {{ $clinic->name }}">
    <meta name="twitter:description" content="Reserva tu cita en línea de forma fácil.">
    <meta name="twitter:image" content="@if($clinic->logo){{ asset('storage/' . $clinic->logo) }}@else{{ asset('images/og-default.png') }}@endif">

    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, "Segoe UI", Arial, sans-serif;
            background: linear-gradient(135deg, #f0fdfa 0%, #ffffff 100%);
            min-height: 100vh;
            padding: 20px;
            color: #1f2937;
        }
        .page { max-width: 560px; margin: 0 auto; }
        .card {
            background: #ffffff;
            border-radius: 20px;
            padding: 36px 32px;
            box-shadow: 0 20px 40px -8px rgba(13, 148, 136, 0.12);
            border: 1px solid rgba(13, 148, 136, 0.1);
        }
        .clinic-header { text-align: center; margin-bottom: 24px; padding-bottom: 20px; border-bottom: 1px solid #f0fdfa; }
        .logo-wrap {
            width: 72px; height: 72px; border-radius: 50%;
            background: linear-gradient(135deg, #14b8a6, #0d9488);
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 14px;
            color: white; font-size: 28px; font-weight: 800;
            box-shadow: 0 8px 20px -6px rgba(13, 148, 136, 0.35);
        }
        .logo-wrap img { width: 100%; height: 100%; object-fit: cover; border-radius: 50%; }
        h1 { font-size: 24px; font-weight: 800; color: #0f766e; margin-bottom: 4px; letter-spacing: -0.02em; }
        .clinic-meta { color: #6b7280; font-size: 13px; line-height: 1.5; }
        .cta-box {
            background: linear-gradient(135deg, #f0fdfa, #ccfbf1);
            border: 1px solid #99f6e4;
            border-radius: 12px;
            padding: 14px 16px;
            margin-bottom: 20px;
            text-align: center;
            font-size: 14px;
            color: #115e59;
        }
        .cta-box strong { color: #0f766e; }

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
        button[type=submit] {
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
        button[type=submit]:hover { transform: translateY(-1px); box-shadow: 0 10px 20px -5px rgba(13, 148, 136, 0.4); }
        .error { background: #fef2f2; color: #991b1b; padding: 12px; border-radius: 10px; margin-bottom: 16px; border: 1px solid #fecaca; font-size: 14px; }
        .honeypot { position: absolute; left: -9999px; top: -9999px; height: 0; width: 0; overflow: hidden; }

        .df-footer {
            text-align: center;
            margin-top: 18px;
            padding: 14px;
        }
        .df-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 7px 14px;
            background: rgba(255,255,255,0.85);
            backdrop-filter: blur(8px);
            border: 1px solid rgba(13, 148, 136, 0.15);
            border-radius: 999px;
            text-decoration: none;
            font-size: 12px;
            color: #6b7280;
            font-weight: 500;
            transition: all 0.2s;
        }
        .df-badge:hover { background: white; color: #0d9488; border-color: #14b8a6; transform: translateY(-1px); }
        .df-badge strong { color: #0d9488; font-weight: 700; }
        .df-logo-dot { width: 16px; height: 16px; border-radius: 4px; background: linear-gradient(135deg, #14b8a6, #06b6d4); }
    </style>
</head>
<body>
<div class="page">
    <div class="card">
        <div class="clinic-header">
            <div class="logo-wrap">
                @if($clinic->logo)
                    <img src="{{ asset('storage/' . $clinic->logo) }}" alt="{{ $clinic->name }}">
                @else
                    {{ mb_strtoupper(mb_substr($clinic->name, 0, 1)) }}
                @endif
            </div>
            <h1>{{ $clinic->name }}</h1>
            <p class="clinic-meta">
                @if($clinic->address){{ $clinic->address }}@if($clinic->city) · {{ $clinic->city }}@endif @elseif($clinic->city){{ $clinic->city }}@endif
                @if($clinic->phone)<br>📞 {{ $clinic->phone }}@endif
            </p>
        </div>

        <div class="cta-box">
            <strong>Agenda tu cita en 2 minutos</strong><br>
            <span style="font-size:13px;">Te confirmaremos el horario exacto por WhatsApp.</span>
        </div>

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
    </div>

    <div class="df-footer">
        <a href="{{ url('/') }}?utm_source=portal&utm_medium=booking&utm_campaign=powered_by" target="_blank" rel="noopener" class="df-badge">
            <span class="df-logo-dot"></span>
            Reserva vía <strong>DocFácil</strong>
        </a>
    </div>
</div>
</body>
</html>
