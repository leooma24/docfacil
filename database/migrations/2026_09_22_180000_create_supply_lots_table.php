<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Los lotes, para poder avisar de caducidades.
 *
 * Sin esto el kardex sabe cuánto hay, pero no desde cuándo: un frasco de
 * composite que entró hace ocho meses y otro que entró ayer son el mismo
 * renglón. Y la caducidad es de las mermas que más duele, porque se pudo haber
 * evitado.
 *
 * El lote es OPCIONAL: los guantes no caducan y capturarlo sería trabajo sin
 * provecho. Cuando no se captura, el insumo simplemente no avisa de nada — que
 * es distinto de inventar una fecha.
 *
 * El consumo se reparte FEFO (primero lo que caduca antes), y eso se calcula al
 * leer, no se guarda: ver Supply::fefoAllocation().
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('supply_lots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('clinic_id')->constrained()->cascadeOnDelete();
            $table->foreignId('supply_id')->constrained()->cascadeOnDelete();

            $table->string('lot_number')->nullable();
            $table->date('expires_on')->nullable();

            // Lo que entró en este lote, en unidad de consumo.
            $table->decimal('quantity', 12, 3);
            $table->decimal('unit_cost', 12, 4)->nullable();
            $table->string('supplier')->nullable();
            $table->timestamps();

            $table->index(['clinic_id', 'supply_id', 'expires_on']);
        });

        Schema::table('supply_movements', function (Blueprint $table) {
            $table->foreignId('supply_lot_id')->nullable()->after('supply_id')
                ->constrained('supply_lots')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('supply_movements', function (Blueprint $table) {
            $table->dropConstrainedForeignId('supply_lot_id');
        });

        Schema::dropIfExists('supply_lots');
    }
};
