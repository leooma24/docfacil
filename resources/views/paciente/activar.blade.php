<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Activa tu acceso · {{ $clinica->name }}</title>
    <link rel="icon" href="{{ asset('favicon-32x32.png') }}">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800" rel="stylesheet">
    <style>
        /* Estilos propios y no Tailwind: esta pagina la abre el paciente
           desde su celular y no queremos depender de que compile en prod. */
        * { box-sizing: border-box; }
        body {
            margin: 0; min-height: 100vh; padding: 24px 16px;
            font-family: 'Inter', -apple-system, 'Segoe UI', Arial, sans-serif;
            background: linear-gradient(160deg, #f0fdfa 0%, #ecfeff 45%, #f8fafc 100%);
            display: flex; align-items: center; justify-content: center;
            color: #0f172a;
        }
        .caja {
            width: 100%; max-width: 420px; background: #fff;
            border: 1px solid #e2e8f0; border-radius: 20px; padding: 32px 26px;
            box-shadow: 0 12px 40px rgba(13, 148, 136, 0.10);
        }
        .marca { text-align: center; margin-bottom: 22px; }
        .marca img { height: 46px; }
        .consultorio {
            margin: 12px 0 0; font-size: 1.15rem; font-weight: 800;
            letter-spacing: -0.02em; color: #0f766e;
        }
        h1 { font-size: 1.35rem; font-weight: 800; letter-spacing: -0.02em; margin: 0 0 6px; }
        .sub { color: #64748b; font-size: 0.9rem; line-height: 1.5; margin: 0 0 22px; }
        label { display: block; font-size: 0.82rem; font-weight: 700; margin-bottom: 6px; color: #334155; }
        input[type=password], input[type=email] {
            width: 100%; padding: 12px 14px; font-size: 1rem; font-family: inherit;
            border: 1px solid #cbd5e1; border-radius: 11px; background: #fff; color: #0f172a;
            margin-bottom: 16px;
        }
        input:focus { outline: none; border-color: #0d9488; box-shadow: 0 0 0 3px rgba(13,148,136,0.14); }
        input[readonly] { background: #f1f5f9; color: #64748b; }
        .pista { font-size: 0.78rem; color: #64748b; margin: -10px 0 16px; }
        button {
            width: 100%; padding: 13px; font-size: 1rem; font-weight: 700; font-family: inherit;
            color: #fff; background: linear-gradient(135deg, #0d9488, #0891b2);
            border: none; border-radius: 11px; cursor: pointer;
        }
        button:hover { opacity: 0.93; }
        .errores {
            background: #fef2f2; border-left: 3px solid #ef4444; border-radius: 8px;
            padding: 11px 14px; margin-bottom: 18px; font-size: 0.85rem; color: #991b1b;
        }
        .errores ul { margin: 0; padding-left: 18px; }
        .pie { margin-top: 20px; text-align: center; font-size: 0.75rem; color: #94a3b8; }
    </style>
</head>
<body>
    <div class="caja">
        <div class="marca">
            <img src="{{ asset('images/solo_logo.png') }}" alt="DocFácil"
                 onerror="this.style.display='none'">
            <p class="consultorio">{{ $clinica->name }}</p>
        </div>

        <h1>Hola, {{ $patient->first_name }}</h1>
        <p class="sub">
            Elige una contraseña y listo: vas a poder ver tus citas, tus recetas
            y tus pagos cuando quieras.
        </p>

        @if ($errors->any())
            <div class="errores">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- full() y no current(): la firma viaja en el query string
             y current() lo tira, con lo que el POST llegaria sin firma. --}}
        <form method="POST" action="{{ url()->full() }}">
            @csrf

            <label for="correo">Tu correo</label>
            <input type="email" id="correo" value="{{ $patient->email }}" readonly>
            <p class="pista">Con este correo vas a entrar de aquí en adelante.</p>

            <label for="password">Contraseña</label>
            <input type="password" id="password" name="password" required
                   autocomplete="new-password" minlength="8">

            <label for="password_confirmation">Repite la contraseña</label>
            <input type="password" id="password_confirmation" name="password_confirmation"
                   required autocomplete="new-password" minlength="8">
            <p class="pista">Mínimo 8 caracteres.</p>

            <button type="submit">Activar mi acceso</button>
        </form>

        <p class="pie">Tu información médica solo la ve tu consultorio.</p>
    </div>
</body>
</html>
