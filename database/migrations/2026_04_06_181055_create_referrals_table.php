<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('referrals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('referrer_id')->constrained('users')->cascadeOnDelete();
            $table->string('referrer_code')->unique();
            $table->string('referred_email');
            $table->foreignId('referred_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('status', ['pending', 'registered', 'rewarded'])->default('pending');
            $table->string('reward_type')->nullable();
            $table->integer('reward_days')->nullable();
            $table->timestamp('rewarded_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('referrals');
    }
};
