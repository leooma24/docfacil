<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * El corte del mes le llega por correo al doctor el día 1. Esta columna es
 * para que pueda decir que no desde el mismo correo o desde su configuración.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clinics', function (Blueprint $table) {
            $table->boolean('corte_por_correo')->default(true)->after('minutos_entre_citas');
        });
    }

    public function down(): void
    {
        Schema::table('clinics', function (Blueprint $table) {
            $table->dropColumn('corte_por_correo');
        });
    }
};
