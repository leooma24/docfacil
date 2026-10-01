<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Cada tratamiento de un presupuesto aceptado se agenda como cita, y la cita
 * queda ligada a él: la consulta sabe qué diente y qué servicio, y al
 * cerrarla el tratamiento queda hecho.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->foreignId('treatment_plan_item_id')->nullable()->after('service_id')->constrained()->nullOnDelete();
        });

        Schema::table('treatment_plan_items', function (Blueprint $table) {
            $table->timestamp('completed_at')->nullable()->after('sort_order');
        });
    }

    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->dropConstrainedForeignId('treatment_plan_item_id');
        });
        Schema::table('treatment_plan_items', function (Blueprint $table) {
            $table->dropColumn('completed_at');
        });
    }
};
