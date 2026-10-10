{{-- Acceso denegado. De usted, con estilos propios (sin el Tailwind de una
     CDN) y según quién llega: al doctor se le dice que puede ser su plan, a la
     asistente que se lo pida al doctor; nadie ve las ligas de Ventas ni de
     Administración (auditoría del 12-oct-2026). --}}
@php
    $usuario = auth()->user();
    $esDelConsultorio = $usuario && $usuario->clinic_id && in_array($usuario->role, ['doctor', 'staff'], true);
    $esAsistente = $esDelConsultorio && $usuario->esAsistente();
    // El motivo, si quien bloqueó dejó uno (p. ej. "Enlace expirado").
    $motivo = isset($exception) ? trim((string) $exception->getMessage()) : '';
    if (in_array($motivo, ['', 'Forbidden', 'This action is unauthorized.'], true)) {
        $motivo = '';
    }
    $ayuda = 'https://wa.me/52' . preg_replace('/\D/', '', (string) config('services.notifications.phone') ?: '6682493398')
        . '?text=' . urlencode('Hola, me salió "No tiene acceso" en DocFácil' . ($esDelConsultorio ? ' (' . ($usuario->clinic?->name ?? '') . ')' : '') . '.');
@endphp
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>No tiene acceso · DocFácil</title>
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 24px 16px;
               font-family: ui-sans-serif, system-ui, -apple-system, "Segoe UI", Roboto, sans-serif; background: #f8fafc; color: #0f172a; }
        .caja { width: 100%; max-width: 480px; background: #fff; border: 1px solid #e5e7eb; border-radius: 16px; padding: 28px 24px; text-align: center; }
        .logo { height: 40px; margin-bottom: 18px; }
        .icono { width: 56px; height: 56px; margin: 0 auto 14px; border-radius: 16px; background: #fffbeb; color: #b45309; display: flex; align-items: center; justify-content: center; }
        h1 { font-size: 22px; margin: 0 0 8px; }
        p { font-size: 16px; line-height: 1.5; color: #475569; margin: 0 0 10px; }
        .motivo { font-size: 15px; background: #f8fafc; border-radius: 12px; padding: 10px 12px; color: #334155; }
        .botones { display: flex; flex-direction: column; gap: 10px; margin-top: 18px; }
        .boton { display: flex; align-items: center; justify-content: center; min-height: 46px; border-radius: 12px; font-size: 16px; font-weight: 700; text-decoration: none; }
        .principal { background: #0d9488; color: #fff; }
        .secundario { background: #fff; color: #0f766e; border: 1px solid #99f6e4; }
        .pie { margin-top: 18px; font-size: 14px; color: #64748b; }
        .pie a { color: #0f766e; font-weight: 700; }
    </style>
</head>
<body>
    <div class="caja">
        <a href="{{ $esDelConsultorio ? url('/doctor') : url('/') }}"><img class="logo" src="{{ asset('images/logo_doc_facil.png') }}" alt="DocFácil"></a>
        <div class="icono" aria-hidden="true">
            <svg width="28" height="28" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z"/></svg>
        </div>
        <h1>No tiene acceso a esta parte</h1>

        @if($motivo)
            <p class="motivo">{{ $motivo }}</p>
        @endif

        @if($esAsistente)
            <p>Esta parte la maneja el doctor. Pídale que lo haga él o que le dé permiso.</p>
        @elseif($esDelConsultorio)
            <p>Puede ser que esta función no viene en su plan. Sus datos siguen guardados; en Mi plan ve qué trae cada uno.</p>
        @else
            <p>Puede ser que su sesión se haya cerrado. Entre otra vez con su correo.</p>
        @endif

        <div class="botones">
            @if($esDelConsultorio)
                <a class="boton principal" href="{{ url('/doctor') }}">Volver a mi consultorio</a>
                @unless($esAsistente)
                    <a class="boton secundario" href="{{ url('/doctor/actualizar-plan') }}">Ver qué trae cada plan</a>
                @endunless
            @else
                <a class="boton principal" href="{{ url('/doctor/login') }}">Entrar a mi consultorio</a>
                <a class="boton secundario" href="{{ url('/paciente/login') }}">Soy paciente</a>
            @endif
        </div>

        <p class="pie">¿Cree que es un error? <a href="{{ $ayuda }}" target="_blank" rel="noopener">Escríbanos por WhatsApp</a></p>
    </div>
</body>
</html>
