<x-filament-panels::page>
    <div style="display:flex;align-items:center;gap:0.5rem;flex-wrap:wrap;margin-bottom:1rem;">
        <button type="button" wire:click="otroDia(-1)" style="padding:0.4rem 0.8rem;border:1px solid #d1d5db;border-radius:8px;background:#fff;font-weight:700;">‹</button>
        <button type="button" wire:click="hoy" style="padding:0.4rem 0.8rem;border:1px solid #d1d5db;border-radius:8px;background:#fff;font-weight:700;font-size:0.85rem;">Hoy</button>
        <button type="button" wire:click="otroDia(1)" style="padding:0.4rem 0.8rem;border:1px solid #d1d5db;border-radius:8px;background:#fff;font-weight:700;">›</button>
        <span style="font-weight:800;color:#0f172a;margin-left:0.25rem;">{{ $fechaTexto }}</span>
        @if($esHoy)<span style="font-size:0.8rem;color:#047857;font-weight:700;">Hoy</span>@endif
        <input type="date" wire:model.live="dia" style="margin-left:auto;padding:0.35rem 0.6rem;border:1px solid #d1d5db;border-radius:8px;">
    </div>

    @php $estados = ['scheduled' => ['Programada', '#475569', '#f1f5f9'], 'confirmed' => ['Confirmada', '#065f46', '#d1fae5'], 'in_progress' => ['En consulta', '#1e40af', '#dbeafe'], 'completed' => ['Atendida', '#64748b', '#f8fafc'], 'no_show' => ['No vino', '#991b1b', '#fee2e2']]; @endphp

    <div style="overflow-x:auto;">
        <div style="display:grid;grid-template-columns:repeat({{ max(1, $columnas->count()) }}, minmax(240px, 1fr));gap:0.75rem;min-width:min(100%, {{ $columnas->count() * 250 }}px);">
            @foreach($columnas as $col)
                <div style="background:#fff;border:1px solid #e5e7eb;border-radius:12px;padding:0.85rem;">
                    <div style="font-weight:800;color:#0f172a;">{{ $col['doctor']->user?->name ?? 'Doctor' }}</div>
                    <div style="font-size:0.78rem;color:#64748b;margin-bottom:0.6rem;">{{ $col['citas']->count() }} {{ $col['citas']->count() === 1 ? 'cita' : 'citas' }}</div>

                    @if($col['cerrado'])
                        <div style="padding:0.5rem 0.7rem;border-radius:8px;background:#fef3c7;color:#92400e;font-size:0.82rem;font-weight:700;margin-bottom:0.5rem;">No atiende: {{ $col['cerrado']->reason ?: 'día cerrado' }}</div>
                    @endif

                    @forelse($col['citas'] as $cita)
                        @php $e = $estados[$cita->status] ?? [$cita->status, '#475569', '#f1f5f9']; @endphp
                        <a href="{{ \App\Filament\Doctor\Resources\AppointmentResource::getUrl('edit', ['record' => $cita]) }}"
                           style="display:block;padding:0.55rem 0.65rem;border:1px solid #e5e7eb;border-left:4px solid #14b8a6;border-radius:8px;margin-bottom:0.4rem;color:#0f172a;">
                            <div style="display:flex;justify-content:space-between;gap:0.5rem;">
                                <strong>{{ $cita->starts_at->format('H:i') }}</strong>
                                <span style="font-size:0.7rem;font-weight:700;padding:0.05rem 0.45rem;border-radius:999px;color:{{ $e[1] }};background:{{ $e[2] }};">{{ $e[0] }}</span>
                            </div>
                            <div style="font-size:0.85rem;">{{ $cita->patient?->first_name }} {{ $cita->patient?->last_name }}</div>
                            <div style="font-size:0.75rem;color:#64748b;">{{ $cita->service?->name ?? 'Consulta' }} · hasta {{ $cita->ends_at->format('H:i') }}</div>
                        </a>
                    @empty
                        <div style="font-size:0.82rem;color:#94a3b8;margin-bottom:0.4rem;">Sin citas.</div>
                    @endforelse

                    @if(! empty($col['libres']))
                        <div style="margin-top:0.6rem;font-size:0.75rem;font-weight:800;color:#047857;">Libre</div>
                        @foreach($col['libres'] as [$de, $a])
                            <a href="{{ \App\Filament\Doctor\Resources\AppointmentResource::getUrl('create') }}?doctor={{ $col['doctor']->id }}&inicio={{ $this->dia }}T{{ $de }}"
                               style="display:inline-block;margin:0.2rem 0.25rem 0 0;padding:0.2rem 0.55rem;border-radius:999px;background:#ecfdf5;color:#065f46;font-size:0.78rem;font-weight:700;">{{ $de }} a {{ $a }}</a>
                        @endforeach
                    @endif
                </div>
            @endforeach
        </div>
    </div>
</x-filament-panels::page>
