<x-filament-widgets::widget>
    @php $next = $this->getNextAppointment(); @endphp
    @if($next)
    <div class="rounded-2xl p-4 md:p-6 text-white" style="background:linear-gradient(135deg,#0d9488,#0891b2);">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex items-center gap-3 md:gap-4">
                <div class="w-12 h-12 md:w-14 md:h-14 bg-white/20 rounded-full flex items-center justify-center shrink-0">
                    <span class="text-base md:text-xl font-extrabold">{{ substr($next->patient->first_name, 0, 1) }}{{ substr($next->patient->last_name, 0, 1) }}</span>
                </div>
                <div class="min-w-0">
                    <div class="text-[10px] md:text-xs opacity-80 uppercase tracking-wide font-semibold">Siguiente paciente</div>
                    <div class="text-lg md:text-xl font-extrabold truncate">{{ $next->patient->first_name }} {{ $next->patient->last_name }}</div>
                    <div class="flex flex-wrap items-center gap-x-3 gap-y-0.5 text-xs md:text-sm opacity-90 mt-0.5">
                        <span>{{ $next->starts_at->format('H:i') }} hrs</span>
                        @if($next->service)
                        <span>{{ $next->service->name }}</span>
                        @endif
                        <span>{{ $next->doctor->user->name ?? '' }}</span>
                    </div>
                </div>
            </div>
            <div class="flex flex-wrap gap-2 pl-15 sm:pl-0">
                <a href="{{ route('filament.doctor.pages.consultation', ['appointment' => $next->id]) }}"
                    class="inline-flex items-center justify-center gap-2 px-4 py-2.5 md:py-2.5 rounded-xl font-bold text-sm no-underline transition" style="background:white;color:#0f766e;">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/></svg>
                    Iniciar consulta
                </a>
                @if($next->patient->phone)
                <a href="https://wa.me/52{{ preg_replace('/\D/', '', $next->patient->phone) }}" target="_blank"
                    class="inline-flex items-center justify-center gap-2 px-4 py-2.5 md:py-2.5 text-white rounded-xl font-semibold text-sm no-underline transition" style="background:rgba(255,255,255,0.2);"
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"/></svg>
                    WhatsApp
                </a>
                @endif
                <a href="{{ route('filament.doctor.pages.patient-profile', ['patient' => $next->patient->id]) }}"
                    class="inline-flex items-center justify-center gap-2 px-4 py-2.5 md:py-2.5 text-white rounded-xl font-semibold text-sm no-underline transition" style="background:rgba(255,255,255,0.2);"
                    Ver perfil
                </a>
            </div>
        </div>
    </div>
    @endif
</x-filament-widgets::widget>
