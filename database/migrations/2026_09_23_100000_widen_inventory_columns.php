<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Ensancha columnas de insumos que quedaron cortas.
 *
 * Las migraciones originales de insumos se editaron despues de haberse
 * corrido en produccion, asi que alla el cambio no llega: una migracion ya
 * registrada no se vuelve a ejecutar. Esta es la que si llega.
 *
 * Los dos casos que importan:
 *
 * - `tooth_number` a 10 caracteres. `SupplyScope::teeth()` parte listas
 *   ("16, 15, 14, 13") y rangos, que es el flujo previsto. Con 10, MySQL en
 *   modo estricto lanza "Data too long" y revienta el cierre de la consulta;
 *   SQLite, donde corren las pruebas, se lo traga sin decir nada.
 *
 * - Los costos a 2 decimales. El costo es por unidad de consumo (un
 *   mililitro, un guante), no por caja: un insumo de $0.008 el ml se guarda
 *   como $0.01, 25% de error multiplicado por cada consulta.
 *
 * Es segura donde las columnas ya tienen el tamano nuevo (una instalacion
 * hecha despues del arreglo): cambiar una columna a la definicion que ya
 * tiene no altera nada.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('supplies', function (Blueprint $table) {
            // Se repite el default que la columna ya tenia: change() reemplaza
            // la definicion completa, no solo el pedazo que cambia.
            $table->decimal('cost_per_unit', 12, 4)->default(0)->change();
        });

        Schema::table('supply_movements', function (Blueprint $table) {
            $table->decimal('unit_cost', 12, 4)->nullable()->change();

            // 64 alcanza para un nombre de clase (App\Models\Appointment son
            // 21) y deja holgura en el indice unico que la incluye.
            $table->string('reference_type', 64)->nullable()->change();

            // DATETIME y no TIMESTAMP: TIMESTAMP se convierte con la zona
            // horaria de la sesion de MySQL, y desde este lote cada
            // consultorio tiene la suya. Ademas no topa en 2038.
            $table->dateTime('occurred_at')->change();
        });

        Schema::table('supply_lots', function (Blueprint $table) {
            $table->decimal('unit_cost', 12, 4)->nullable()->change();
        });

        Schema::table('consultation_procedures', function (Blueprint $table) {
            $table->string('tooth_number', 60)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('supplies', function (Blueprint $table) {
            $table->decimal('cost_per_unit', 10, 2)->default(0)->change();
        });

        Schema::table('supply_movements', function (Blueprint $table) {
            $table->decimal('unit_cost', 10, 2)->nullable()->change();
            $table->string('reference_type')->nullable()->change();
            $table->timestamp('occurred_at')->change();
        });

        Schema::table('supply_lots', function (Blueprint $table) {
            $table->decimal('unit_cost', 10, 2)->nullable()->change();
        });

        Schema::table('consultation_procedures', function (Blueprint $table) {
            $table->string('tooth_number', 10)->nullable()->change();
        });
    }
};
