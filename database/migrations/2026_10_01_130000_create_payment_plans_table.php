<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Planes de pago (ortodoncia y tratamientos largos): un enganche y N
 * mensualidades. Cada mensualidad es un cobro normal (payments) con su
 * fecha de vencimiento, así que abonos, vencidos, recordatorios y el corte
 * funcionan igual que con cualquier cobro.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('clinic_id')->constrained()->cascadeOnDelete();
            $table->foreignId('patient_id')->constrained()->cascadeOnDelete();
            $table->foreignId('doctor_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('service_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('treatment_plan_id')->nullable()->constrained()->nullOnDelete();
            $table->string('description', 150);
            $table->decimal('total', 10, 2);
            $table->decimal('down_payment', 10, 2)->default(0);
            $table->unsignedSmallInteger('installments_count');
            $table->decimal('installment_amount', 10, 2);
            $table->date('first_due_date');
            $table->string('status', 20)->default('active');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['clinic_id', 'status']);
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->foreignId('payment_plan_id')->nullable()->after('service_id')->constrained()->cascadeOnDelete();
            // 0 es el enganche; 1..N las mensualidades.
            $table->unsignedSmallInteger('installment_number')->nullable()->after('payment_plan_id');
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropConstrainedForeignId('payment_plan_id');
            $table->dropColumn('installment_number');
        });
        Schema::dropIfExists('payment_plans');
    }
};
