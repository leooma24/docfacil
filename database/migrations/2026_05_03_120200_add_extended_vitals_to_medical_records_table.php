<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('medical_records', function (Blueprint $table) {
            $table->unsignedSmallInteger('respiratory_rate')->nullable()->after('vital_signs');
            $table->unsignedTinyInteger('oxygen_saturation')->nullable()->after('respiratory_rate');
            $table->decimal('height', 5, 2)->nullable()->after('oxygen_saturation');
            $table->decimal('head_circumference', 5, 2)->nullable()->after('height');
            $table->json('cie10_codes')->nullable()->after('head_circumference');
        });
    }

    public function down(): void
    {
        Schema::table('medical_records', function (Blueprint $table) {
            $table->dropColumn([
                'respiratory_rate',
                'oxygen_saturation',
                'height',
                'head_circumference',
                'cie10_codes',
            ]);
        });
    }
};
