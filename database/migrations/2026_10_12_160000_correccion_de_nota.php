<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Corrección (addendum) de una nota bloqueada: una nota nueva ligada a la
 * original, que no se toca (NOM-024 6.3.4, NOM-004).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('medical_records', function (Blueprint $table) {
            $table->foreignId('corrige_a_id')->nullable()->after('appointment_id')->constrained('medical_records')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('medical_records', fn (Blueprint $table) => $table->dropConstrainedForeignId('corrige_a_id'));
    }
};
