<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Datos fiscales del paciente, para la lista que el dentista le pasa a su
 * contador (11-oct-2026). El CFDI va en una segunda fase y usará estos mismos.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('patients', function (Blueprint $table) {
            $table->string('rfc', 13)->nullable();
            $table->string('razon_social')->nullable();
            $table->string('regimen_fiscal', 3)->nullable();
            $table->string('cp_fiscal', 5)->nullable();
            $table->string('uso_cfdi', 4)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('patients', function (Blueprint $table) {
            $table->dropColumn(['rfc', 'razon_social', 'regimen_fiscal', 'cp_fiscal', 'uso_cfdi']);
        });
    }
};
