<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Día 5 del plan (10-oct-2026): cómo quedó cada llamada de seguimiento
 * (logrado, volver a llamar en X días o cerrar caso).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('seguimientos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('clinic_id')->constrained()->cascadeOnDelete();
            $table->foreignId('patient_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('motivo', 20);
            $table->string('resultado', 10);
            $table->date('volver_el')->nullable();
            $table->timestamps();
            $table->index(['clinic_id', 'patient_id', 'motivo']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seguimientos');
    }
};
