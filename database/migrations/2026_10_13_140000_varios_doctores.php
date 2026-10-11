<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Día 8 del plan (10-oct-2026): clínicas con varios doctores.
 * - doctors.comision_porcentaje y comision_montos ({servicio: monto fijo}).
 * - liquidaciones: lo que se le pagó a cada doctor por periodo.
 * - clinics.exigir_dos_pasos: el dueño pide la verificación a todo el equipo.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('doctors', function (Blueprint $table) {
            $table->decimal('comision_porcentaje', 5, 2)->nullable();
            $table->json('comision_montos')->nullable();
        });

        Schema::create('liquidaciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('clinic_id')->constrained()->cascadeOnDelete();
            $table->foreignId('doctor_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('expense_id')->nullable()->constrained()->nullOnDelete();
            $table->date('desde');
            $table->date('hasta');
            $table->decimal('monto', 10, 2);
            $table->timestamps();
            $table->unique(['doctor_id', 'desde', 'hasta']);
        });

        Schema::table('clinics', function (Blueprint $table) {
            $table->boolean('exigir_dos_pasos')->default(false);
        });
    }

    public function down(): void
    {
        Schema::table('clinics', fn (Blueprint $table) => $table->dropColumn('exigir_dos_pasos'));
        Schema::dropIfExists('liquidaciones');
        Schema::table('doctors', fn (Blueprint $table) => $table->dropColumn(['comision_porcentaje', 'comision_montos']));
    }
};
