<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Archivos del paciente: la foto de su hoja de papel, radiografías, estudios
 * (10-oct-2026). Viven en el disco privado; aquí solo se guarda dónde.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('patient_files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('clinic_id')->constrained()->cascadeOnDelete();
            $table->foreignId('patient_id')->constrained()->cascadeOnDelete();
            $table->string('path');
            $table->string('nombre');
            $table->string('mime', 100);
            $table->unsignedInteger('size');
            $table->string('nota')->nullable();
            $table->foreignId('subido_por')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['clinic_id', 'patient_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('patient_files');
    }
};
