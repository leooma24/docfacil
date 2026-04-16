<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('premium_service_purchases', function (Blueprint $table) {
            // Para servicios recurrentes (pricing_type=monthly) Stripe crea una Subscription.
            // Guardamos el sub_xxx para poder reaccionar a customer.subscription.deleted.
            $table->string('stripe_subscription_id')->nullable()->after('stripe_session_id')->index();
        });
    }

    public function down(): void
    {
        Schema::table('premium_service_purchases', function (Blueprint $table) {
            $table->dropColumn('stripe_subscription_id');
        });
    }
};
