<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('spei_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('clinic_id')->constrained('clinics')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete(); // quien inició el pago

            // Contexto del pago
            $table->string('plan', 20); // basico, profesional, clinica
            $table->string('billing_cycle', 10); // monthly | annual
            $table->decimal('amount', 10, 2); // monto esperado en MXN
            $table->string('reference_code', 40)->unique(); // DOCF-{clinic}-{timestamp}

            // Comprobante
            $table->string('receipt_path')->nullable(); // storage/app/public/...
            $table->string('receipt_original_name')->nullable();
            $table->string('receipt_mime', 40)->nullable();
            $table->unsignedInteger('receipt_size_bytes')->nullable();
            $table->text('client_notes')->nullable();

            // Revisión administrativa
            $table->string('status', 20)->default('pending'); // pending | approved | rejected | expired
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('review_notes')->nullable();

            // Efecto de la aprobación
            $table->timestamp('plan_activated_until')->nullable(); // hasta cuándo queda activo el plan tras aprobar

            $table->timestamps();

            $table->index(['status', 'created_at']);
            $table->index('clinic_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('spei_payments');
    }
};
