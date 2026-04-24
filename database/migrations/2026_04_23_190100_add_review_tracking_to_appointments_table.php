<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tracking de cuando el doctor mandó la solicitud de reseña al paciente
 * por WhatsApp (click-to-wa.me). Evita pedir reseña dos veces por la
 * misma cita.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->timestamp('review_request_sent_at')->nullable()->after('followup_sent_at');
        });
    }

    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->dropColumn('review_request_sent_at');
        });
    }
};
