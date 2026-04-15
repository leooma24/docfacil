<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('clinics', function (Blueprint $table) {
            // Stripe (Laravel Cashier necesita estas columnas)
            $table->string('stripe_id')->nullable()->after('plan')->index();
            $table->string('pm_type')->nullable()->after('stripe_id');
            $table->string('pm_last_four', 4)->nullable()->after('pm_type');

            // Estado de la suscripción y ciclo (aplica a Stripe Y a SPEI)
            $table->string('billing_cycle', 10)->nullable()->after('pm_last_four'); // 'monthly' | 'annual'
            $table->string('payment_method', 20)->nullable()->after('billing_cycle'); // 'stripe' | 'spei'
            $table->timestamp('plan_started_at')->nullable()->after('payment_method');
            $table->timestamp('plan_ends_at')->nullable()->after('plan_started_at');
            $table->boolean('auto_renew')->default(true)->after('plan_ends_at');
        });
    }

    public function down(): void
    {
        Schema::table('clinics', function (Blueprint $table) {
            $table->dropColumn([
                'stripe_id', 'pm_type', 'pm_last_four',
                'billing_cycle', 'payment_method',
                'plan_started_at', 'plan_ends_at', 'auto_renew',
            ]);
        });
    }
};
