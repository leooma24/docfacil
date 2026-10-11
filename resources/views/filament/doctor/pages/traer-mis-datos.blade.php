<x-filament-panels::page>
    <p style="font-size:0.95rem;color:#475569;margin-bottom:1rem;max-width:46rem;">
        ¿Viene de otro sistema, de Excel o de su libreta? Suba lo que ya tiene en este orden: primero sus pacientes, luego sus citas y lo que le deben. Las citas y los saldos reconocen a los pacientes que ya subió por su teléfono o su nombre.
    </p>

    @php
        $pasos = [
            ['n' => 1, 'titulo' => 'Sus pacientes', 'texto' => 'Nombre, teléfono y, si los tiene, correo, fecha de nacimiento, CURP y alergias.', 'hecho' => $pacientes > 0 ? "Tiene {$pacientes} " . ($pacientes === 1 ? 'paciente' : 'pacientes') . '.' : null, 'accion' => $this->importarPacientesAction],
            ['n' => 2, 'titulo' => 'Sus citas que vienen', 'texto' => 'Paciente, fecha y hora. Si quiere, duración, motivo y doctor.', 'hecho' => $citas > 0 ? "Tiene {$citas} " . ($citas === 1 ? 'cita' : 'citas') . ' por venir.' : null, 'accion' => $this->importarCitasAction],
            ['n' => 3, 'titulo' => 'Lo que le deben', 'texto' => 'Paciente y cuánto debe. Si quiere, el concepto y desde cuándo.', 'hecho' => $porCobrar > 0 ? 'Le deben $' . number_format($porCobrar, 0) . '.' : null, 'accion' => $this->importarSaldosAction],
        ];
    @endphp

    <div style="display:grid;gap:0.75rem;max-width:46rem;">
        @foreach($pasos as $paso)
            <div style="display:flex;gap:1rem;align-items:center;flex-wrap:wrap;background:#fff;border:1px solid #e5e7eb;border-radius:12px;padding:1rem 1.1rem;">
                <div style="flex:none;width:2.2rem;height:2.2rem;border-radius:999px;background:#ccfbf1;color:#0f766e;font-weight:800;display:flex;align-items:center;justify-content:center;">{{ $paso['n'] }}</div>
                <div style="flex:1;min-width:200px;">
                    <div style="font-weight:800;color:#0f172a;">{{ $paso['titulo'] }}</div>
                    <div style="font-size:0.85rem;color:#64748b;">{{ $paso['texto'] }}</div>
                    @if($paso['hecho'])<div style="font-size:0.8rem;color:#047857;font-weight:700;margin-top:0.2rem;">{{ $paso['hecho'] }}</div>@endif
                </div>
                <div style="flex:none;">{{ $paso['accion'] }}</div>
            </div>
        @endforeach
    </div>

    <p style="font-size:0.85rem;color:#64748b;margin-top:1rem;max-width:46rem;">
        Al terminar le avisamos cuántos entraron. Si algún renglón no entró, le damos un archivo con cuáles y por qué, para corregirlos y volver a subirlos.
    </p>

    <x-filament-actions::modals />
</x-filament-panels::page>
