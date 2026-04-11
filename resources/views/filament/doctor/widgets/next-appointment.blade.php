<x-filament-widgets::widget>
    @php $next = $this->getNextAppointment(); @endphp
    @if($next)
    <div style="position:relative;border-radius:1.5rem;padding:1.75rem 2rem;overflow:hidden;background:linear-gradient(135deg,#0d9488 0%,#0891b2 50%,#7c3aed 100%);color:white;box-shadow:0 20px 60px -15px rgba(13,148,136,0.5),inset 0 1px 0 rgba(255,255,255,0.2);">

        {{-- Animated blob decoration --}}
        <div style="position:absolute;top:-60px;right:-60px;width:220px;height:220px;background:radial-gradient(circle,rgba(255,255,255,0.15),transparent 70%);border-radius:50%;pointer-events:none;"></div>
        <div style="position:absolute;bottom:-80px;left:-40px;width:180px;height:180px;background:radial-gradient(circle,rgba(139,92,246,0.25),transparent 70%);border-radius:50%;pointer-events:none;"></div>

        {{-- Glass grain overlay --}}
        <div style="position:absolute;inset:0;background-image:radial-gradient(circle at 1px 1px,rgba(255,255,255,0.08) 1px,transparent 0);background-size:20px 20px;pointer-events:none;"></div>

        <div style="position:relative;z-index:1;">
            <div style="display:flex;flex-direction:column;gap:1rem;">
                <div style="display:flex;align-items:center;gap:1rem;">
                    <div style="width:56px;height:56px;border-radius:18px;background:rgba(255,255,255,0.2);backdrop-filter:blur(10px);border:1.5px solid rgba(255,255,255,0.3);display:flex;align-items:center;justify-content:center;flex-shrink:0;box-shadow:0 8px 24px rgba(0,0,0,0.15);">
                        <span style="font-size:1.4rem;font-weight:800;letter-spacing:-0.02em;">{{ substr($next->patient->first_name, 0, 1) }}{{ substr($next->patient->last_name, 0, 1) }}</span>
                    </div>
                    <div style="min-width:0;flex:1;">
                        <div style="font-size:0.65rem;opacity:0.75;text-transform:uppercase;letter-spacing:0.15em;font-weight:700;margin-bottom:2px;">⏭ Siguiente paciente</div>
                        <div style="font-size:1.35rem;font-weight:800;letter-spacing:-0.015em;line-height:1.2;">{{ $next->patient->first_name }} {{ $next->patient->last_name }}</div>
                        <div style="display:flex;flex-wrap:wrap;gap:0.5rem 1rem;font-size:0.75rem;opacity:0.85;margin-top:6px;">
                            <span style="display:inline-flex;align-items:center;gap:4px;">
                                <svg style="width:12px;height:12px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                {{ $next->starts_at->format('H:i') }} hrs
                            </span>
                            @if($next->service)
                            <span style="display:inline-flex;align-items:center;gap:4px;">
                                <svg style="width:12px;height:12px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                                {{ $next->service->name }}
                            </span>
                            @endif
                            @if($next->doctor?->user)
                            <span style="opacity:0.7;">Dr. {{ $next->doctor->user->name }}</span>
                            @endif
                        </div>
                    </div>
                </div>

                <div style="display:flex;flex-wrap:wrap;gap:0.5rem;">
                    <a href="{{ route('filament.doctor.pages.consulta', ['appointment' => $next->id]) }}"
                        style="display:inline-flex;align-items:center;gap:6px;padding:12px 20px;background:white;color:#0f766e;border-radius:14px;font-weight:800;font-size:0.85rem;text-decoration:none;box-shadow:0 8px 24px rgba(0,0,0,0.12);transition:transform 0.2s;"
                        onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='translateY(0)'">
                        <svg style="width:16px;height:16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/></svg>
                        Iniciar consulta
                    </a>
                    @if($next->patient->phone)
                    <a href="https://wa.me/52{{ preg_replace('/\D/', '', $next->patient->phone) }}" target="_blank"
                        style="display:inline-flex;align-items:center;gap:6px;padding:12px 18px;background:rgba(255,255,255,0.15);backdrop-filter:blur(10px);color:white;border:1px solid rgba(255,255,255,0.25);border-radius:14px;font-weight:600;font-size:0.85rem;text-decoration:none;transition:all 0.2s;"
                        onmouseover="this.style.background='rgba(255,255,255,0.25)'" onmouseout="this.style.background='rgba(255,255,255,0.15)'">
                        <svg style="width:14px;height:14px;" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"/></svg>
                        WhatsApp
                    </a>
                    @endif
                    <a href="{{ route('filament.doctor.pages.perfil-paciente', ['patient' => $next->patient->id]) }}"
                        style="display:inline-flex;align-items:center;gap:6px;padding:12px 18px;background:rgba(255,255,255,0.15);backdrop-filter:blur(10px);color:white;border:1px solid rgba(255,255,255,0.25);border-radius:14px;font-weight:600;font-size:0.85rem;text-decoration:none;transition:all 0.2s;"
                        onmouseover="this.style.background='rgba(255,255,255,0.25)'" onmouseout="this.style.background='rgba(255,255,255,0.15)'">
                        Ver perfil
                    </a>
                </div>
            </div>
        </div>
    </div>
    @endif
</x-filament-widgets::widget>
