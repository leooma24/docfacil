<x-filament-widgets::widget>
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-2 md:gap-3">
        <a href="{{ route('filament.doctor.resources.appointments.create') }}" class="flex items-center gap-2 md:gap-3 p-3 md:p-4 bg-teal-50 dark:bg-teal-950 border border-teal-200 dark:border-teal-800 rounded-xl no-underline hover:bg-teal-100 transition">
            <div class="w-9 h-9 md:w-10 md:h-10 bg-teal-500 rounded-lg flex items-center justify-center shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="white" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
            </div>
            <span class="text-xs md:text-sm font-bold text-teal-900 dark:text-teal-200">Nueva cita</span>
        </a>
        <a href="{{ route('filament.doctor.resources.patients.create') }}" class="flex items-center gap-2 md:gap-3 p-3 md:p-4 bg-blue-50 dark:bg-blue-950 border border-blue-200 dark:border-blue-800 rounded-xl no-underline hover:bg-blue-100 transition">
            <div class="w-9 h-9 md:w-10 md:h-10 bg-blue-500 rounded-lg flex items-center justify-center shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="white" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
            </div>
            <span class="text-xs md:text-sm font-bold text-blue-900 dark:text-blue-200">Nuevo paciente</span>
        </a>
        <a href="{{ route('filament.doctor.resources.payments.create') }}" class="flex items-center gap-2 md:gap-3 p-3 md:p-4 bg-emerald-50 dark:bg-emerald-950 border border-emerald-200 dark:border-emerald-800 rounded-xl no-underline hover:bg-emerald-100 transition">
            <div class="w-9 h-9 md:w-10 md:h-10 bg-emerald-500 rounded-lg flex items-center justify-center shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="white" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2z"/></svg>
            </div>
            <span class="text-xs md:text-sm font-bold text-emerald-900 dark:text-emerald-200">Cobro</span>
        </a>
        <a href="{{ route('filament.doctor.resources.prescriptions.create') }}" class="flex items-center gap-2 md:gap-3 p-3 md:p-4 bg-purple-50 dark:bg-purple-950 border border-purple-200 dark:border-purple-800 rounded-xl no-underline hover:bg-purple-100 transition">
            <div class="w-9 h-9 md:w-10 md:h-10 bg-purple-500 rounded-lg flex items-center justify-center shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="white" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            </div>
            <span class="text-xs md:text-sm font-bold text-purple-900 dark:text-purple-200">Receta</span>
        </a>
        <a href="{{ route('filament.doctor.pages.consultation') }}" class="flex items-center gap-2 md:gap-3 p-3 md:p-4 bg-amber-50 dark:bg-amber-950 border border-amber-200 dark:border-amber-800 rounded-xl no-underline hover:bg-amber-100 transition col-span-2 sm:col-span-1">
            <div class="w-9 h-9 md:w-10 md:h-10 bg-amber-500 rounded-lg flex items-center justify-center shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="white" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <span class="text-xs md:text-sm font-bold text-amber-900 dark:text-amber-200">Consulta rápida</span>
        </a>
    </div>
</x-filament-widgets::widget>
