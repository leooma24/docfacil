<x-filament-widgets::widget>
    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
        <a href="{{ route('filament.doctor.resources.appointments.create') }}"
            class="flex items-center gap-3 p-4 bg-teal-50 dark:bg-teal-950 border border-teal-200 dark:border-teal-800 rounded-xl hover:bg-teal-100 dark:hover:bg-teal-900 transition group">
            <div class="w-10 h-10 bg-teal-500 rounded-lg flex items-center justify-center group-hover:scale-110 transition">
                <x-filament::icon icon="heroicon-o-calendar-days" class="w-5 h-5 text-white" />
            </div>
            <div>
                <div class="text-sm font-bold text-teal-900 dark:text-teal-100">Nueva cita</div>
                <div class="text-xs text-teal-600 dark:text-teal-400">Agendar paciente</div>
            </div>
        </a>
        <a href="{{ route('filament.doctor.resources.patients.create') }}"
            class="flex items-center gap-3 p-4 bg-blue-50 dark:bg-blue-950 border border-blue-200 dark:border-blue-800 rounded-xl hover:bg-blue-100 dark:hover:bg-blue-900 transition group">
            <div class="w-10 h-10 bg-blue-500 rounded-lg flex items-center justify-center group-hover:scale-110 transition">
                <x-filament::icon icon="heroicon-o-user-plus" class="w-5 h-5 text-white" />
            </div>
            <div>
                <div class="text-sm font-bold text-blue-900 dark:text-blue-100">Nuevo paciente</div>
                <div class="text-xs text-blue-600 dark:text-blue-400">Registrar datos</div>
            </div>
        </a>
        <a href="{{ route('filament.doctor.resources.payments.create') }}"
            class="flex items-center gap-3 p-4 bg-emerald-50 dark:bg-emerald-950 border border-emerald-200 dark:border-emerald-800 rounded-xl hover:bg-emerald-100 dark:hover:bg-emerald-900 transition group">
            <div class="w-10 h-10 bg-emerald-500 rounded-lg flex items-center justify-center group-hover:scale-110 transition">
                <x-filament::icon icon="heroicon-o-banknotes" class="w-5 h-5 text-white" />
            </div>
            <div>
                <div class="text-sm font-bold text-emerald-900 dark:text-emerald-100">Registrar cobro</div>
                <div class="text-xs text-emerald-600 dark:text-emerald-400">Pago rapido</div>
            </div>
        </a>
        <a href="{{ route('filament.doctor.resources.prescriptions.create') }}"
            class="flex items-center gap-3 p-4 bg-purple-50 dark:bg-purple-950 border border-purple-200 dark:border-purple-800 rounded-xl hover:bg-purple-100 dark:hover:bg-purple-900 transition group">
            <div class="w-10 h-10 bg-purple-500 rounded-lg flex items-center justify-center group-hover:scale-110 transition">
                <x-filament::icon icon="heroicon-o-document-text" class="w-5 h-5 text-white" />
            </div>
            <div>
                <div class="text-sm font-bold text-purple-900 dark:text-purple-100">Nueva receta</div>
                <div class="text-xs text-purple-600 dark:text-purple-400">Generar PDF</div>
            </div>
        </a>
    </div>
</x-filament-widgets::widget>
