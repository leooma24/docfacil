<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Infraestructura del marketplace de add-ons (Fase 4 W3).
 *
 * Cada clinica puede activar N add-ons independientes del plan base.
 * El precio y feature-flag del add-on vive en config/addons.php
 * (source of truth unica). Esta tabla solo registra el estado de
 * activacion por clinica.
 *
 * Stripe: cuando la infra de billing este lista, stripe_subscription_
 * item_id apuntara al line item dentro de la subscription principal
 * de la clinica. Mientras esta en NULL, el addon esta "beta testing"
 * (activacion manual sin cobro).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clinic_addons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('clinic_id')->constrained()->cascadeOnDelete();
            $table->string('addon_slug', 50); // ej. 'recall_automation', 'treatment_plans'
            $table->string('status', 20)->default('trial'); // trial, active, past_due, cancelled
            $table->decimal('monthly_price', 10, 2);
            $table->string('billing_cycle', 10)->default('monthly'); // monthly, annual
            $table->timestamp('trial_ends_at')->nullable();
            $table->timestamp('started_at');
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamp('ends_at')->nullable(); // si cancelan, cuando pierden acceso
            $table->string('stripe_subscription_item_id')->nullable(); // Stripe integration futura
            $table->timestamps();

            $table->unique(['clinic_id', 'addon_slug']); // una clinica no puede tener duplicado
            $table->index(['clinic_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clinic_addons');
    }
};
