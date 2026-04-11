<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clinics', function (Blueprint $table) {
            if (!Schema::hasColumn('clinics', 'is_demo')) {
                $table->boolean('is_demo')->default(false)->after('is_active');
            }
            if (!Schema::hasColumn('clinics', 'demo_expires_at')) {
                $table->timestamp('demo_expires_at')->nullable()->after('is_demo');
            }
        });
    }

    public function down(): void
    {
        Schema::table('clinics', function (Blueprint $table) {
            $table->dropColumn(['is_demo', 'demo_expires_at']);
        });
    }
};
