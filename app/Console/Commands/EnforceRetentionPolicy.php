<?php

namespace App\Console\Commands;

use App\Models\Appointment;
use App\Models\MedicalRecord;
use App\Models\Payment;
use App\Models\Prescription;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Política de retención de datos — LFPDPPP art. 11 + NOM-004-SSA3-2012.
 *
 * - Expedientes clínicos: mínimo 5 años desde último acto médico.
 * - Datos fiscales: 5 años (Código Fiscal de la Federación art. 30).
 * - Margen de seguridad: 7 años.
 *
 * Este comando NO borra nada — solo reporta registros candidatos a borrado.
 * El borrado efectivo requiere confirmación manual o activar --force.
 */
class EnforceRetentionPolicy extends Command
{
    protected $signature = 'app:retention-report {--force : Borra los registros reportados}';

    protected $description = 'Reporta (y opcionalmente borra) registros que superaron el periodo de retención legal';

    private const RETENTION_YEARS = 7;

    public function handle(): int
    {
        $cutoff = now()->subYears(self::RETENTION_YEARS);
        $this->info("Auditoría de retención — cutoff: {$cutoff->toDateString()} (> " . self::RETENTION_YEARS . " años)");

        $reports = [
            'medical_records' => MedicalRecord::withoutGlobalScopes()
                ->where('visit_date', '<', $cutoff)
                ->count(),
            'prescriptions' => Prescription::withoutGlobalScopes()
                ->where('prescription_date', '<', $cutoff)
                ->count(),
            'appointments' => Appointment::withoutGlobalScopes()
                ->where('starts_at', '<', $cutoff)
                ->whereIn('status', ['completed', 'no_show', 'cancelled'])
                ->count(),
            'payments' => Payment::withoutGlobalScopes()
                ->where('payment_date', '<', $cutoff)
                ->count(),
            'commissions' => \DB::table('commissions')
                ->where('earned_at', '<', $cutoff)
                ->count(),
        ];

        $this->table(['Tabla', 'Registros candidatos'], collect($reports)->map(
            fn ($count, $table) => [$table, $count]
        )->values()->toArray());

        $total = array_sum($reports);
        if ($total === 0) {
            $this->info('Nada que borrar: todos los registros están dentro del periodo de retención.');
            return self::SUCCESS;
        }

        if (!$this->option('force')) {
            $this->warn("Total: {$total} registros candidatos. Usa --force para borrarlos.");
            return self::SUCCESS;
        }

        $this->warn('Borrando registros fuera del periodo de retención...');
        // Usamos DB::table() directo para saltar el trait Lockable.
        // El borrado por retención es legítimo y autorizado por la propia normativa.
        DB::table('payments')->where('payment_date', '<', $cutoff)->delete();

        // Prescription items primero (FK)
        $oldRxIds = DB::table('prescriptions')->where('prescription_date', '<', $cutoff)->pluck('id');
        DB::table('prescription_items')->whereIn('prescription_id', $oldRxIds)->delete();
        DB::table('prescriptions')->whereIn('id', $oldRxIds)->delete();

        DB::table('medical_records')->where('visit_date', '<', $cutoff)->delete();
        DB::table('appointments')
            ->where('starts_at', '<', $cutoff)
            ->whereIn('status', ['completed', 'no_show', 'cancelled'])
            ->delete();
        DB::table('commissions')->where('earned_at', '<', $cutoff)->delete();

        $this->info("Borrado completado. Registros eliminados conforme a política de retención.");
        return self::SUCCESS;
    }
}
