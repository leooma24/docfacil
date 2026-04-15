<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('commissions', function (Blueprint $table) {
            // Ciclo de la venta que generó la comisión
            $table->string('billing_cycle', 10)->default('monthly')->after('plan_at_sale'); // 'monthly' | 'annual'

            // Método de pago usado en la venta
            $table->string('payment_method', 20)->default('stripe')->after('billing_cycle'); // 'stripe' | 'spei'

            // Tipo de payout al vendedor (derivado de billing_cycle)
            // - 'split' = 50% primer pago + 50% segundo pago (mensual)
            // - 'lump_sum' = 100% en un solo pago (anual)
            $table->string('payout_type', 15)->default('split')->after('payment_method');
        });
    }

    public function down(): void
    {
        Schema::table('commissions', function (Blueprint $table) {
            $table->dropColumn(['billing_cycle', 'payment_method', 'payout_type']);
        });
    }
};
