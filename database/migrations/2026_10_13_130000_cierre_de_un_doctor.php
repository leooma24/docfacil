<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Día 6 del plan (10-oct-2026): las vacaciones o el congreso de un doctor
 * cierran solo su agenda, no todo el consultorio. Sin doctor, cierra todo.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clinic_closures', function (Blueprint $table) {
            $table->foreignId('doctor_id')->nullable()->after('clinic_id')->constrained()->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('clinic_closures', function (Blueprint $table) {
            $table->dropConstrainedForeignId('doctor_id');
        });
    }
};
