<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Check-in — {{ $clinic->name }}</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background: linear-gradient(135deg, #f0fdfa 0%, #e0f2fe 100%); min-height: 100vh; padding: 20px 16px 40px; color: #1f2937; }
        .container { max-width: 500px; margin: 0 auto; }
        .header { text-align: center; padding: 24px 16px; }
        .icon { width: 64px; height: 64px; background: linear-gradient(135deg, #0d9488, #0891b2); border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; margin-bottom: 12px; box-shadow: 0 8px 20px rgba(13,148,136,0.25); }
        .icon svg { width: 32px; height: 32px; color: white; }
        .clinic-name { font-size: 20px; font-weight: 800; color: #0f766e; margin-bottom: 4px; }
        .welcome { font-size: 14px; color: #6b7280; }
        .card { background: white; border-radius: 20px; padding: 24px 20px; box-shadow: 0 10px 40px rgba(0,0,0,0.06); }
        h1 { font-size: 18px; margin-bottom: 4px; color: #111; }
        .subtitle { font-size: 13px; color: #6b7280; margin-bottom: 20px; }
        .form-group { margin-bottom: 14px; }
        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
        label { display: block; font-size: 12px; font-weight: 600; color: #374151; margin-bottom: 5px; }
        .required { color: #dc2626; }
        input, select, textarea { width: 100%; padding: 12px 14px; font-size: 15px; border: 1.5px solid #e5e7eb; border-radius: 12px; background: #f9fafb; transition: all 0.15s; font-family: inherit; }
        input:focus, select:focus, textarea:focus { outline: none; border-color: #0d9488; background: white; box-shadow: 0 0 0 3px rgba(13,148,136,0.1); }
        textarea { resize: vertical; min-height: 80px; }
        .error { color: #dc2626; font-size: 11px; margin-top: 3px; }
        .hint { color: #9ca3af; font-size: 11px; margin-top: 3px; }
        button[type="submit"] { width: 100%; padding: 16px; background: linear-gradient(135deg, #0d9488, #0891b2); color: white; border: none; border-radius: 14px; font-size: 16px; font-weight: 700; cursor: pointer; margin-top: 8px; box-shadow: 0 8px 20px rgba(13,148,136,0.3); transition: transform 0.15s; }
        button[type="submit"]:hover { transform: translateY(-1px); }
        button[type="submit"]:active { transform: translateY(0); }
        .honeypot { position: absolute; left: -9999px; opacity: 0; pointer-events: none; }
        .footer { text-align: center; margin-top: 20px; font-size: 11px; color: #9ca3af; }
        .footer a { color: #0d9488; font-weight: 600; text-decoration: none; }
        .section-divider { font-size: 11px; font-weight: 700; color: #9ca3af; text-transform: uppercase; letter-spacing: 0.8px; margin: 18px 0 10px; padding-bottom: 6px; border-bottom: 1px dashed #e5e7eb; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="icon">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
            </div>
            <div class="clinic-name">{{ $clinic->name }}</div>
            <div class="welcome">¡Bienvenido! Regístrate para tu consulta</div>
        </div>

        <div class="card">
            <h1>Check-in rápido</h1>
            <p class="subtitle">Llena tus datos mientras esperas. Solo toma 30 segundos.</p>

            <form method="POST" action="{{ route('checkin.store', $clinic->slug) }}">
                @csrf

                {{-- Honeypot for bots --}}
                <input type="text" name="honeypot" class="honeypot" tabindex="-1" autocomplete="off">

                <div class="section-divider">Datos personales</div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Nombre <span class="required">*</span></label>
                        <input type="text" name="first_name" value="{{ old('first_name') }}" required autocomplete="given-name">
                        @error('first_name')<div class="error">{{ $message }}</div>@enderror
                    </div>
                    <div class="form-group">
                        <label>Apellidos <span class="required">*</span></label>
                        <input type="text" name="last_name" value="{{ old('last_name') }}" required autocomplete="family-name">
                        @error('last_name')<div class="error">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="form-group">
                    <label>Teléfono</label>
                    <input type="tel" name="phone" value="{{ old('phone') }}" placeholder="55 1234 5678" autocomplete="tel">
                    <div class="hint">Para poder contactarte o enviarte recordatorios</div>
                    @error('phone')<div class="error">{{ $message }}</div>@enderror
                </div>

                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" value="{{ old('email') }}" placeholder="tu@email.com" autocomplete="email">
                    @error('email')<div class="error">{{ $message }}</div>@enderror
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Fecha de nacimiento</label>
                        <input type="date" name="birth_date" value="{{ old('birth_date') }}">
                        @error('birth_date')<div class="error">{{ $message }}</div>@enderror
                    </div>
                    <div class="form-group">
                        <label>Género</label>
                        <select name="gender">
                            <option value="">—</option>
                            <option value="female" {{ old('gender') === 'female' ? 'selected' : '' }}>Femenino</option>
                            <option value="male" {{ old('gender') === 'male' ? 'selected' : '' }}>Masculino</option>
                            <option value="other" {{ old('gender') === 'other' ? 'selected' : '' }}>Otro</option>
                        </select>
                    </div>
                </div>

                <div class="section-divider">Información médica (opcional)</div>

                <div class="form-group">
                    <label>Tipo de sangre</label>
                    <select name="blood_type">
                        <option value="">—</option>
                        @foreach(['A+','A-','B+','B-','AB+','AB-','O+','O-'] as $bt)
                        <option value="{{ $bt }}" {{ old('blood_type') === $bt ? 'selected' : '' }}>{{ $bt }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label>Alergias</label>
                    <input type="text" name="allergies" value="{{ old('allergies') }}" placeholder="Penicilina, látex, etc.">
                </div>

                <div class="form-group">
                    <label>¿Por qué vienes hoy? <span class="required">*</span></label>
                    <textarea name="reason_for_visit" placeholder="Describe tu molestia o motivo de visita" required>{{ old('reason_for_visit') }}</textarea>
                </div>

                <button type="submit">Completar check-in →</button>
            </form>
        </div>

        <div class="footer">
            Tus datos son privados y solo los ve tu doctor.<br>
            <a href="https://docfacil.tu-app.co">Powered by DocFácil</a>
        </div>
    </div>
</body>
</html>
