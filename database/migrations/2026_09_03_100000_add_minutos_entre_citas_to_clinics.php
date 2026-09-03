<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Minutos que el consultorio necesita entre un paciente y el siguiente.
 *
 * En odontología no se puede encadenar una cita pegada a la otra: hay que
 * limpiar el sillón, esterilizar y guardar. Sin esto el portal ofrecía las
 * 11:00 y las 12:00 seguidas para una cita de una hora, y el doctor se
 * quedaba sin un minuto de aire.
 *
 * Arranca en 0 para que a nadie le cambie la agenda de un día para otro: el
 * que lo quiera, lo prende en Configuración.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clinics', function (Blueprint $table) {
            $table->unsignedSmallInteger('minutos_entre_citas')->default(0)->after('working_hours');
        });
    }

    public function down(): void
    {
        Schema::table('clinics', function (Blueprint $table) {
            $table->dropColumn('minutos_entre_citas');
        });
    }
};
