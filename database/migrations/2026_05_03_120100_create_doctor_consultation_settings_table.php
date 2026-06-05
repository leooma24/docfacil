<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('doctor_consultation_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('doctor_id')->unique()->constrained()->cascadeOnDelete();
            $table->json('enabled_fields')->nullable();
            $table->boolean('inherits_clinic_config')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('doctor_consultation_settings');
    }
};
