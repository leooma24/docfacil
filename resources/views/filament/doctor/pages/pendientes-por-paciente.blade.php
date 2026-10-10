<x-filament-panels::page>
    <p style="font-size:0.9rem;color:#6b7280;margin-bottom:1rem;">
        Lo que se quedó a medias con cada paciente. El recordatorio abre su WhatsApp con el mensaje ya escrito y usted da enviar; se le recuerda una vez al mes, no más.
    </p>

    @if($sinRespuesta->isEmpty() && $aMedias->isEmpty())
        <div style="background:#ecfdf5;border:1px solid #6ee7b7;border-radius:16px;padding:1.25rem;font-weight:700;color:#064e3b;">
            Nada pendiente: todos los presupuestos tienen respuesta y no hay tratamientos a medias.
        </div>
    @endif

    {{-- Presupuestos que el paciente tiene y no ha contestado. --}}
    @if($sinRespuesta->isNotEmpty())
        <h2 style="font-size:1.05rem;font-weight:800;margin:0.5rem 0;">Sin respuesta · {{ $sinRespuesta->count() }}</h2>
        <p style="font-size:0.8rem;color:#6b7280;margin-bottom:0.6rem;">Se los mandó y no han dicho que sí ni que no (más de 7 días).</p>
        @foreach($sinRespuesta as $fila)
            @php $plan = $fila['plan']; @endphp
            <div style="display:flex;align-items:center;gap:0.75rem;flex-wrap:wrap;background:#fff;border:1px solid #e5e7eb;border-radius:12px;padding:0.85rem 1rem;margin-bottom:0.5rem;">
                <div style="flex:1;min-width:220px;">
                    <div style="font-weight:700;">{{ $plan->patient->first_name }} {{ $plan->patient->last_name }} · {{ $plan->title }}</div>
                    <div style="font-size:0.8rem;color:#6b7280;">
                        ${{ number_format((float) $plan->total, 0) }} · se lo mandó hace {{ $fila['dias'] }} días
                        @if($plan->last_reminded_at) · recordado el {{ $plan->last_reminded_at->format('d/m') }} @endif
                        @if($fila['debe'] > 0) · <strong style="color:#b45309;">Debe ${{ number_format($fila['debe'], 0) }}</strong> @endif
                    </div>
                </div>
                @if($fila['toca'])
                    <a href="{{ route('plan.recordar', $plan) }}" target="_blank" rel="noopener"
                       style="flex:none;background:#25d366;color:#05330f;font-weight:800;font-size:0.85rem;padding:0.55rem 1rem;border-radius:12px;text-decoration:none;">Recordar por WhatsApp</a>
                @else
                    <span style="flex:none;font-size:0.8rem;color:#6b7280;">Ya se le recordó. Vuelve a tocar en {{ max(1, $diasEntreRecordatorios - (int) $plan->last_reminded_at->diffInDays(now())) }} días.</span>
                @endif
                <a href="{{ \App\Filament\Doctor\Resources\TreatmentPlanResource::getUrl('edit', ['record' => $plan->id]) }}" style="flex:none;font-size:0.8rem;text-decoration:underline;color:#0f766e;">Ver</a>
            </div>
        @endforeach
    @endif

    {{-- Aceptados que nadie ha terminado de agendar. --}}
    @if($aMedias->isNotEmpty())
        <h2 style="font-size:1.05rem;font-weight:800;margin:1.5rem 0 0.5rem;">A medias · {{ $aMedias->count() }}</h2>
        <p style="font-size:0.8rem;color:#6b7280;margin-bottom:0.6rem;">Ya dijeron que sí y les falta agendar el siguiente tratamiento.</p>
        @foreach($aMedias as $fila)
            @php $plan = $fila['plan']; @endphp
            <div style="display:flex;align-items:center;gap:0.75rem;flex-wrap:wrap;background:#fff;border:1px solid #e5e7eb;border-radius:12px;padding:0.85rem 1rem;margin-bottom:0.5rem;">
                <div style="flex:1;min-width:220px;">
                    <div style="font-weight:700;">{{ $plan->patient->first_name }} {{ $plan->patient->last_name }} · {{ $plan->title }}</div>
                    <div style="font-size:0.8rem;color:#6b7280;">
                        Van {{ $fila['avance'] }} · sigue: {{ $fila['siguiente'] }}
                        @if($fila['debe'] > 0) · <strong style="color:#b45309;">Debe ${{ number_format($fila['debe'], 0) }}</strong> @endif
                    </div>
                </div>
                <a href="{{ $this->ligaParaAgendar($plan) }}"
                   style="flex:none;background:#0f766e;color:#fff;font-weight:800;font-size:0.85rem;padding:0.55rem 1rem;border-radius:12px;text-decoration:none;">Agendar</a>
                @if(empty($plan->patient->phone))
                    {{-- Sin teléfono no hay a dónde mandarlo. --}}
                @elseif($fila['toca'])
                    <a href="{{ route('plan.recordar', $plan) }}" target="_blank" rel="noopener"
                       style="flex:none;font-size:0.8rem;text-decoration:underline;color:#0f8a4d;">Recordar por WhatsApp</a>
                @else
                    <span style="flex:none;font-size:0.8rem;color:#6b7280;">Recordado el {{ $plan->last_reminded_at->format('d/m') }}</span>
                @endif
            </div>
        @endforeach
    @endif
</x-filament-panels::page>
