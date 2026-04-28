<x-filament-widgets::widget>
    <div class="rounded-2xl border-2 border-fuchsia-200 bg-gradient-to-br from-fuchsia-50 via-pink-50 to-white p-5 md:p-6 shadow-sm">
        <div class="flex flex-col md:flex-row md:items-center gap-4 md:gap-6">
            {{-- Icono --}}
            <div class="flex-shrink-0">
                <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-fuchsia-500 to-pink-500 flex items-center justify-center shadow-md">
                    <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                    </svg>
                </div>
            </div>

            {{-- Mensaje --}}
            <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2 flex-wrap">
                    <span class="text-[11px] font-bold tracking-wider text-fuchsia-700 uppercase">
                        Tu plan permite varios doctores
                    </span>
                    @if($isUnlimited)
                        <span class="text-xs px-2 py-0.5 rounded-full bg-fuchsia-100 text-fuchsia-700 font-semibold">Plan Clínica · ilimitado</span>
                    @else
                        <span class="text-xs px-2 py-0.5 rounded-full bg-fuchsia-100 text-fuchsia-700 font-semibold">{{ $current + $pending }}/{{ $max }} usados</span>
                    @endif
                </div>

                <h3 class="mt-1 text-lg md:text-xl font-extrabold text-gray-900">
                    @if($isUnlimited)
                        Suma a tu equipo dental
                    @elseif($slotsLeft === 1)
                        Te queda 1 doctor por invitar
                    @else
                        Te quedan {{ $slotsLeft }} doctores por invitar
                    @endif
                </h3>

                <p class="mt-1 text-sm text-gray-600">
                    @if($current > 0 || $pending > 0)
                        Ya tienes {{ $current }} {{ $current === 1 ? 'doctor activo' : 'doctores activos' }}@if($pending > 0) y {{ $pending }} invitación{{ $pending === 1 ? '' : 'es' }} pendiente{{ $pending === 1 ? '' : 's' }}@endif. Cada doctor tiene su propia agenda y reportes — todo dentro de la misma clínica.
                    @else
                        Invita a tu socio o socia. Cada doctor maneja su agenda, sus pacientes y sus reportes — todo dentro de la misma clínica.
                    @endif
                </p>
            </div>

            {{-- CTAs --}}
            <div class="flex flex-col sm:flex-row gap-2 flex-shrink-0">
                @if($pending > 0)
                <a href="{{ $manageUrl }}"
                    class="inline-flex items-center justify-center gap-1.5 px-4 py-2.5 bg-white border-2 border-fuchsia-200 text-fuchsia-700 hover:bg-fuchsia-50 text-sm font-semibold rounded-xl transition">
                    Ver pendientes ({{ $pending }})
                </a>
                @endif
                <a href="{{ $inviteUrl }}"
                    class="inline-flex items-center justify-center gap-1.5 px-4 py-2.5 bg-gradient-to-r from-fuchsia-600 to-pink-600 text-white hover:shadow-lg hover:shadow-fuchsia-200 text-sm font-bold rounded-xl transition hover:-translate-y-0.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                    </svg>
                    Invitar doctor
                </a>
            </div>
        </div>
    </div>
</x-filament-widgets::widget>
