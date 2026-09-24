@php
    $datos = $this->getViewData();
    $tareas = $datos['tareas'];
    $numeros = $datos['numeros'];
@endphp

<x-filament-widgets::widget>
    <div style="background:#fff;border:1px solid #e5e7eb;border-radius:0.9rem;padding:1.1rem 1.25rem;">

        <div style="display:flex;align-items:baseline;justify-content:space-between;gap:1rem;margin-bottom:0.9rem;flex-wrap:wrap;">
            <div>
                <div style="font-size:1.05rem;font-weight:700;">Hoy toca esto</div>
                <div style="font-size:0.8rem;color:#6b7280;">{{ now()->locale('es')->isoFormat('dddd D [de] MMMM') }}</div>
            </div>
            <a href="{{ \App\Filament\Sales\Pages\ColaDelDia::getUrl() }}"
               style="background:#0f766e;color:#fff;font-weight:600;font-size:0.82rem;padding:0.5rem 0.9rem;border-radius:0.6rem;text-decoration:none;">Abrir la cola del día</a>
        </div>

        <ol style="margin:0;padding-left:1.1rem;">
            @foreach($tareas as $tarea)
                <li style="margin-bottom:0.7rem;">
                    <div style="font-weight:600;color:{{ $tarea['urgente'] ? '#b45309' : '#111827' }};">
                        {{ $tarea['que'] }}
                    </div>
                    <div style="font-size:0.8rem;color:#6b7280;">{{ $tarea['porque'] }}</div>
                </li>
            @endforeach
        </ol>

        <div style="display:flex;gap:1.5rem;flex-wrap:wrap;border-top:1px solid #f3f4f6;margin-top:0.6rem;padding-top:0.75rem;font-size:0.8rem;color:#6b7280;">
            <div>Enviados hoy: <strong style="color:#111827;">{{ $numeros['enviadosHoy'] }}</strong> de {{ $numeros['tope'] }}</div>
            <div>Respuestas hoy: <strong style="color:#111827;">{{ $numeros['respuestasHoy'] }}</strong></div>
            <div>Esta semana: <strong style="color:#111827;">{{ $numeros['enviadosSemana'] }}</strong> enviados, <strong style="color:#111827;">{{ $numeros['respuestasSemana'] }}</strong> respuestas</div>
        </div>

    </div>
</x-filament-widgets::widget>
