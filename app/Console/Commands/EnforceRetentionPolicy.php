<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

/**
 * Política de retención de datos — ley de datos personales (art. 10) + NOM-004-SSA3-2012.
 *
 * - Expedientes clínicos: mínimo 5 años desde el ÚLTIMO acto médico del
 *   paciente (NOM-004 5.4; en dental, desde la última consulta, NOM-013 5.14).
 * - Datos fiscales: 5 años (Código Fiscal de la Federación art. 30).
 * - Margen de seguridad: 7 años.
 *
 * Antes contaba por la fecha de cada nota: a un paciente que lleva diez años
 * viniendo se le habrían borrado las notas viejas aunque siguiera en
 * tratamiento. Ahora el expediente de un paciente solo es candidato cuando
 * NADA suyo —nota, receta ni cita— es más reciente que el corte.
 *
 * Por defecto NO borra nada: solo reporta. El borrado requiere --force.
 */
class EnforceRetentionPolicy extends Command
{
    protected $signature = 'app:retention-report {--force : Borra los registros reportados}';

    protected $description = 'Reporta (y opcionalmente borra) registros que superaron el periodo de retención legal';

    private const RETENTION_YEARS = 7;

    public function handle(): int
    {
        $cutoff = now()->subYears(self::RETENTION_YEARS)->toDateString();
        $this->info("Auditoría de retención — cutoff: {$cutoff} (> " . self::RETENTION_YEARS . ' años desde el último acto médico)');

        $pacientes = $this->pacientesSinActividadDesde($cutoff);

        $reports = [
            'pacientes con expediente vencido' => $pacientes->count(),
            'medical_records' => DB::table('medical_records')->whereIn('patient_id', $pacientes)->count(),
            'prescriptions' => DB::table('prescriptions')->whereIn('patient_id', $pacientes)->count(),
            'appointments' => DB::table('appointments')
                ->whereIn('patient_id', $pacientes)
                ->whereIn('status', ['completed', 'no_show', 'cancelled'])
                ->count(),
            // Lo fiscal va por su propia fecha: no depende del expediente.
            'payments' => DB::table('payments')->where('payment_date', '<', $cutoff)->count(),
            'commissions' => DB::table('commissions')->where('earned_at', '<', $cutoff)->count(),
        ];

        $this->table(['Tabla', 'Registros candidatos'], collect($reports)->map(
            fn ($count, $table) => [$table, $count]
        )->values()->toArray());

        $total = array_sum(array_slice($reports, 1));

        if ($total === 0) {
            $this->info('Nada que borrar: todos los registros están dentro del periodo de retención.');

            return self::SUCCESS;
        }

        if (! $this->option('force')) {
            $this->warn("Total: {$total} registros candidatos. Usa --force para borrarlos.");

            return self::SUCCESS;
        }

        $this->warn('Borrando registros fuera del periodo de retención...');

        // Consultas directas para saltar el bloqueo de Lockable: el borrado
        // por retención es legítimo y lo prevé la propia normativa.
        DB::table('payments')->where('payment_date', '<', $cutoff)->delete();

        $recetas = DB::table('prescriptions')->whereIn('patient_id', $pacientes)->pluck('id');
        DB::table('prescription_items')->whereIn('prescription_id', $recetas)->delete();
        DB::table('prescriptions')->whereIn('id', $recetas)->delete();

        DB::table('medical_records')->whereIn('patient_id', $pacientes)->delete();
        DB::table('appointments')
            ->whereIn('patient_id', $pacientes)
            ->whereIn('status', ['completed', 'no_show', 'cancelled'])
            ->delete();
        DB::table('commissions')->where('earned_at', '<', $cutoff)->delete();

        $this->info('Borrado completado. Registros eliminados conforme a política de retención.');

        return self::SUCCESS;
    }

    /**
     * Pacientes cuyo último acto médico es anterior al corte: no tienen ninguna
     * nota, receta ni cita desde entonces, y sí tienen algo antes.
     */
    private function pacientesSinActividadDesde(string $cutoff): \Illuminate\Support\Collection
    {
        $reciente = fn (string $tabla, string $fecha) => fn (Builder $q) => $q
            ->select(DB::raw(1))
            ->from($tabla)
            ->whereColumn("{$tabla}.patient_id", 'patients.id')
            ->where($fecha, '>=', $cutoff);

        $antiguo = fn (string $tabla) => fn (Builder $q) => $q
            ->select(DB::raw(1))
            ->from($tabla)
            ->whereColumn("{$tabla}.patient_id", 'patients.id');

        return DB::table('patients')
            ->whereNotExists($reciente('medical_records', 'visit_date'))
            ->whereNotExists($reciente('prescriptions', 'prescription_date'))
            ->whereNotExists($reciente('appointments', 'starts_at'))
            ->where(fn (Builder $q) => $q
                ->whereExists($antiguo('medical_records'))
                ->orWhereExists($antiguo('prescriptions'))
                ->orWhereExists($antiguo('appointments')))
            ->pluck('id');
    }
}
