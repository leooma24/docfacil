<x-filament-panels::page>
    {{-- Al regresar de WhatsApp, la fila ya avanzó: se vuelve a leer sola. --}}
    <div x-data
         x-on:visibilitychange.document="if (! document.hidden) $wire.$refresh()"
         x-on:focus.window="$wire.$refresh()">

        <p style="font-size:0.9rem;color:#6b7280;margin-bottom:1rem;">
            Citas de {{ $manana }}. Toque el botón: se abre su WhatsApp con el mensaje ya escrito, usted da enviar y regresa aquí por el siguiente.
        </p>

        @if($total > 0)
            <div style="font-weight:700;font-size:1.05rem;margin-bottom:0.5rem;">Van {{ $enviados }} de {{ $total }}</div>
            <div style="height:8px;background:#e5e7eb;border-radius:999px;overflow:hidden;margin-bottom:1.25rem;">
                <div style="height:8px;background:#25d366;width:{{ round($enviados * 100 / $total) }}%;"></div>
            </div>
        @endif

        @if($pendientes->isNotEmpty())
            @php $siguiente = $pendientes->first(); @endphp
            <div style="background:#ecfdf5;border:1px solid #6ee7b7;border-radius:16px;padding:1.25rem;margin-bottom:1.25rem;">
                <div style="font-size:0.8rem;color:#065f46;">Sigue</div>
                <div style="font-size:1.25rem;font-weight:800;color:#064e3b;">{{ $siguiente->patient->first_name }} {{ $siguiente->patient->last_name }} · {{ $siguiente->starts_at->format('H:i') }}</div>
                <a href="{{ route('cita.recordar', $siguiente->id) }}" target="_blank" rel="noopener"
                   style="display:inline-block;margin-top:0.75rem;background:#25d366;color:#05330f;font-weight:800;font-size:1rem;padding:0.75rem 1.25rem;border-radius:12px;text-decoration:none;">
                    Mandar por WhatsApp
                </a>
            </div>
        @elseif($total > 0)
            <div style="background:#ecfdf5;border:1px solid #6ee7b7;border-radius:16px;padding:1.25rem;margin-bottom:1.25rem;font-weight:700;color:#064e3b;">
                Listo: todos los de mañana tienen su recordatorio.
            </div>
        @else
            <div style="background:#f9fafb;border:1px solid #e5e7eb;border-radius:16px;padding:1.25rem;margin-bottom:1.25rem;color:#374151;">
                Mañana no hay citas por recordar.
            </div>
        @endif

        @if($pendientes->count() > 1)
            <h3 style="font-weight:700;margin:1rem 0 0.5rem;">Faltan</h3>
            @foreach($pendientes->skip(1) as $cita)
                <div style="display:flex;align-items:center;gap:0.75rem;background:#fff;border:1px solid #e5e7eb;border-radius:12px;padding:0.7rem 1rem;margin-bottom:0.4rem;">
                    <div style="flex:1;min-width:0;"><strong>{{ $cita->patient->first_name }} {{ $cita->patient->last_name }}</strong> · {{ $cita->starts_at->format('H:i') }}</div>
                    <a href="{{ route('cita.recordar', $cita->id) }}" target="_blank" rel="noopener"
                       style="flex:none;color:#0f8a4d;font-weight:700;text-decoration:underline;">Mandar</a>
                </div>
            @endforeach
        @endif

        @if($recordados->isNotEmpty())
            <h3 style="font-weight:700;margin:1.25rem 0 0.5rem;color:#6b7280;">Ya recordados</h3>
            @foreach($recordados as $cita)
                <div style="display:flex;align-items:center;gap:0.75rem;background:#f9fafb;border:1px solid #e5e7eb;border-radius:12px;padding:0.7rem 1rem;margin-bottom:0.4rem;color:#6b7280;">
                    <div style="flex:1;min-width:0;">✓ {{ $cita->patient->first_name }} {{ $cita->patient->last_name }} · {{ $cita->starts_at->format('H:i') }}</div>
                    <a href="{{ route('cita.recordar', $cita->id) }}" target="_blank" rel="noopener" style="flex:none;text-decoration:underline;">Mandar otra vez</a>
                </div>
            @endforeach
        @endif

        @if($sinTelefono->isNotEmpty())
            <h3 style="font-weight:700;margin:1.25rem 0 0.5rem;">Sin teléfono: llámele</h3>
            @foreach($sinTelefono as $cita)
                <div style="background:#fffbeb;border:1px solid #fcd34d;border-radius:12px;padding:0.7rem 1rem;margin-bottom:0.4rem;">
                    <strong>{{ $cita->patient->first_name }} {{ $cita->patient->last_name }}</strong> · {{ $cita->starts_at->format('H:i') }}
                </div>
            @endforeach
        @endif
    </div>
</x-filament-panels::page>
