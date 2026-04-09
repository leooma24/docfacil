<?php

namespace App\Console\Commands;

use App\Models\Clinic;
use Database\Seeders\DemoSalesSeeder;
use Database\Seeders\DemoSeeder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DemoReset extends Command
{
    protected $signature = 'app:demo-reset';

    protected $description = 'Reset demo clinic data: wipe and reseed from DemoSeeder';

    public function handle(): int
    {
        $this->info('Resetting demo data...');

        // Limpiar primero las clínicas ficticias del vendedor demo (evita huérfanos
        // que sobreviven entre corridas porque no pertenecen a la clínica principal).
        DB::table('clinics')->where('slug', 'like', 'demo-sales-%')->delete();

        DB::transaction(function () {
            $clinics = Clinic::where('slug', 'clinica-dental-sonrisas-cdmx')->get();

            foreach ($clinics as $clinic) {
                $clinicId = $clinic->id;

                // Delete in dependency order (children first)
                DB::table('prescription_items')->whereIn(
                    'prescription_id',
                    DB::table('prescriptions')->where('clinic_id', $clinicId)->pluck('id')
                )->delete();
                DB::table('odontogram_teeth')->whereIn(
                    'odontogram_id',
                    DB::table('odontograms')->where('clinic_id', $clinicId)->pluck('id')
                )->delete();

                foreach ([
                    'prescriptions', 'consent_forms', 'odontograms',
                    'payments', 'medical_records', 'appointments',
                    'patients', 'services', 'doctors',
                ] as $table) {
                    if (Schema::hasTable($table)) {
                        DB::table($table)->where('clinic_id', $clinicId)->delete();
                    }
                }

                // Delete users belonging to this clinic
                DB::table('users')->where('clinic_id', $clinicId)->delete();

                // Finally the clinic itself
                DB::table('clinics')->where('id', $clinicId)->delete();
            }
        });

        $this->info('Reseeding demo doctor...');
        (new DemoSeeder())->run();

        $this->info('Reseeding demo sales rep...');
        (new DemoSalesSeeder())->run();

        $this->info('Demo reset complete.');

        return self::SUCCESS;
    }
}
