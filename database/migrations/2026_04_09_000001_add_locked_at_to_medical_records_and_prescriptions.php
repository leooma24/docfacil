<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * NOM-004-SSA3-2012 — inmutabilidad del expediente clínico.
 *
 * Agregamos locked_at a notas clínicas y recetas: después de 24h una nota
 * queda bloqueada y no puede modificarse ni borrarse (solo se puede
 * crear addendum). Las correcciones quedan en el audit log de Spatie.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::table('medical_records', function (Blueprint $table) {
            $table->timestamp('locked_at')->nullable()->after('vital_signs');
        });

        Schema::table('prescriptions', function (Blueprint $table) {
            $table->timestamp('locked_at')->nullable()->after('notes');
        });

        Schema::table('consent_forms', function (Blueprint $table) {
            $table->timestamp('locked_at')->nullable()->after('signed_at');
        });
    }

    public function down(): void
    {
        Schema::table('medical_records', fn (Blueprint $t) => $t->dropColumn('locked_at'));
        Schema::table('prescriptions', fn (Blueprint $t) => $t->dropColumn('locked_at'));
        Schema::table('consent_forms', fn (Blueprint $t) => $t->dropColumn('locked_at'));
    }
};
