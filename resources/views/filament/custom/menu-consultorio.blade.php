{{-- Arriba del menú: el consultorio y la acción principal del momento. --}}
@php
    $usuario = auth()->user();
    $clinica = $usuario?->clinic;
    $sobran = ['clinica', 'clínica', 'dental', 'consultorio', 'odontologia', 'odontología', 'de', 'la', 'el', 'del', 'y'];
    $palabras = collect(preg_split('/\s+/', (string) $clinica?->name))->filter()
        ->reject(fn ($p) => in_array(mb_strtolower($p), $sobran, true))->values();
    $iniciales = mb_strtoupper(mb_substr($palabras[0] ?? ($clinica?->name ?? 'D'), 0, 1) . mb_substr($palabras[1] ?? '', 0, 1));

    // El botón principal sabe qué sigue: la consulta abierta, quien ya está
    // en la sala de espera, el siguiente paciente de hoy (con 15 minutos de
    // tolerancia), o una consulta nueva.
    $abierta = $clinica ? \App\Models\Appointment::where('clinic_id', $clinica->id)->where('status', 'in_progress')
        ->with('patient')->latest('starts_at')->first() : null;
    $enSala = ($clinica && ! $abierta) ? \App\Models\Appointment::where('clinic_id', $clinica->id)
        ->whereIn('status', ['scheduled', 'confirmed'])
        ->whereNotNull('arrived_at')
        ->whereBetween('starts_at', [today()->startOfDay(), today()->endOfDay()])
        ->with('patient')->orderBy('starts_at')->first() : null;
    $siguiente = ($clinica && ! $abierta) ? ($enSala ?? \App\Models\Appointment::where('clinic_id', $clinica->id)
        ->whereIn('status', ['scheduled', 'confirmed'])
        ->whereBetween('starts_at', [now()->subMinutes(15), today()->endOfDay()])
        ->with('patient')->orderBy('starts_at')->first()) : null;
    $cita = $abierta ?? $siguiente;
    $urlConsulta = \App\Filament\Doctor\Pages\Consultation::getUrl($cita ? ['appointment' => $cita->id] : [], panel: 'doctor');
@endphp
@if($clinica)
<div class="dfm-top">
    <div class="dfm-clinica">
        {{-- Si el logo no carga, quedan las iniciales. --}}
        <span class="dfm-avatar">{{ $iniciales }}</span>
        @if($clinica->logo)
            <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($clinica->logo) }}" alt="" class="dfm-avatar dfm-avatar-img"
                 onload="this.previousElementSibling.remove()" onerror="this.remove()">
        @endif
        <span class="dfm-clinica-txt">
            <span class="dfm-clinica-nombre">{{ $clinica->name }}</span>
            <span class="dfm-clinica-sub">{{ $usuario->name }}</span>
        </span>
    </div>

    <a href="{{ $urlConsulta }}" class="dfm-cta">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polygon points="6 4 20 12 6 20 6 4"/></svg>
        <span class="dfm-cta-txt">
            @if($abierta)
                <span class="dfm-cta-t">Continuar con {{ $abierta->patient?->first_name }}</span>
                <span class="dfm-cta-s">Consulta en curso</span>
            @elseif($siguiente)
                <span class="dfm-cta-t">Atender a {{ $siguiente->patient?->first_name }}</span>
                @if($siguiente->arrived_at)
                <span class="dfm-cta-s">Ya llegó · cita {{ $siguiente->starts_at->format('H:i') }}</span>
                @else
                <span class="dfm-cta-s">{{ $siguiente->starts_at->format('H:i') }} · {{ \Illuminate\Support\Str::limit($siguiente->service?->name ?? 'Consulta', 22) }}</span>
                @endif
            @else
                <span class="dfm-cta-t">Nueva consulta</span>
                <span class="dfm-cta-s">Sin pacientes pendientes hoy</span>
            @endif
        </span>
    </a>
</div>
@endif
