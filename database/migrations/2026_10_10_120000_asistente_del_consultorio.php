<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * La asistente con su propio usuario (10-oct-2026).
 *
 * La invitación ahora dice a quién se invita (doctor o asistente) y si esa
 * asistente ve el dinero del consultorio (corte, gastos, ingresos). Los
 * doctores siempre lo ven; el permiso solo aplica al staff.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('doctor_invitations', function (Blueprint $table) {
            $table->string('role', 20)->default('doctor')->after('specialty');
            $table->boolean('ve_dinero')->default(false)->after('role');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->boolean('ve_dinero')->default(false)->after('role');
        });
    }

    public function down(): void
    {
        Schema::table('doctor_invitations', fn (Blueprint $table) => $table->dropColumn(['role', 've_dinero']));
        Schema::table('users', fn (Blueprint $table) => $table->dropColumn('ve_dinero'));
    }
};
