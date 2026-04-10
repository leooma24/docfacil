<x-filament-widgets::widget>
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-2 md:gap-3">
        <a href="{{ route('filament.doctor.resources.citas.create') }}" class="flex items-center gap-2 md:gap-3 p-3 md:p-4 rounded-xl no-underline transition" style="background:#f0fdfa;border:1px solid #99f6e4;">
            <div class="w-9 h-9 md:w-10 md:h-10 rounded-lg flex items-center justify-center shrink-0" style="background:#14b8a6;">
                <svg class="w-5 h-5" fill="none" stroke="white" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
            </div>
            <span class="text-xs md:text-sm font-bold" style="color:#134e4a;">Nueva cita</span>
        </a>
        <a href="{{ route('filament.doctor.resources.pacientes.create') }}" class="flex items-center gap-2 md:gap-3 p-3 md:p-4 rounded-xl no-underline transition" style="background:#eff6ff;border:1px solid #bfdbfe;">
            <div class="w-9 h-9 md:w-10 md:h-10 rounded-lg flex items-center justify-center shrink-0" style="background:#3b82f6;">
                <svg class="w-5 h-5" fill="none" stroke="white" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
            </div>
            <span class="text-xs md:text-sm font-bold" style="color:#1e3a5f;">Nuevo paciente</span>
        </a>
        <a href="{{ route('filament.doctor.resources.cobros.create') }}" class="flex items-center gap-2 md:gap-3 p-3 md:p-4 rounded-xl no-underline transition" style="background:#ecfdf5;border:1px solid #a7f3d0;">
            <div class="w-9 h-9 md:w-10 md:h-10 rounded-lg flex items-center justify-center shrink-0" style="background:#10b981;">
                <svg class="w-5 h-5" fill="none" stroke="white" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2z"/></svg>
            </div>
            <span class="text-xs md:text-sm font-bold" style="color:#064e3b;">Cobro</span>
        </a>
        <a href="{{ route('filament.doctor.resources.recetas.create') }}" class="flex items-center gap-2 md:gap-3 p-3 md:p-4 rounded-xl no-underline transition" style="background:#faf5ff;border:1px solid #d8b4fe;">
            <div class="w-9 h-9 md:w-10 md:h-10 rounded-lg flex items-center justify-center shrink-0" style="background:#8b5cf6;">
                <svg class="w-5 h-5" fill="none" stroke="white" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            </div>
            <span class="text-xs md:text-sm font-bold" style="color:#4c1d95;">Receta</span>
        </a>
        <a href="{{ route('filament.doctor.pages.consulta') }}" class="flex items-center gap-2 md:gap-3 p-3 md:p-4 rounded-xl no-underline transition col-span-2 sm:col-span-1" style="background:#fef3c7;border:1px solid #fcd34d;">
            <div class="w-9 h-9 md:w-10 md:h-10 rounded-lg flex items-center justify-center shrink-0" style="background:#f59e0b;">
                <svg class="w-5 h-5" fill="none" stroke="white" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <span class="text-xs md:text-sm font-bold" style="color:#92400e;">Consulta rápida</span>
        </a>
    </div>
</x-filament-widgets::widget>
