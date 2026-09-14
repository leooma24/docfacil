<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Cuándo y cómo aceptó el paciente el aviso de privacidad del consultorio.
 *
 * Los datos de salud son sensibles: la Ley Federal de Protección de Datos
 * Personales en Posesión de los Particulares (DOF 20-mar-2025, art. 8) pide
 * consentimiento expreso y por escrito, que se pueda demostrar. Se guarda la
 * fecha, la versión del aviso que aceptó y por dónde lo hizo.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('patients', function (Blueprint $table) {
            $table->timestamp('aviso_privacidad_aceptado_at')->nullable();
            $table->string('aviso_privacidad_version', 20)->nullable();
            $table->string('aviso_privacidad_medio', 30)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('patients', function (Blueprint $table) {
            $table->dropColumn([
                'aviso_privacidad_aceptado_at',
                'aviso_privacidad_version',
                'aviso_privacidad_medio',
            ]);
        });
    }
};
