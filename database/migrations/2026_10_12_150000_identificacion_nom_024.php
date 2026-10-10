<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Datos mínimos del paciente de la NOM-024-SSA3-2012 (6.5 y Tabla 1): CURP,
 * entidad de nacimiento, nacionalidad y residencia. Fecha de nacimiento y
 * sexo ya existían. Todos opcionales: no frenan dar de alta a un paciente.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('patients', function (Blueprint $table) {
            $table->string('curp', 18)->nullable()->after('last_name');
            $table->string('entidad_nacimiento', 2)->nullable()->after('birth_date');
            $table->string('nacionalidad', 3)->nullable()->after('entidad_nacimiento');
            $table->string('estado_residencia', 2)->nullable()->after('address');
            $table->string('municipio_residencia', 100)->nullable()->after('estado_residencia');
            $table->unique(['clinic_id', 'curp']);
        });
    }

    public function down(): void
    {
        Schema::table('patients', function (Blueprint $table) {
            $table->dropUnique(['clinic_id', 'curp']);
            $table->dropColumn(['curp', 'entidad_nacimiento', 'nacionalidad', 'estado_residencia', 'municipio_residencia']);
        });
    }
};
