<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Días en que el consultorio cierra aunque sea su horario normal:
 * vacaciones, el 16 de septiembre, un congreso, una incapacidad.
 *
 * Sin esto, el portal público seguía aceptando citas para toda la semana
 * que el doctor se iba de vacaciones.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clinic_closures', function (Blueprint $table) {
            $table->id();
            $table->foreignId('clinic_id')->constrained()->cascadeOnDelete();
            $table->date('starts_on');
            // Igual a starts_on cuando es un solo día.
            $table->date('ends_on');
            $table->string('reason')->nullable();
            $table->timestamps();

            // Se consulta siempre por clínica y rango de fechas.
            $table->index(['clinic_id', 'starts_on', 'ends_on']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clinic_closures');
    }
};
