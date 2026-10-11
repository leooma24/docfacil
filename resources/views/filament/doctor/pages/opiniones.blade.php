<x-filament-panels::page>
    <div style="display:flex;align-items:center;gap:1rem;flex-wrap:wrap;background:#fff;border:1px solid #e5e7eb;border-radius:12px;padding:1rem 1.1rem;margin-bottom:1rem;">
        <div style="font-size:2rem;font-weight:800;color:#0f172a;">{{ $cuantas ? number_format($promedio, 1) : '—' }}</div>
        <div>
            <div style="color:#f59e0b;font-size:1.3rem;letter-spacing:2px;">{{ str_repeat('★', (int) round($promedio)) }}<span style="color:#e2e8f0;">{{ str_repeat('★', 5 - (int) round($promedio)) }}</span></div>
            <div style="font-size:0.85rem;color:#64748b;">{{ $cuantas }} {{ $cuantas === 1 ? 'opinión' : 'opiniones' }}. Las contestan en su portal después de la visita.</div>
        </div>
    </div>

    @forelse($encuestas as $e)
        <div style="background:#fff;border:1px solid #e5e7eb;border-radius:12px;padding:0.8rem 1rem;margin-bottom:0.5rem;">
            <div style="display:flex;justify-content:space-between;gap:0.75rem;flex-wrap:wrap;">
                <span><strong>{{ $e->patient?->first_name }} {{ $e->patient?->last_name }}</strong> <span style="color:#64748b;font-size:0.85rem;">· {{ $e->doctor?->user?->name }} · {{ $e->created_at->format('d/m/Y') }}</span></span>
                <span style="color:#f59e0b;">{{ str_repeat('★', $e->estrellas) }}<span style="color:#e2e8f0;">{{ str_repeat('★', 5 - $e->estrellas) }}</span></span>
            </div>
            @if($e->comentario)<div style="font-size:0.9rem;color:#334155;margin-top:0.3rem;">{{ $e->comentario }}</div>@endif
        </div>
    @empty
        <div style="color:#94a3b8;">Todavía no hay opiniones. Les sale a sus pacientes en su portal después de cada visita.</div>
    @endforelse
</x-filament-panels::page>
