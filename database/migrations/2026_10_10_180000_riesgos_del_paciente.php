<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Lo importante del paciente, con casillas (10-oct-2026).
 *
 * `riesgos` guarda las casillas marcadas (diabetes, hipertensión,
 * anticoagulantes, embarazo...). `riesgos_revisados_at` es la última vez que
 * alguien confirmó que sigue igual: de ahí sale el "¿sigue igual?" a los
 * 6 meses. Nada de esto reemplaza las alergias ni las notas médicas: se junta
 * con ellas.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('patients', function (Blueprint $table) {
            $table->json('riesgos')->nullable()->after('medical_notes');
            $table->dateTime('riesgos_revisados_at')->nullable()->after('riesgos');
        });
    }

    public function down(): void
    {
        Schema::table('patients', fn (Blueprint $table) => $table->dropColumn(['riesgos', 'riesgos_revisados_at']));
    }
};
