<x-filament-panels::page>
    <div style="display:flex;gap:0.5rem;flex-wrap:wrap;margin-bottom:1rem;">
        @foreach(['todos' => 'Todos', 'atrasados' => 'Control atrasado', 'sin_cita' => 'Sin próxima cita', 'deben' => 'Mensualidad vencida'] as $clave => $nombre)
            <button type="button" wire:click="$set('filtro', '{{ $clave }}')"
                style="padding:0.4rem 0.85rem;border-radius:999px;font-size:0.85rem;font-weight:700;border:1px solid {{ $filtro === $clave ? '#0f766e' : '#d1d5db' }};background:{{ $filtro === $clave ? '#0f766e' : '#fff' }};color:{{ $filtro === $clave ? '#fff' : '#334155' }};">
                {{ $nombre }} · {{ $cuantos[$clave] }}
            </button>
        @endforeach
    </div>

    @forelse($filas as $fila)
        @php $p = $fila['paciente']; @endphp
        <div style="display:flex;align-items:center;gap:0.6rem 1rem;flex-wrap:wrap;background:#fff;border:1px solid #e5e7eb;border-radius:12px;padding:0.85rem 1rem;margin-bottom:0.5rem;">
            <div style="flex:1;min-width:220px;">
                <a href="{{ \App\Filament\Doctor\Pages\PatientProfile::getUrl(['patient' => $p->id]) }}" style="font-weight:800;color:#0f172a;">{{ $p->first_name }} {{ $p->last_name }}</a>
                <div style="font-size:0.8rem;color:#64748b;">
                    Último control: {{ $fila['ultimo'] ? $fila['ultimo']->locale('es')->isoFormat('D [de] MMM') : 'sin registro' }}
                    · Próximo: {{ $fila['proximo'] ? $fila['proximo']->locale('es')->isoFormat('ddd D [de] MMM, HH:mm') : 'sin cita' }}
                    @if($fila['delMes']) · Mensualidad de este mes: {{ $fila['delMes']->remaining > 0 ? 'pendiente' : 'pagada' }} @endif
                </div>
                <div style="display:flex;gap:0.35rem;flex-wrap:wrap;margin-top:0.3rem;">
                    @if($fila['atrasado'])<span style="font-size:0.72rem;font-weight:800;padding:0.1rem 0.5rem;border-radius:999px;background:#fee2e2;color:#991b1b;">Control atrasado</span>@endif
                    @if(! $fila['proximo'] && ! $fila['atrasado'])<span style="font-size:0.72rem;font-weight:800;padding:0.1rem 0.5rem;border-radius:999px;background:#fef3c7;color:#92400e;">Sin próxima cita</span>@endif
                    @if($fila['vencido'] > 0)<span style="font-size:0.72rem;font-weight:800;padding:0.1rem 0.5rem;border-radius:999px;background:#ffedd5;color:#9a3412;">Mensualidad vencida ${{ number_format($fila['vencido'], 0) }}</span>@endif
                </div>
            </div>
            @if(! $fila['proximo'])
                <a href="{{ \App\Filament\Doctor\Resources\AppointmentResource::getUrl('create') }}?patient={{ $p->id }}@if($servicio)&servicio={{ $servicio }}@endif"
                   style="flex:none;background:#0f766e;color:#fff;font-weight:800;font-size:0.85rem;padding:0.5rem 0.9rem;border-radius:12px;">Agendar control</a>
            @endif
            @if($fila['plan'])
                <a href="{{ \App\Filament\Doctor\Resources\PaymentPlanResource::getUrl('view', ['record' => $fila['plan']]) }}" style="flex:none;font-size:0.8rem;text-decoration:underline;color:#0f766e;">Plan de pagos</a>
            @endif
        </div>
    @empty
        <div style="background:#ecfdf5;border:1px solid #6ee7b7;border-radius:12px;padding:1rem;font-weight:700;color:#064e3b;">
            {{ $filtro === 'todos' ? 'Todavía no hay pacientes en ortodoncia: salen al tener un plan de pagos de ortodoncia o un control reciente.' : 'Nadie en este grupo.' }}
        </div>
    @endforelse
</x-filament-panels::page>
