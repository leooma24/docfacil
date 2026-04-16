<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Catálogo de servicios premium de DocFácil (setup, capacitación, branding, etc.)
        // que se venden a las clínicas como addon del SaaS. No confundir con "services"
        // (servicios del consultorio del doctor ofrecidos a sus pacientes).
        Schema::create('premium_services', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('name');
            $table->string('category', 30); // setup | capacitacion | branding | whatsapp | legal | marketing

            $table->decimal('price_mxn', 10, 2);
            $table->string('pricing_type', 20)->default('one_time'); // one_time | monthly | custom_quote
            $table->unsignedInteger('sla_days')->default(3);

            $table->string('short_desc');
            $table->text('long_desc')->nullable();
            $table->text('bullets')->nullable();

            $table->string('icon_svg_path')->nullable();
            $table->string('target_audience', 20)->default('all');

            $table->boolean('is_active')->default(true);
            $table->boolean('is_featured')->default(false);
            $table->unsignedInteger('sort_order')->default(0);

            $table->boolean('requires_intake')->default(false);
            $table->json('intake_form_schema')->nullable();

            $table->unsignedTinyInteger('seller_commission_pct')->default(20);

            $table->timestamps();

            $table->index(['is_active', 'category']);
            $table->index(['target_audience', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('premium_services');
    }
};
