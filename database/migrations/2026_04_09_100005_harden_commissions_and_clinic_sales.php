<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Hardening post-review de seguridad:
 * - Unique index (clinic_id, tier) para prevenir comisiones duplicadas por race condition.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::table('commissions', function (Blueprint $table) {
            $table->unique(['clinic_id', 'tier'], 'commissions_clinic_tier_unique');
        });
    }

    public function down(): void
    {
        Schema::table('commissions', function (Blueprint $table) {
            $table->dropUnique('commissions_clinic_tier_unique');
        });
    }
};
