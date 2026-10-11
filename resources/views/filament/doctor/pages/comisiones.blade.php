<x-filament-panels::page>
    <div style="display:flex;align-items:center;gap:0.75rem;flex-wrap:wrap;margin-bottom:0.5rem;">
        <label style="font-size:0.85rem;color:#374151;">Mes
            <input type="month" wire:model.live="mes" style="margin-left:0.4rem;padding:0.35rem 0.6rem;border:1px solid #d1d5db;border-radius:8px;">
        </label>
        <span style="font-weight:800;color:#0f172a;">{{ $mesTexto }}</span>
    </div>
    <p style="font-size:0.85rem;color:#64748b;margin-bottom:1rem;max-width:48rem;">
        A cada doctor le toca su parte de lo que los pacientes ya pagaron en el mes: su porcentaje o el monto fijo de ese tratamiento. Lo que falta por cobrar se le paga cuando el paciente termine de pagar.
    </p>

    @php $sem = ['pagado' => ['Pagado', '#065f46', '#d1fae5'], 'parcial' => ['Parcial', '#92400e', '#fef3c7'], 'sin_pagar' => ['Sin pagar', '#991b1b', '#fee2e2']]; @endphp

    @foreach($tarjetas as $t)
        @php $d = $t['doctor']; $liq = $t['liq']; @endphp
        <div style="background:#fff;border:1px solid #e5e7eb;border-radius:12px;padding:1rem 1.1rem;margin-bottom:0.9rem;">
            <div style="display:flex;align-items:center;gap:0.75rem;flex-wrap:wrap;">
                <div style="flex:1;min-width:220px;">
                    <div style="font-weight:800;color:#0f172a;font-size:1.05rem;">{{ $d->user?->name ?? 'Doctor' }}</div>
                    <div style="font-size:0.8rem;color:#64748b;">
                        @if($d->comision_porcentaje !== null) {{ rtrim(rtrim(number_format((float) $d->comision_porcentaje, 2), '0'), '.') }}% de lo cobrado @else Sin porcentaje @endif
                        @if(! empty($d->comision_montos)) · {{ count($d->comision_montos) }} {{ count($d->comision_montos) === 1 ? 'monto fijo' : 'montos fijos' }} @endif
                    </div>
                </div>
                <div style="text-align:right;">
                    <div style="font-size:0.75rem;color:#64748b;">Le toca este mes</div>
                    <div style="font-size:1.4rem;font-weight:800;color:#0f766e;">${{ number_format($liq['total'], 2) }}</div>
                    @if($liq['porCobrar'] > 0)<div style="font-size:0.75rem;color:#92400e;">y ${{ number_format($liq['porCobrar'], 0) }} cuando paguen lo pendiente</div>@endif
                </div>
            </div>

            <div style="display:flex;gap:0.5rem;flex-wrap:wrap;margin-top:0.6rem;">
                {{ ($this->configurarAction)(['doctor' => $d->id]) }}
                @if($t['pagada'])
                    <span style="font-size:0.8rem;font-weight:700;color:#065f46;padding:0.3rem 0.6rem;background:#d1fae5;border-radius:8px;">Pagada el {{ $t['pagada']->created_at->format('d/m/Y') }} · ${{ number_format((float) $t['pagada']->monto, 2) }}</span>
                @elseif($liq['total'] > 0)
                    {{ ($this->pagarAction)(['doctor' => $d->id]) }}
                @endif
            </div>

            @if(! empty($liq['renglones']))
                <div style="margin-top:0.75rem;border-top:1px solid #f1f5f9;">
                    @foreach($liq['renglones'] as $r)
                        @php $s = $sem[$r['semaforo']]; @endphp
                        <div x-data="{ ver: false }" style="padding:0.55rem 0;border-bottom:1px solid #f1f5f9;">
                            <div style="display:flex;align-items:center;gap:0.6rem;flex-wrap:wrap;font-size:0.88rem;">
                                <span style="flex:none;font-size:0.7rem;font-weight:800;padding:0.1rem 0.5rem;border-radius:999px;color:{{ $s[1] }};background:{{ $s[2] }};">{{ $s[0] }}</span>
                                <span style="flex:1;min-width:180px;">{{ $r['paciente'] }} · {{ $r['concepto'] }} <span style="color:#64748b;">(${{ number_format($r['total'], 0) }})</span></span>
                                <strong style="flex:none;">${{ number_format($r['ganado'], 2) }}</strong>
                                <button type="button" x-on:click="ver = !ver" style="flex:none;font-size:0.78rem;color:#0f766e;text-decoration:underline;background:none;">Explicar</button>
                            </div>
                            <div x-show="ver" x-cloak style="font-size:0.82rem;color:#475569;margin-top:0.3rem;padding:0.5rem 0.7rem;background:#f8fafc;border-radius:8px;">{{ $r['explicacion'] }}</div>
                        </div>
                    @endforeach
                </div>
            @else
                <div style="margin-top:0.6rem;font-size:0.85rem;color:#94a3b8;">Sin cobros suyos este mes. Los cobros cuentan para el doctor de la cita o del plan de pagos.</div>
            @endif
        </div>
    @endforeach

    <x-filament-actions::modals />
</x-filament-panels::page>
