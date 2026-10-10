<x-filament-panels::page>
    <div style="max-width: 46rem; display: grid; gap: 1.25rem;">
        <x-filament::section>
            <x-slot name="heading">Bájelos cuando quiera</x-slot>

            <p style="margin-bottom: .75rem;">
                Un archivo ZIP con todo lo de su consultorio ({{ number_format($pacientes) }} {{ $pacientes === 1 ? 'paciente' : 'pacientes' }}):
            </p>
            <ul style="list-style: disc; padding-left: 1.25rem; display: grid; gap: .25rem; margin-bottom: 1rem;">
                <li>Pacientes con sus alergias y antecedentes, citas, notas de consulta, recetas y odontogramas.</li>
                <li>Presupuestos, cobros, abonos, planes de pago, gastos y laboratorio.</li>
                <li>Las fotos, radiografías y documentos de cada paciente, en su carpeta.</li>
            </ul>
            <p style="margin-bottom: 1rem; font-size: .875rem; opacity: .8;">
                Las hojas se abren en Excel. Si tiene muchas fotos, tarda un poco en bajar.
            </p>

            <x-filament::button tag="a" :href="$liga" icon="heroicon-o-arrow-down-tray">
                Bajar todos mis datos
            </x-filament::button>
        </x-filament::section>

        <x-filament::section>
            <x-slot name="heading">Si deja de pagar</x-slot>

            <p>
                Nada se borra. Su cuenta pasa al plan Gratis: sigue con su agenda y el expediente de sus pacientes,
                con tope de 15 pacientes y 10 citas al mes (si ya tiene más pacientes, no pierde ninguno: solo no puede agregar).
                Lo de los planes de pago, como presupuestos o laboratorio, se queda guardado y se vuelve a abrir cuando renueva.
                Y puede bajar todos sus datos aquí cuando quiera, pague o no.
            </p>
            <p style="margin-top: .5rem;">Y el expediente de un paciente que ya tuvo consulta no se borra ni por error: la NOM-004 pide conservarlo al menos 5 años.</p>
        </x-filament::section>
    </div>
</x-filament-panels::page>
