<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('prospects', function (Blueprint $table) {
            if (!Schema::hasColumn('prospects', 'objections_faced')) {
                $table->json('objections_faced')->nullable()->after('notes')
                    ->comment('Array of objection keys faced during sales process');
            }
            if (!Schema::hasColumn('prospects', 'demo_scheduled_at')) {
                $table->timestamp('demo_scheduled_at')->nullable()->after('objections_faced');
            }
            if (!Schema::hasColumn('prospects', 'demo_completed_at')) {
                $table->timestamp('demo_completed_at')->nullable()->after('demo_scheduled_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('prospects', function (Blueprint $table) {
            $table->dropColumn(['objections_faced', 'demo_scheduled_at', 'demo_completed_at']);
        });
    }
};
