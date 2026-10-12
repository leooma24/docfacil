<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Los módulos que el consultorio prende y apaga (11-oct-2026). NULL = todo
 * prendido, como estaban los consultorios que ya lo usaban; el nuevo nace con
 * [] (todo apagado) y prende lo que use.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clinics', function (Blueprint $table) {
            $table->json('modulos')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('clinics', function (Blueprint $table) {
            $table->dropColumn('modulos');
        });
    }
};
