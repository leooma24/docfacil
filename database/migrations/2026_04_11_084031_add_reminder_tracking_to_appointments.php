<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            if (!Schema::hasColumn('appointments', 'reminder_24h_sent_at')) {
                $table->timestamp('reminder_24h_sent_at')->nullable()->after('reminder_sent');
            }
            if (!Schema::hasColumn('appointments', 'reminder_2h_sent_at')) {
                $table->timestamp('reminder_2h_sent_at')->nullable()->after('reminder_24h_sent_at');
            }
            if (!Schema::hasColumn('appointments', 'followup_sent_at')) {
                $table->timestamp('followup_sent_at')->nullable()->after('reminder_2h_sent_at');
            }
            if (!Schema::hasColumn('appointments', 'confirmed_at')) {
                $table->timestamp('confirmed_at')->nullable()->after('followup_sent_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->dropColumn(['reminder_24h_sent_at', 'reminder_2h_sent_at', 'followup_sent_at', 'confirmed_at']);
        });
    }
};
