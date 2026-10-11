<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Día 2 del plan (10-oct-2026).
 * - payments.es_anticipo: el dinero que el paciente deja antes del
 *   tratamiento; queda a su favor y se descuenta en el siguiente cobro.
 * - cortes_de_caja: el cierre del día, con lo que se contó en el cajón.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->boolean('es_anticipo')->default(false)->after('status');
        });

        Schema::create('cortes_de_caja', function (Blueprint $table) {
            $table->id();
            $table->foreignId('clinic_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->date('fecha');
            $table->decimal('esperado', 10, 2);
            $table->decimal('contado', 10, 2);
            $table->decimal('diferencia', 10, 2);
            $table->string('notas', 500)->nullable();
            $table->timestamps();
            $table->index(['clinic_id', 'fecha']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cortes_de_caja');
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn('es_anticipo');
        });
    }
};
