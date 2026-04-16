<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('premium_service_purchases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('clinic_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('premium_service_id')->constrained()->restrictOnDelete();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();

            // Snapshot por si cambia el catálogo
            $table->string('service_name_snapshot');
            $table->decimal('amount_mxn', 10, 2);
            $table->string('pricing_type', 20);

            // Workflow
            $table->string('status', 25)->default('pending_payment');
            // pending_payment → paid → in_progress → delivered → cancelled | refunded

            $table->string('payment_method', 20)->nullable(); // stripe | spei | manual
            $table->string('stripe_session_id')->nullable()->index();
            $table->foreignId('spei_payment_id')->nullable()->constrained('spei_payments')->nullOnDelete();

            $table->json('intake_data')->nullable();
            $table->text('client_notes')->nullable();
            $table->json('delivery_files')->nullable();
            $table->text('delivery_notes')->nullable();

            $table->timestamp('paid_at')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->text('internal_notes')->nullable();

            $table->timestamps();

            $table->index(['clinic_id', 'status']);
            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('premium_service_purchases');
    }
};
