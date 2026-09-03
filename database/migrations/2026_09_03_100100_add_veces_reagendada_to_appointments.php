<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Cuántas veces se ha movido esta cita de horario.
 *
 * El dato ya quedaba en la bitácora de actividad, pero ahí nadie lo mira.
 * Un paciente que mueve su cita tres veces casi nunca llega a la cuarta, y
 * eso el doctor lo tiene que saber antes de guardarle el lugar.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->unsignedTinyInteger('veces_reagendada')->default(0)->after('confirmed_at');
        });
    }

    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->dropColumn('veces_reagendada');
        });
    }
};
