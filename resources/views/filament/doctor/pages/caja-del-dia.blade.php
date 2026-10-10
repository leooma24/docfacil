<x-filament-panels::page>
    <div style="display:flex;align-items:center;gap:0.75rem;flex-wrap:wrap;margin-bottom:1rem;">
        <label style="font-size:0.85rem;color:#374151;">Día
            <input type="date" wire:model.live="dia" style="margin-left:0.4rem;padding:0.35rem 0.6rem;border:1px solid #d1d5db;border-radius:8px;">
        </label>
        <span style="font-size:0.85rem;color:#6b7280;">{{ $esHoy ? 'Hoy, ' : '' }}{{ $fechaTexto }}</span>
    </div>

    {{-- Por forma de pago: lo que debe haber en el cajón (efectivo) y en el banco. --}}
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(150px,1fr));gap:0.75rem;margin-bottom:1rem;">
        @foreach($formas as $clave => $nombre)
            <div style="background:#fff;border:1px solid #e5e7eb;border-radius:12px;padding:0.85rem 1rem;">
                <div style="font-size:0.8rem;color:#6b7280;">{{ $nombre }}</div>
                <div style="font-size:1.35rem;font-weight:800;color:#111827;">${{ number_format($caja['porMetodo'][$clave], 0) }}</div>
            </div>
        @endforeach
        <div style="background:#ecfdf5;border:1px solid #6ee7b7;border-radius:12px;padding:0.85rem 1rem;">
            <div style="font-size:0.8rem;color:#065f46;">Total del día</div>
            <div style="font-size:1.35rem;font-weight:800;color:#064e3b;">${{ number_format($caja['total'], 0) }}</div>
        </div>
    </div>

    @if($facturas->isNotEmpty())
        <div style="background:#fffbeb;border:1px solid #fcd34d;border-radius:12px;padding:0.9rem 1rem;margin-bottom:1rem;">
            <div style="font-weight:800;color:#92400e;margin-bottom:0.4rem;">Facturas por mandar · {{ $facturas->count() }}</div>
            <div style="font-size:0.8rem;color:#92400e;margin-bottom:0.5rem;">Las pidió el paciente. Su contador las hace; aquí se anota cuando ya se mandaron.</div>
            @foreach($facturas as $f)
                <div style="display:flex;align-items:center;gap:0.75rem;padding:0.4rem 0;border-top:1px solid #fde68a;flex-wrap:wrap;">
                    <div style="flex:1;min-width:200px;font-size:0.9rem;">
                        <strong>{{ $f->patient?->first_name }} {{ $f->patient?->last_name }}</strong> · ${{ number_format((float) $f->amount, 0) }} · {{ $f->payment_date?->format('d/m/Y') }}
                    </div>
                    <button type="button" wire:click="facturaEnviada({{ $f->id }})" style="flex:none;padding:0.35rem 0.8rem;border-radius:8px;background:#b45309;color:#fff;font-size:0.8rem;font-weight:700;">Ya se la mandé</button>
                </div>
            @endforeach
        </div>
    @endif

    <h3 style="font-weight:800;margin:0.5rem 0;">Movimientos</h3>
    <p style="font-size:0.8rem;color:#6b7280;margin-bottom:0.5rem;">"Pidió factura" solo deja anotado quién la quiere: la factura la hace su contador. Cuando ya se mandó, tóquele "Ya se la mandé".</p>
    @forelse($caja['movimientos'] as $m)
        <div style="display:flex;align-items:center;gap:0.75rem;flex-wrap:wrap;background:#fff;border:1px solid #e5e7eb;border-radius:12px;padding:0.7rem 1rem;margin-bottom:0.4rem;">
            <div style="flex:none;width:3rem;color:#6b7280;font-size:0.85rem;">{{ $m['hora'] }}</div>
            <div style="flex:1;min-width:180px;">
                <strong>{{ $m['paciente'] }}</strong> · {{ $m['concepto'] }}
                <div style="font-size:0.8rem;color:#6b7280;">{{ $m['metodo'] }}@if($m['factura']) · {{ $m['facturaEnviada'] ? 'factura enviada' : 'pidió factura' }}@endif</div>
            </div>
            <div style="flex:none;font-weight:800;">${{ number_format($m['monto'], 0) }}</div>
            <a href="{{ route('cobro.recibo', $m['cobro']) }}" target="_blank" rel="noopener" style="flex:none;font-size:0.8rem;text-decoration:underline;color:#0f766e;">Recibo</a>
            @unless($m['factura'])
                <button type="button" wire:click="pidioFactura({{ $m['cobro'] }})" style="flex:none;font-size:0.8rem;text-decoration:underline;color:#92400e;background:none;">Pidió factura</button>
            @endunless
        </div>
    @empty
        <p style="font-size:0.9rem;color:#6b7280;">Ese día no entró nada.</p>
    @endforelse
</x-filament-panels::page>
