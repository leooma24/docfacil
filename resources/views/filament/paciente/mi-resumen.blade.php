<x-filament-widgets::widget>
    @if($paciente)
    <div style="display:grid;gap:0.85rem;">
        {{-- ¿Cómo le fue? --}}
        @if($visita)
            <div x-data="{ estrellas: 0, comentario: '' }" style="background:#fff;border:1px solid #99f6e4;border-radius:12px;padding:1rem 1.1rem;">
                <div style="font-weight:800;color:#0f172a;">¿Cómo le fue en su visita del {{ $visita->starts_at->locale('es')->isoFormat('D [de] MMMM') }}?</div>
                <div style="font-size:0.85rem;color:#64748b;margin-bottom:0.5rem;">Con {{ $visita->doctor?->user?->name ?? 'su doctor' }}. Solo lo ve el consultorio.</div>
                <div style="display:flex;gap:0.25rem;margin-bottom:0.5rem;">
                    @for($i = 1; $i <= 5; $i++)
                        <button type="button" x-on:click="estrellas = {{ $i }}" aria-label="{{ $i }} estrellas"
                            :style="{ color: estrellas >= {{ $i }} ? '#f59e0b' : '#cbd5e1' }" style="font-size:1.9rem;line-height:1;background:none;">★</button>
                    @endfor
                </div>
                <textarea x-model="comentario" maxlength="500" rows="2" placeholder="¿Algo que quiera decirnos? (opcional)" style="width:100%;padding:0.5rem 0.65rem;border:1px solid #d1d5db;border-radius:8px;font-size:0.9rem;"></textarea>
                <button type="button" x-bind:disabled="estrellas === 0" x-on:click="$wire.calificar({{ $visita->id }}, estrellas, comentario)"
                    style="margin-top:0.5rem;padding:0.5rem 1rem;border-radius:8px;background:#0f766e;color:#fff;font-weight:700;" x-bind:style="{ opacity: estrellas === 0 ? .5 : 1 }">Enviar</button>
            </div>
        @endif

        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:0.85rem;">
            {{-- Próxima cita --}}
            <div style="background:#fff;border:1px solid #e5e7eb;border-radius:12px;padding:1rem;">
                <div style="font-size:0.8rem;color:#64748b;">Su próxima cita</div>
                @if($proxima)
                    <div style="font-weight:800;color:#0f172a;font-size:1.05rem;">{{ $proxima->starts_at->locale('es')->isoFormat('dddd D [de] MMMM, HH:mm') }}</div>
                    <div style="font-size:0.85rem;color:#475569;">{{ $proxima->service?->name ?? 'Consulta' }} · {{ $proxima->clinic?->name }}</div>
                @else
                    <div style="font-weight:700;color:#475569;">No tiene cita agendada.</div>
                @endif
            </div>

            {{-- Su cuenta --}}
            <div style="background:#fff;border:1px solid #e5e7eb;border-radius:12px;padding:1rem;">
                <div style="font-size:0.8rem;color:#64748b;">Su cuenta</div>
                @if($cuenta['debe'] > 0)
                    <div style="font-weight:800;color:#b45309;font-size:1.05rem;">Debe ${{ number_format($cuenta['debe'], 0) }}</div>
                @else
                    <div style="font-weight:800;color:#047857;font-size:1.05rem;">Al corriente</div>
                @endif
                <div style="font-size:0.85rem;color:#475569;">Ha pagado ${{ number_format($cuenta['pagado'], 0) }} de ${{ number_format($cuenta['total'], 0) }}@if($cuenta['aFavor'] > 0) · a su favor ${{ number_format($cuenta['aFavor'], 0) }}@endif</div>
                <a href="{{ route('portal.estado-de-cuenta') }}" target="_blank" style="font-size:0.82rem;color:#0f766e;text-decoration:underline;">Estado de cuenta (PDF)</a>
            </div>
        </div>

        @if($presupuestos->isNotEmpty())
            <div style="background:#fff;border:1px solid #e5e7eb;border-radius:12px;padding:1rem;">
                <div style="font-weight:800;color:#0f172a;margin-bottom:0.4rem;">Sus presupuestos</div>
                @foreach($presupuestos as $plan)
                    <div style="display:flex;justify-content:space-between;align-items:center;gap:0.75rem;padding:0.35rem 0;border-top:1px solid #f1f5f9;">
                        <span>{{ $plan->title }} · <span style="color:#64748b;">{{ $plan->status === 'accepted' ? 'Aceptado' : 'Por revisar' }}</span></span>
                        <a href="{{ route('treatment-plan.public', $plan->public_token) }}" style="font-weight:700;color:#0f766e;">Ver</a>
                    </div>
                @endforeach
            </div>
        @endif

        @if($porFirmar->isNotEmpty())
            <div style="background:#fffbeb;border:1px solid #fcd34d;border-radius:12px;padding:1rem;">
                <div style="font-weight:800;color:#92400e;margin-bottom:0.4rem;">Para firmar antes de su cita</div>
                @foreach($porFirmar as $c)
                    <div style="display:flex;justify-content:space-between;align-items:center;gap:0.75rem;padding:0.35rem 0;">
                        <span>{{ $c->title }}</span>
                        <a href="{{ $ligaFirmar($c) }}" style="padding:0.4rem 0.9rem;border-radius:8px;background:#b45309;color:#fff;font-weight:700;">Firmar</a>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
    @endif
</x-filament-widgets::widget>
