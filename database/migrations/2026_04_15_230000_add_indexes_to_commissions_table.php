<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('commissions', function (Blueprint $table) {
            // Query común: Commission::where('clinic_id', X)->whereIn('status', [...])
            $table->index(['clinic_id', 'status'], 'commissions_clinic_status_idx');
            // Query del vendedor: Commission::forUser($id)->where('status', 'pending')
            $table->index(['user_id', 'status'], 'commissions_user_status_idx');
        });
    }

    public function down(): void
    {
        Schema::table('commissions', function (Blueprint $table) {
            $table->dropIndex('commissions_clinic_status_idx');
            $table->dropIndex('commissions_user_status_idx');
        });
    }
};
