{{-- Abajo del menú: el plan, y cuánto le queda de prueba. --}}
@php
    $clinica = auth()->user()?->clinic;
    $enPrueba = $clinica?->enPruebaVigente();
    $dias = $enPrueba ? (int) today()->diffInDays($clinica->trial_ends_at->copy()->startOfDay()) : null;
    $avance = $enPrueba ? max(4, min(100, round((15 - $dias) / 15 * 100))) : null;
@endphp
@if($clinica)
<a href="{{ \App\Filament\Doctor\Pages\Upgrade::getUrl(panel: 'doctor') }}" class="dfm-plan{{ $enPrueba ? ' dfm-plan-prueba' : '' }}">
    @if($enPrueba)
        <span class="dfm-plan-t">Prueba: quedan {{ $dias }} {{ $dias === 1 ? 'día' : 'días' }}</span>
        <span class="dfm-barra"><span style="width: {{ $avance }}%"></span></span>
        <span class="dfm-plan-s">Todo desbloqueado · Ver planes</span>
    @else
        <span class="dfm-plan-t">Plan {{ \App\Models\Clinic::displayNameForPlan($clinica->plan) }}</span>
        <span class="dfm-plan-s">Ver mi plan</span>
    @endif
</a>
@endif
