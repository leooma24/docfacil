<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Cuándo se le recordó por última vez un presupuesto al paciente (10-oct-2026).
 *
 * De ahí sale la regla del dentista: un recordatorio amable al mes y no más,
 * que él decide mandar. Sin la fecha, la lista de "lo voy a pensar" no sabe a
 * quién ya se le escribió.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('treatment_plans', function (Blueprint $table) {
            $table->dateTime('last_reminded_at')->nullable()->after('rejected_at');
        });
    }

    public function down(): void
    {
        Schema::table('treatment_plans', fn (Blueprint $table) => $table->dropColumn('last_reminded_at'));
    }
};
