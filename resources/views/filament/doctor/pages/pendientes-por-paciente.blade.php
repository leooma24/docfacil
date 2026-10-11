<x-filament-panels::page>
    <p style="font-size:0.9rem;color:#6b7280;margin-bottom:1rem;">
        A quién hay que llamar y lo que se quedó a medias con cada paciente. El WhatsApp se abre con el mensaje ya escrito y usted da enviar; nada sale solo.
    </p>

    @if($porLlamar->isEmpty() && $sinRespuesta->isEmpty() && $aMedias->isEmpty())
        <div style="background:#ecfdf5;border:1px solid #6ee7b7;border-radius:16px;padding:1.25rem;font-weight:700;color:#064e3b;">
            Nada pendiente: nadie por llamar, todos los presupuestos tienen respuesta y no hay tratamientos a medias.
        </div>
    @endif

    {{-- A quién hay que llamar (día 5 del plan, 10-oct-2026): se arma sola. --}}
    @if($porLlamar->isNotEmpty() || $resumen['logrados'] > 0)
        <div style="display:flex;justify-content:space-between;align-items:baseline;flex-wrap:wrap;gap:0.5rem;margin:0.5rem 0;">
            <h2 style="font-size:1.05rem;font-weight:800;">A quién hay que llamar · {{ $porLlamar->count() }}</h2>
            @if($resumen['logrados'] > 0)
                <span style="font-size:0.85rem;color:#047857;font-weight:700;">Este mes: {{ $resumen['logrados'] }} {{ $resumen['logrados'] === 1 ? 'logrado' : 'logrados' }}@if($resumen['cobrado'] > 0) · entraron ${{ number_format($resumen['cobrado'], 0) }} de saldos que se llamaron @endif</span>
            @endif
        </div>
        <p style="font-size:0.8rem;color:#6b7280;margin-bottom:0.6rem;">Canceló o no vino y no tiene otra cita, debe algo vencido, le toca su revisión o lleva 6 meses sin venir. Al terminar, marque cómo quedó.</p>
        @foreach($porLlamar as $fila)
            @php $p = $fila['paciente']; @endphp
            <div wire:key="llamar-{{ $p->id }}-{{ $fila['motivo'] }}" x-data="{ dias: 7 }" style="display:flex;align-items:center;gap:0.6rem 0.75rem;flex-wrap:wrap;background:#fff;border:1px solid #e5e7eb;border-radius:12px;padding:0.85rem 1rem;margin-bottom:0.5rem;">
                <div style="flex:1;min-width:220px;">
                    <div style="font-weight:700;">
                        <a href="{{ \App\Filament\Doctor\Pages\PatientProfile::getUrl(['patient' => $p->id]) }}" style="color:#0f172a;">{{ $p->first_name }} {{ $p->last_name }}</a>
                        <span style="margin-left:0.35rem;padding:0.1rem 0.5rem;border-radius:999px;background:#f1f5f9;color:#475569;font-size:0.72rem;font-weight:700;white-space:nowrap;">{{ $motivos[$fila['motivo']] }}</span>
                    </div>
                    <div style="font-size:0.8rem;color:#6b7280;">{{ $fila['texto'] }}@if($p->telefonoDeContacto()) · {{ $p->telefonoDeContacto() }}@endif</div>
                </div>
                @if($fila['whatsapp'])
                    <a href="{{ $fila['whatsapp'] }}" target="_blank" rel="noopener" style="flex:none;background:#25d366;color:#05330f;font-weight:800;font-size:0.85rem;padding:0.5rem 0.9rem;border-radius:12px;text-decoration:none;">WhatsApp</a>
                @endif
                <button type="button" wire:click="marcar({{ $p->id }}, '{{ $fila['motivo'] }}', 'logrado')" style="flex:none;padding:0.45rem 0.8rem;border-radius:8px;background:#ecfdf5;color:#065f46;font-weight:700;font-size:0.8rem;border:1px solid #6ee7b7;">Logrado</button>
                <span style="flex:none;display:inline-flex;align-items:center;gap:0.3rem;font-size:0.8rem;">
                    <button type="button" x-on:click="$wire.marcar({{ $p->id }}, '{{ $fila['motivo'] }}', 'volver', dias)" style="padding:0.45rem 0.8rem;border-radius:8px;background:#fffbeb;color:#92400e;font-weight:700;border:1px solid #fcd34d;">Volver a llamar en</button>
                    <select x-model.number="dias" style="padding:0.35rem 1.6rem 0.35rem 0.5rem;border:1px solid #d1d5db;border-radius:8px;font-size:0.8rem;">
                        <option value="3">3 días</option><option value="7">7 días</option><option value="15">15 días</option><option value="30">30 días</option>
                    </select>
                </span>
                <button type="button" wire:click="marcar({{ $p->id }}, '{{ $fila['motivo'] }}', 'cerrado')" wire:confirm="¿Cerrar el caso? Ya no le vuelve a salir por este motivo." style="flex:none;padding:0.45rem 0.8rem;border-radius:8px;background:#f8fafc;color:#475569;font-weight:700;font-size:0.8rem;border:1px solid #e2e8f0;">Cerrar caso</button>
            </div>
        @endforeach
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
                @if(empty($plan->patient->telefonoDeContacto()))
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
