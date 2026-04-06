<x-filament-widgets::widget>
    @php $next = $this->getNextAppointment(); @endphp
    @if($next)
    <div style="background:linear-gradient(135deg,#0d9488,#0891b2);border-radius:1rem;padding:1.5rem;color:white;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:1rem;">
        <div style="display:flex;align-items:center;gap:1rem;">
            <div style="width:56px;height:56px;background:rgba(255,255,255,0.2);border-radius:50%;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                <span style="font-size:1.25rem;font-weight:800;">{{ substr($next->patient->first_name, 0, 1) }}{{ substr($next->patient->last_name, 0, 1) }}</span>
            </div>
            <div>
                <div style="font-size:0.75rem;opacity:0.8;text-transform:uppercase;letter-spacing:0.05em;font-weight:600;">Siguiente paciente</div>
                <div style="font-size:1.25rem;font-weight:800;">{{ $next->patient->first_name }} {{ $next->patient->last_name }}</div>
                <div style="font-size:0.875rem;opacity:0.9;display:flex;gap:1rem;margin-top:0.25rem;">
                    <span>{{ $next->starts_at->format('H:i') }} hrs</span>
                    @if($next->service)
                    <span>{{ $next->service->name }}</span>
                    @endif
                    <span>{{ $next->doctor->user->name ?? '' }}</span>
                </div>
            </div>
        </div>
        <div style="display:flex;gap:0.5rem;flex-wrap:wrap;">
            <a href="{{ route('filament.doctor.pages.consultation', ['appointment' => $next->id]) }}"
                style="display:inline-flex;align-items:center;gap:0.5rem;padding:0.625rem 1.25rem;background:white;color:#0d9488;border-radius:0.75rem;font-weight:700;font-size:0.875rem;text-decoration:none;">
                <svg style="width:18px;height:18px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/></svg>
                Iniciar consulta
            </a>
            @if($next->patient->phone)
            <a href="https://wa.me/52{{ preg_replace('/\D/', '', $next->patient->phone) }}" target="_blank"
                style="display:inline-flex;align-items:center;gap:0.5rem;padding:0.625rem 1.25rem;background:rgba(255,255,255,0.2);color:white;border-radius:0.75rem;font-weight:600;font-size:0.875rem;text-decoration:none;">
                <svg style="width:16px;height:16px;" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"/></svg>
                WhatsApp
            </a>
            @endif
            <a href="{{ route('filament.doctor.pages.patient-profile', ['patient' => $next->patient->id]) }}"
                style="display:inline-flex;align-items:center;gap:0.5rem;padding:0.625rem 1.25rem;background:rgba(255,255,255,0.2);color:white;border-radius:0.75rem;font-weight:600;font-size:0.875rem;text-decoration:none;">
                Ver perfil
            </a>
        </div>
    </div>
    @endif
</x-filament-widgets::widget>
