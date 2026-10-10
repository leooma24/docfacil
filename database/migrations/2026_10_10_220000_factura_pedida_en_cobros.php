<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Quién pidió factura y si ya se le mandó (10-oct-2026).
 *
 * DocFácil no hace CFDI: la factura la saca el contador del dentista. Lo que
 * faltaba era no olvidarla: "a veces el paciente me reclama un mes después".
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->boolean('factura_solicitada')->default(false)->after('notes');
            $table->dateTime('factura_enviada_at')->nullable()->after('factura_solicitada');
        });
    }

    public function down(): void
    {
        Schema::table('payments', fn (Blueprint $table) => $table->dropColumn(['factura_solicitada', 'factura_enviada_at']));
    }
};
