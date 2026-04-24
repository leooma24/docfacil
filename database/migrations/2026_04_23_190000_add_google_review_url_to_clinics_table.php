<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Add-on Reseñas Google — la clínica pega su URL directa de dejar
 * reseña en Google Maps (obtenida desde su perfil de Google Business)
 * y DocFácil la usa para pre-armar el mensaje de WhatsApp al paciente.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clinics', function (Blueprint $table) {
            $table->string('google_review_url', 500)->nullable()->after('logo');
        });
    }

    public function down(): void
    {
        Schema::table('clinics', function (Blueprint $table) {
            $table->dropColumn('google_review_url');
        });
    }
};
