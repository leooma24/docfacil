{{-- Abajo del menú: el plan, y cuánto le queda de prueba. --}}
@php
    $clinica = auth()->user()?->clinic;
    $enPrueba = $clinica?->enPruebaVigente();
    $dias = $enPrueba ? (int) today()->diffInDays($clinica->trial_ends_at->copy()->startOfDay()) : null;
@endphp
@if($clinica)
<a href="{{ \App\Filament\Doctor\Pages\Upgrade::getUrl(panel: 'doctor') }}" class="docfacil-menu-plan"
   style="display:block;margin:0.5rem 0.75rem 0.9rem;padding:0.65rem 0.8rem;border-radius:0.7rem;background:{{ $enPrueba ? 'rgba(251,191,36,0.14)' : 'rgba(255,255,255,0.06)' }};border:1px solid {{ $enPrueba ? 'rgba(251,191,36,0.35)' : 'rgba(255,255,255,0.08)' }};">
    @if($enPrueba)
        <div style="font-size:0.82rem;font-weight:700;color:#fde68a;">Prueba: quedan {{ $dias }} {{ $dias === 1 ? 'día' : 'días' }}</div>
        <div style="font-size:0.72rem;color:rgba(255,255,255,0.7);margin-top:2px;">Tiene todo desbloqueado · Ver planes</div>
    @else
        <div style="font-size:0.82rem;font-weight:700;color:#ffffff;">Plan {{ \App\Models\Clinic::displayNameForPlan($clinica->plan) }}</div>
        <div style="font-size:0.72rem;color:rgba(255,255,255,0.6);margin-top:2px;">Ver mi plan</div>
    @endif
</a>
@endif
