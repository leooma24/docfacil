<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            Producción por doctor — {{ ucfirst($month) }}
        </x-slot>
        <x-slot name="description">
            Citas e ingresos del mes en curso por cada doctor activo.
        </x-slot>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-200 dark:border-gray-700">
                        <th class="py-2 px-3 text-left font-semibold text-gray-600 dark:text-gray-300">Doctor</th>
                        <th class="py-2 px-3 text-right font-semibold text-gray-600 dark:text-gray-300">Citas</th>
                        <th class="py-2 px-3 text-right font-semibold text-gray-600 dark:text-gray-300">Completadas</th>
                        <th class="py-2 px-3 text-right font-semibold text-gray-600 dark:text-gray-300">Canceladas</th>
                        <th class="py-2 px-3 text-right font-semibold text-gray-600 dark:text-gray-300">No-show</th>
                        <th class="py-2 px-3 text-right font-semibold text-gray-600 dark:text-gray-300">% Asistencia</th>
                        <th class="py-2 px-3 text-right font-semibold text-gray-600 dark:text-gray-300">Ingresos</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($doctors as $d)
                    <tr class="border-b border-gray-100 dark:border-gray-800 hover:bg-gray-50 dark:hover:bg-gray-800/40">
                        <td class="py-3 px-3">
                            <div class="font-medium text-gray-900 dark:text-gray-100">{{ $d['name'] }}</div>
                            <div class="text-xs text-gray-500 dark:text-gray-400">{{ $d['specialty'] }}</div>
                        </td>
                        <td class="py-3 px-3 text-right text-gray-900 dark:text-gray-100">{{ $d['total'] }}</td>
                        <td class="py-3 px-3 text-right text-teal-600 dark:text-teal-400 font-medium">{{ $d['completed'] }}</td>
                        <td class="py-3 px-3 text-right text-amber-600 dark:text-amber-400">{{ $d['cancelled'] }}</td>
                        <td class="py-3 px-3 text-right text-red-600 dark:text-red-400">{{ $d['no_show'] }}</td>
                        <td class="py-3 px-3 text-right">
                            <span @class([
                                'inline-block px-2 py-0.5 rounded text-xs font-semibold',
                                'bg-teal-100 text-teal-700 dark:bg-teal-900/30 dark:text-teal-300' => $d['completion_rate'] >= 80,
                                'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300' => $d['completion_rate'] >= 50 && $d['completion_rate'] < 80,
                                'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-300' => $d['completion_rate'] < 50,
                            ])>{{ $d['completion_rate'] }}%</span>
                        </td>
                        <td class="py-3 px-3 text-right font-bold text-gray-900 dark:text-gray-100">${{ number_format($d['income'], 2) }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-6 text-center text-gray-500 dark:text-gray-400">
                            Aún no hay doctores registrados en el consultorio.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
                @if ($doctors->isNotEmpty())
                <tfoot>
                    <tr class="border-t-2 border-gray-200 dark:border-gray-700 font-bold">
                        <td class="py-3 px-3 text-gray-900 dark:text-gray-100">Total consultorio</td>
                        <td class="py-3 px-3 text-right text-gray-900 dark:text-gray-100">{{ $total_appointments }}</td>
                        <td class="py-3 px-3"></td>
                        <td class="py-3 px-3"></td>
                        <td class="py-3 px-3"></td>
                        <td class="py-3 px-3"></td>
                        <td class="py-3 px-3 text-right text-gray-900 dark:text-gray-100">${{ number_format($total_income, 2) }}</td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
