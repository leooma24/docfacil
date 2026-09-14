<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Lo que le faltaba a la receta para llevar lo que pide la ley.
 *
 * Del médico: la institución que expidió su título y, si es especialista, su
 * cédula de especialidad (reglamento de atención médica, arts. 64 y 65; Ley
 * General de Salud, art. 83). De cada medicamento: la presentación y la vía
 * de administración (Reglamento de Insumos para la Salud, art. 30).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('doctors', function (Blueprint $table) {
            $table->string('institucion_titulo', 150)->nullable()->after('license_number');
            $table->string('cedula_especialidad', 50)->nullable()->after('institucion_titulo');
        });

        Schema::table('prescription_items', function (Blueprint $table) {
            $table->string('presentacion', 100)->nullable()->after('medication');
            $table->string('via_administracion', 40)->nullable()->after('dosage');
        });
    }

    public function down(): void
    {
        Schema::table('doctors', function (Blueprint $table) {
            $table->dropColumn(['institucion_titulo', 'cedula_especialidad']);
        });

        Schema::table('prescription_items', function (Blueprint $table) {
            $table->dropColumn(['presentacion', 'via_administracion']);
        });
    }
};
