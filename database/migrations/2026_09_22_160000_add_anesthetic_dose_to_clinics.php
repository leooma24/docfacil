<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Los parámetros para vigilar la dosis de anestesia.
 *
 * Van VACÍOS a propósito. La dosis máxima depende del anestésico (no es lo
 * mismo la lidocaína con epinefrina que sin ella), y un número de dosis máxima
 * equivocado en una app clínica es peor que no dar ninguno: el doctor confiaría
 * en él. Así que el mecanismo existe y no afirma nada hasta que alguien con
 * criterio clínico lo configure.
 *
 * - `anesthetic_max_mg_kg`  — el límite por toxicidad, en mg por kilo de peso.
 * - `anesthetic_mg_ml`      — la concentración del anestésico que usan.
 * - `anesthetic_ml_per_cartridge`— el volumen del cartucho. 1.8 ml es el estándar
 *                            dental, así que ese sí viene puesto.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clinics', function (Blueprint $table) {
            $table->decimal('anesthetic_max_mg_kg', 5, 2)->nullable()->after('minutos_entre_citas');
            $table->decimal('anesthetic_mg_ml', 6, 2)->nullable()->after('anesthetic_max_mg_kg');
            $table->decimal('anesthetic_ml_per_cartridge', 4, 2)->default(1.8)->after('anesthetic_mg_ml');
        });
    }

    public function down(): void
    {
        Schema::table('clinics', function (Blueprint $table) {
            $table->dropColumn(['anesthetic_max_mg_kg', 'anesthetic_mg_ml', 'anesthetic_ml_per_cartridge']);
        });
    }
};
