<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * El paciente que pidió que no le escriban por WhatsApp (10-oct-2026).
 * Lo marca el consultorio, o se marca solo si contesta "ya no me manden".
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('patients', function (Blueprint $table) {
            $table->boolean('no_quiere_whatsapp')->default(false)->after('phone');
            $table->timestamp('no_quiere_whatsapp_at')->nullable()->after('no_quiere_whatsapp');
        });
    }

    public function down(): void
    {
        Schema::table('patients', function (Blueprint $table) {
            $table->dropColumn(['no_quiere_whatsapp', 'no_quiere_whatsapp_at']);
        });
    }
};
