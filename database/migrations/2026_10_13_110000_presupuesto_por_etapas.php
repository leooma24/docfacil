<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Día 4 del plan (10-oct-2026): presupuesto por etapas.
 * - Cada tratamiento con su etapa ("1. Urgencia"), opcional (no suma al
 *   total) y varios dientes en un renglón ("11, 12, 21, 22").
 * - Descuento en porcentaje, cuánto detalle ve el paciente y su firma.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('treatment_plan_items', function (Blueprint $table) {
            $table->string('etapa', 60)->nullable()->after('treatment_plan_id');
            $table->boolean('opcional')->default(false)->after('subtotal');
            // Antes 10 caracteres: un diente o "46-47".
            $table->string('tooth_number', 60)->nullable()->change();
        });

        Schema::table('treatment_plans', function (Blueprint $table) {
            $table->decimal('descuento_porcentaje', 5, 2)->nullable()->after('discount');
            // completo | solo_total | sin_precios
            $table->string('detalle_al_enviar', 15)->default('completo')->after('descuento_porcentaje');
            $table->longText('firma_paciente')->nullable()->after('accepted_ip');
            $table->string('firmado_por', 120)->nullable()->after('firma_paciente');
        });
    }

    public function down(): void
    {
        Schema::table('treatment_plans', function (Blueprint $table) {
            $table->dropColumn(['descuento_porcentaje', 'detalle_al_enviar', 'firma_paciente', 'firmado_por']);
        });
        Schema::table('treatment_plan_items', function (Blueprint $table) {
            $table->dropColumn(['etapa', 'opcional']);
            $table->string('tooth_number', 10)->nullable()->change();
        });
    }
};
