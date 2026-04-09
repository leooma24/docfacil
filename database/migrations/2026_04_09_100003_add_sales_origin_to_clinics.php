<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('clinics', function (Blueprint $table) {
            $table->foreignId('sold_by_user_id')->nullable()->after('is_active')->constrained('users')->nullOnDelete();
            $table->timestamp('sold_at')->nullable();
            $table->timestamp('first_payment_received_at')->nullable();
            $table->timestamp('second_payment_received_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('clinics', function (Blueprint $table) {
            $table->dropForeign(['sold_by_user_id']);
            $table->dropColumn(['sold_by_user_id', 'sold_at', 'first_payment_received_at', 'second_payment_received_at', 'cancelled_at']);
        });
    }
};
