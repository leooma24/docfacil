<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Lo que los pacientes le escriben al número de DocFácil (12-oct-2026): se
 * le pasa a su consultorio. Sin consultorio cuando no se pudo reconocer.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mensajes_de_pacientes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('clinic_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('patient_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('appointment_id')->nullable()->constrained()->nullOnDelete();
            $table->string('telefono', 20)->index();
            $table->text('texto');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mensajes_de_pacientes');
    }
};
