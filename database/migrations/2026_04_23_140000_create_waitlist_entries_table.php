<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Lista de espera — pacientes que quieren agendar en un rango de fechas/
 * servicio/doctor. Cuando se cancela una cita proxima, el observer manda
 * WhatsApp a los top N matches que siguen en waiting para ofrecerles el
 * hueco. El doctor reagenda manual con quien responda primero.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('waitlist_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('clinic_id')->constrained()->cascadeOnDelete();
            $table->foreignId('patient_id')->constrained()->cascadeOnDelete();
            $table->foreignId('service_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('doctor_id')->nullable()->constrained()->nullOnDelete();
            $table->date('desired_from');
            $table->date('desired_to');
            $table->unsignedTinyInteger('priority')->default(0); // 0=normal, 1=urgente
            $table->text('notes')->nullable();
            $table->string('status', 20)->default('waiting'); // waiting, notified, booked, expired, cancelled
            $table->timestamp('notified_at')->nullable();
            $table->foreignId('notified_for_appointment_id')->nullable()->constrained('appointments')->nullOnDelete();
            $table->timestamps();

            $table->index(['clinic_id', 'status']);
            $table->index(['desired_from', 'desired_to']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('waitlist_entries');
    }
};
