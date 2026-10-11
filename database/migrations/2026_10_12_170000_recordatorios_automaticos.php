<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Recordatorios automáticos por la API oficial de WhatsApp: cada consultorio
 * decide si los prende, y con un tope de mensajes al día para cuidar el costo.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clinics', function (Blueprint $table) {
            $table->boolean('recordatorios_automaticos')->default(false);
            $table->unsignedSmallInteger('recordatorios_por_dia')->default(200);
        });
    }

    public function down(): void
    {
        Schema::table('clinics', fn (Blueprint $table) => $table->dropColumn(['recordatorios_automaticos', 'recordatorios_por_dia']));
    }
};
