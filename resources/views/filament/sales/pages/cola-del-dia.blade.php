@php
    $datos = $this->getViewData();
    $numeros = $datos['numeros'];
@endphp

<x-filament-panels::page>

    {{-- Los números del día. Sin esto, "cómo vamos" se contesta de memoria. --}}
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(150px,1fr));gap:0.75rem;margin-bottom:1.5rem;">
        @php
            $tarjetas = [
                ['Enviados hoy', $numeros['enviadosHoy'] . ' de ' . $numeros['tope'], '#0f766e'],
                ['Respuestas hoy', $numeros['respuestasHoy'], '#b45309'],
                ['Enviados esta semana', $numeros['enviadosSemana'], '#475569'],
                ['Respuestas esta semana', $numeros['respuestasSemana'], '#475569'],
                ['Demos por hacer', $numeros['demosAgendadas'], '#475569'],
            ];
        @endphp
        @foreach($tarjetas as [$titulo, $valor, $color])
            <div style="background:#fff;border:1px solid #e5e7eb;border-radius:0.75rem;padding:0.9rem 1rem;">
                <div style="font-size:0.7rem;text-transform:uppercase;letter-spacing:0.05em;color:#6b7280;">{{ $titulo }}</div>
                <div style="font-size:1.5rem;font-weight:800;color:{{ $color }};">{{ $valor }}</div>
            </div>
        @endforeach
    </div>

    {{-- El embudo por etapas: enseña en qué escalón se cae, no cuánto se vendió. --}}
    <div style="background:#fff;border:1px solid #e5e7eb;border-radius:0.75rem;padding:0.9rem 1rem;margin-bottom:1.5rem;">
        <div style="font-size:0.7rem;text-transform:uppercase;letter-spacing:0.05em;color:#6b7280;margin-bottom:0.6rem;">Dónde se cae el embudo</div>
        <div style="display:flex;flex-wrap:wrap;gap:1.25rem;">
            @foreach($numeros['embudo'] as $paso)
                <div>
                    <div style="font-size:0.75rem;color:#6b7280;">{{ $paso['etapa'] }}</div>
                    <div style="font-size:1.15rem;font-weight:700;">
                        {{ $paso['valor'] }}
                        @if($paso['tasa'] !== null)
                            <span style="font-size:0.75rem;font-weight:600;color:{{ $paso['tasa'] == 0 ? '#b91c1c' : '#6b7280' }};">{{ $paso['tasa'] }}%</span>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    {{-- 1. Contestaron. Va primero porque es lo que más cuesta dejar enfriar. --}}
    @if($datos['contestaron']->isNotEmpty())
        <div style="margin-bottom:1.75rem;">
            <h2 style="font-size:1rem;font-weight:700;margin-bottom:0.25rem;">Te contestaron y están esperando</h2>
            <p style="font-size:0.8rem;color:#6b7280;margin-bottom:0.75rem;">Contéstales primero. Un prospecto que levantó la mano y se enfría ya no vuelve.</p>

            @foreach($datos['contestaron'] as $p)
                <div style="display:flex;align-items:center;gap:0.75rem;background:#fffbeb;border:1px solid #fde68a;border-radius:0.75rem;padding:0.85rem 1rem;margin-bottom:0.5rem;">
                    <div style="flex:1;min-width:0;">
                        <div style="font-weight:600;">{{ $p->name }}</div>
                        <div style="font-size:0.78rem;color:#6b7280;">
                            {{ collect([$p->specialty, $p->city])->filter()->implode(' · ') }}
                            · contestó {{ $p->replied_at?->diffForHumans() }}
                        </div>
                    </div>
                    <a href="{{ $this->ligaWhatsApp($p) }}" target="_blank" rel="noopener" wire:click="registrarEnvio({{ $p->id }})"
                       style="flex:none;background:#fff;border:1px solid #d1d5db;color:#374151;font-weight:600;font-size:0.82rem;padding:0.5rem 0.9rem;border-radius:0.6rem;text-decoration:none;">Abrir chat</a>
                    <a href="{{ $this->ligaParaPedirLaCita($p) }}" target="_blank" rel="noopener" wire:click="registrarEnvio({{ $p->id }})"
                       style="flex:none;background:#25d366;color:#05330f;font-weight:700;font-size:0.82rem;padding:0.5rem 0.9rem;border-radius:0.6rem;text-decoration:none;">Pedir la cita</a>
                </div>
            @endforeach
        </div>
    @endif

    {{-- 2. Seguimientos que ya vencen. --}}
    <div style="margin-bottom:1.75rem;">
        <h2 style="font-size:1rem;font-weight:700;margin-bottom:0.25rem;">Seguimiento de hoy</h2>
        <p style="font-size:0.8rem;color:#6b7280;margin-bottom:0.75rem;">Ya les escribiste antes y les toca el siguiente mensaje.</p>

        @forelse($datos['seguimientos'] as $p)
            <div style="display:flex;align-items:center;gap:0.75rem;background:#fff;border:1px solid #e5e7eb;border-radius:0.75rem;padding:0.85rem 1rem;margin-bottom:0.5rem;">
                <div style="flex:1;min-width:0;">
                    <div style="font-weight:600;">{{ $p->name }}</div>
                    <div style="font-size:0.78rem;color:#6b7280;">
                        Día {{ $p->contact_day }} de la cadencia · le tocaba {{ $p->next_contact_at?->format('d/m') }}
                    </div>
                </div>
                <a href="{{ $this->ligaWhatsApp($p) }}" target="_blank" rel="noopener" wire:click="registrarEnvio({{ $p->id }})"
                   style="flex:none;background:#25d366;color:#05330f;font-weight:700;font-size:0.82rem;padding:0.5rem 0.9rem;border-radius:0.6rem;text-decoration:none;">Abrir chat</a>
            </div>
        @empty
            <p style="font-size:0.85rem;color:#6b7280;">Nada pendiente. Ve directo al primer contacto.</p>
        @endforelse
    </div>

    {{-- 3. Primeros contactos, solo verificados. --}}
    <div>
        <h2 style="font-size:1rem;font-weight:700;margin-bottom:0.25rem;">Primer contacto · hasta {{ $numeros['tope'] }} al día</h2>
        <p style="font-size:0.8rem;color:#6b7280;margin-bottom:0.75rem;">
            Todos con número verificado en WhatsApp. Mándalos después del mediodía, uno cada 8 o 10 minutos.
        </p>

        @forelse($datos['primerContacto'] as $p)
            @php $fuente = $this->fuente($p); @endphp
            <div style="display:flex;align-items:center;gap:0.75rem;background:#fff;border:1px solid #e5e7eb;border-radius:0.75rem;padding:0.85rem 1rem;margin-bottom:0.5rem;">
                <div style="flex:1;min-width:0;">
                    <div style="font-weight:600;">{{ $p->name }}</div>
                    <div style="font-size:0.78rem;color:#6b7280;">
                        {{ collect([$p->specialty, $p->clinic_name, $p->city])->filter()->implode(' · ') }}
                    </div>
                    @if($fuente)
                        <div style="font-size:0.72rem;color:#9ca3af;margin-top:1px;">
                            Su número lo publicó en
                            <a href="{{ $fuente['url'] }}" target="_blank" rel="noopener" style="color:inherit;text-decoration:underline;">{{ $fuente['donde'] }}</a>
                        </div>
                    @endif
                </div>
                <a href="{{ $this->ligaWhatsApp($p) }}" target="_blank" rel="noopener" wire:click="registrarEnvio({{ $p->id }})"
                   style="flex:none;background:#25d366;color:#05330f;font-weight:700;font-size:0.82rem;padding:0.5rem 0.9rem;border-radius:0.6rem;text-decoration:none;">Abrir chat</a>
            </div>
        @empty
            <div style="background:#f9fafb;border:1px dashed #d1d5db;border-radius:0.75rem;padding:1rem;font-size:0.85rem;color:#6b7280;">
                No quedan números verificados sin contactar. Hay que verificar otra tanda antes de seguir:
                un número de directorio sin verificar tiene 9 de cada 10 de no existir.
            </div>
        @endforelse
    </div>

    {{-- 4. Los que hay que verificar. Es trabajo de bodega, no de venta: abrir
         el chat no manda nada, solo enseña si el número existe. --}}
    @if($datos['porVerificar']->isNotEmpty())
        <div style="margin-top:1.75rem;">
            <h2 style="font-size:1rem;font-weight:700;margin-bottom:0.25rem;">Por verificar · {{ $datos['porVerificar']->count() }}</h2>
            <p style="font-size:0.8rem;color:#6b7280;margin-bottom:0.75rem;">
                Abre el chat, lee el mensaje que le tocaría y marca si el número existe. WhatsApp no manda nada solo:
                el texto se queda en el cuadro hasta que tú le des enviar. Los que existan entran mañana a primer
                contacto; los que no, se cierran y no vuelven a salir.
            </p>

            @foreach($datos['porVerificar'] as $p)
                <div style="display:flex;align-items:center;gap:0.5rem;background:#fff;border:1px solid #e5e7eb;border-radius:0.75rem;padding:0.7rem 1rem;margin-bottom:0.4rem;">
                    <div style="flex:1;min-width:0;">
                        <div style="font-weight:600;font-size:0.92rem;">{{ $p->name }}</div>
                        <div style="font-size:0.75rem;color:#6b7280;">{{ collect([$p->city, $p->phone])->filter()->implode(' · ') }}</div>
                    </div>
                    <a href="{{ $this->ligaParaVerificar($p) }}" target="_blank" rel="noopener"
                       style="flex:none;background:#fff;border:1px solid #d1d5db;color:#374151;font-weight:600;font-size:0.78rem;padding:0.4rem 0.75rem;border-radius:0.5rem;text-decoration:none;">Abrir chat</a>
                    <button type="button" wire:click="marcarVerificado({{ $p->id }}, true)"
                            style="flex:none;background:#dcfce7;border:1px solid #86efac;color:#166534;font-weight:700;font-size:0.78rem;padding:0.4rem 0.75rem;border-radius:0.5rem;">Sí existe</button>
                    <button type="button" wire:click="marcarVerificado({{ $p->id }}, false)"
                            style="flex:none;background:#fef2f2;border:1px solid #fecaca;color:#991b1b;font-weight:700;font-size:0.78rem;padding:0.4rem 0.75rem;border-radius:0.5rem;">No existe</button>
                    <button type="button" wire:click="abrirReporte({{ $p->id }})" title="Reportar algo de este prospecto o de su mensaje"
                            style="flex:none;background:#fff;border:1px solid #d1d5db;color:#6b7280;font-size:0.78rem;padding:0.4rem 0.6rem;border-radius:0.5rem;">Reportar</button>
                </div>

                @if($reportando === $p->id)
                    <div style="background:#fffbeb;border:1px solid #fde68a;border-radius:0.75rem;padding:0.85rem 1rem;margin:-0.2rem 0 0.6rem;">
                        <div style="font-size:0.8rem;font-weight:600;margin-bottom:0.4rem;">¿Qué salió mal con {{ $p->name }}?</div>
                        <textarea wire:model="reporte" rows="2" placeholder="Ej: el mensaje le dice doctora y es hombre; la especialidad no es esa; el nombre está mal escrito"
                                  style="width:100%;padding:0.5rem 0.65rem;border:1px solid #d1d5db;border-radius:0.5rem;font-size:0.85rem;"></textarea>
                        <div style="display:flex;gap:0.5rem;margin-top:0.5rem;">
                            <button type="button" wire:click="guardarReporte"
                                    style="background:#0f766e;color:#fff;font-weight:600;font-size:0.8rem;padding:0.4rem 0.9rem;border-radius:0.5rem;">Guardar</button>
                            <button type="button" wire:click="cancelarReporte"
                                    style="background:#fff;border:1px solid #d1d5db;color:#6b7280;font-size:0.8rem;padding:0.4rem 0.9rem;border-radius:0.5rem;">Cancelar</button>
                        </div>
                    </div>
                @endif
            @endforeach
        </div>
    @endif

</x-filament-panels::page>
