<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('prospects', function (Blueprint $table) {
            $table->string('website')->nullable()->after('phone');
            $table->boolean('has_whatsapp')->default(false)->after('website');
            $table->string('osm_id')->nullable()->unique()->after('has_whatsapp');
            $table->decimal('latitude', 10, 7)->nullable()->after('osm_id');
            $table->decimal('longitude', 10, 7)->nullable()->after('latitude');
        });

        // Make email nullable (scraped clinics often don't publish email).
        if (DB::getDriverName() === 'sqlite') {
            return;
        }
        DB::statement('ALTER TABLE prospects MODIFY COLUMN email VARCHAR(255) NULL');
    }

    public function down(): void
    {
        Schema::table('prospects', function (Blueprint $table) {
            $table->dropUnique(['osm_id']);
            $table->dropColumn(['website', 'has_whatsapp', 'osm_id', 'latitude', 'longitude']);
        });

        if (DB::getDriverName() === 'sqlite') {
            return;
        }
        DB::statement('ALTER TABLE prospects MODIFY COLUMN email VARCHAR(255) NOT NULL');
    }
};
