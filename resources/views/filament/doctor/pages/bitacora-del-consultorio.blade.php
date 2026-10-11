<x-filament-panels::page>
    <div style="display:flex;gap:0.75rem;flex-wrap:wrap;align-items:center;margin-bottom:1rem;">
        <select wire:model.live="quien" style="padding:0.4rem 2rem 0.4rem 0.6rem;border:1px solid #d1d5db;border-radius:8px;">
            <option value="">Todo el equipo</option>
            @foreach($equipo as $id => $nombre)<option value="{{ $id }}">{{ $nombre }}</option>@endforeach
        </select>
        <input type="date" wire:model.live="dia" style="padding:0.35rem 0.6rem;border:1px solid #d1d5db;border-radius:8px;">
        <span style="font-size:0.82rem;color:#64748b;">Los últimos 200 movimientos. La NOM-024 pide saber quién vio o cambió cada expediente.</span>
    </div>

    <div style="background:#fff;border:1px solid #e5e7eb;border-radius:12px;">
        @forelse($registros as $r)
            <div style="display:flex;gap:0.75rem;flex-wrap:wrap;padding:0.6rem 1rem;border-bottom:1px solid #f1f5f9;font-size:0.88rem;">
                <span style="flex:none;width:8.5rem;color:#64748b;">{{ $r->created_at->format('d/m/Y H:i') }}</span>
                <span style="flex:none;min-width:9rem;font-weight:700;color:#0f172a;">{{ $r->causer?->name }}</span>
                <span style="flex:1;min-width:200px;">
                    {{ \App\Models\Patient::queHizo($r) }}
                    @if($dq = \App\Filament\Doctor\Pages\BitacoraDelConsultorio::deQuien($r->subject)) · <strong>{{ $dq }}</strong> @endif
                </span>
            </div>
        @empty
            <div style="padding:1rem;color:#94a3b8;">Sin movimientos.</div>
        @endforelse
    </div>
</x-filament-panels::page>
