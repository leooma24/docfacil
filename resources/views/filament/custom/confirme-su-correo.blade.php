{{-- Desde el 11-oct-2026 el correo sin confirmar no detiene la entrada:
     aquí se le recuerda, sin estorbar. --}}
@php($usuario = auth()->user())
@if($usuario instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $usuario->hasVerifiedEmail())
    <div style="display:flex;flex-wrap:wrap;align-items:center;gap:0.5rem 1rem;padding:0.7rem 1rem;margin-bottom:1rem;background:#fffbeb;border:1px solid #fde68a;border-radius:12px;color:#78350f;font-size:0.9rem;">
        <span style="flex:1;min-width:200px;"><strong>Confirme su correo.</strong> Le mandamos una liga a {{ $usuario->email }}; sirve para recuperar su contraseña si la olvida.</span>
        <a href="{{ route('filament.doctor.auth.email-verification.prompt') }}" style="font-weight:700;text-decoration:underline;">Mandarla otra vez</a>
    </div>
@endif
