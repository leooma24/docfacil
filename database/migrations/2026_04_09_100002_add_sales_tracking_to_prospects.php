<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('prospects', function (Blueprint $table) {
            $table->foreignId('assigned_to_sales_rep_id')->nullable()->after('status')->constrained('users')->nullOnDelete();
            $table->foreignId('converted_clinic_id')->nullable()->after('converted_at')->constrained('clinics')->nullOnDelete();
            $table->timestamp('last_followup_at')->nullable();
            $table->timestamp('next_followup_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('prospects', function (Blueprint $table) {
            $table->dropForeign(['assigned_to_sales_rep_id']);
            $table->dropForeign(['converted_clinic_id']);
            $table->dropColumn(['assigned_to_sales_rep_id', 'converted_clinic_id', 'last_followup_at', 'next_followup_at']);
        });
    }
};
