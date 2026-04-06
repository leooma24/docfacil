<x-filament-widgets::widget>
    <div class="flex flex-wrap gap-3">
        <a href="{{ route('filament.doctor.resources.appointments.create') }}"
            class="flex-1 min-w-[140px] flex items-center gap-3 px-4 py-3 bg-teal-50 dark:bg-teal-950 border border-teal-200 dark:border-teal-800 rounded-xl hover:bg-teal-100 dark:hover:bg-teal-900 transition group hover:-translate-y-0.5 hover:shadow-md">
            <div class="w-9 h-9 bg-teal-500 rounded-lg flex items-center justify-center group-hover:scale-110 transition flex-shrink-0">
                <x-filament::icon icon="heroicon-o-calendar-days" class="w-5 h-5 text-white" />
            </div>
            <span class="text-sm font-bold text-teal-900 dark:text-teal-100">Nueva cita</span>
        </a>
        <a href="{{ route('filament.doctor.resources.patients.create') }}"
            class="flex-1 min-w-[140px] flex items-center gap-3 px-4 py-3 bg-blue-50 dark:bg-blue-950 border border-blue-200 dark:border-blue-800 rounded-xl hover:bg-blue-100 dark:hover:bg-blue-900 transition group hover:-translate-y-0.5 hover:shadow-md">
            <div class="w-9 h-9 bg-blue-500 rounded-lg flex items-center justify-center group-hover:scale-110 transition flex-shrink-0">
                <x-filament::icon icon="heroicon-o-user-plus" class="w-5 h-5 text-white" />
            </div>
            <span class="text-sm font-bold text-blue-900 dark:text-blue-100">Nuevo paciente</span>
        </a>
        <a href="{{ route('filament.doctor.resources.payments.create') }}"
            class="flex-1 min-w-[140px] flex items-center gap-3 px-4 py-3 bg-emerald-50 dark:bg-emerald-950 border border-emerald-200 dark:border-emerald-800 rounded-xl hover:bg-emerald-100 dark:hover:bg-emerald-900 transition group hover:-translate-y-0.5 hover:shadow-md">
            <div class="w-9 h-9 bg-emerald-500 rounded-lg flex items-center justify-center group-hover:scale-110 transition flex-shrink-0">
                <x-filament::icon icon="heroicon-o-banknotes" class="w-5 h-5 text-white" />
            </div>
            <span class="text-sm font-bold text-emerald-900 dark:text-emerald-100">Registrar cobro</span>
        </a>
        <a href="{{ route('filament.doctor.resources.prescriptions.create') }}"
            class="flex-1 min-w-[140px] flex items-center gap-3 px-4 py-3 bg-purple-50 dark:bg-purple-950 border border-purple-200 dark:border-purple-800 rounded-xl hover:bg-purple-100 dark:hover:bg-purple-900 transition group hover:-translate-y-0.5 hover:shadow-md">
            <div class="w-9 h-9 bg-purple-500 rounded-lg flex items-center justify-center group-hover:scale-110 transition flex-shrink-0">
                <x-filament::icon icon="heroicon-o-document-text" class="w-5 h-5 text-white" />
            </div>
            <span class="text-sm font-bold text-purple-900 dark:text-purple-100">Nueva receta</span>
        </a>
    </div>
</x-filament-widgets::widget>
