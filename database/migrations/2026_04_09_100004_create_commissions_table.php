<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('commissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('clinic_id')->constrained()->cascadeOnDelete();
            $table->foreignId('prospect_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('tier', ['first', 'second']);
            $table->decimal('amount', 10, 2);
            $table->string('plan_at_sale', 50);
            $table->enum('status', ['pending', 'paid', 'clawed_back', 'cancelled'])->default('pending');
            $table->timestamp('earned_at');
            $table->timestamp('paid_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index(['clinic_id', 'tier']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('commissions');
    }
};
