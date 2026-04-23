<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Recall automático: permite marcar servicios que necesitan seguimiento
 * periódico (limpiezas cada 6m, revisiones cada 12m, ortodoncia cada 1m).
 *
 * Feature-gated por 'recall_automation' — add-on $49/mes (o bundled en
 * Básico+ mientras termina la infra del marketplace en W3).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $table->unsignedTinyInteger('recall_months')->nullable()->after('category');
        });
    }

    public function down(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $table->dropColumn('recall_months');
        });
    }
};
