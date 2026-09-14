<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * La zona horaria del consultorio.
 *
 * Vacía a propósito: mientras el doctor no elija, se usa la que le toca por
 * su ciudad o su estado (ver App\Support\ZonaHoraria). Así los consultorios
 * de Sinaloa o Cancún que ya existen quedan a su hora sin tener que entrar a
 * configurarla.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clinics', function (Blueprint $table) {
            $table->string('timezone', 40)->nullable()->after('state');
        });
    }

    public function down(): void
    {
        Schema::table('clinics', function (Blueprint $table) {
            $table->dropColumn('timezone');
        });
    }
};
