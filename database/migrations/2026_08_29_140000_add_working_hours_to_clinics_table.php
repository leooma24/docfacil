<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Horario de atención del consultorio.
 *
 * No existía en ningún lado: un paciente podía pedir cita el domingo a las
 * 3 de la mañana desde el portal público y el sistema la aceptaba. El
 * calendario solo tenía 07:00–21:00 quemado como rango visual, que es otra
 * cosa: eso pinta la cuadrícula, no impide nada.
 *
 * Se guarda como JSON con una entrada por día (null = cerrado), que es más
 * simple de leer y editar que siete pares de columnas.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clinics', function (Blueprint $table) {
            $table->json('working_hours')->nullable()->after('phone');
        });
    }

    public function down(): void
    {
        Schema::table('clinics', function (Blueprint $table) {
            $table->dropColumn('working_hours');
        });
    }
};
