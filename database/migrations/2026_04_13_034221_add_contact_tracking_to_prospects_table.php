<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'sqlite' && !Schema::hasColumn('prospects', 'contact_day')) {
            // SQLite: columns may already exist from test refresh
        }

        Schema::table('prospects', function (Blueprint $table) {
            if (!Schema::hasColumn('prospects', 'contact_day')) {
                $table->unsignedTinyInteger('contact_day')->default(0)->after('status')
                    ->comment('Current day in outreach cadence: 0,1,3,7,14,30');
            }
            if (!Schema::hasColumn('prospects', 'last_contact_method')) {
                $table->string('last_contact_method', 20)->nullable()->after('contact_day')
                    ->comment('whatsapp, email, phone, in_person, demo');
            }
            if (!Schema::hasColumn('prospects', 'next_contact_at')) {
                $table->timestamp('next_contact_at')->nullable()->after('last_contact_method');
            }
            if (!Schema::hasColumn('prospects', 'outreach_started_at')) {
                $table->timestamp('outreach_started_at')->nullable()->after('next_contact_at')
                    ->comment('When the outreach cadence started');
            }
        });
    }

    public function down(): void
    {
        Schema::table('prospects', function (Blueprint $table) {
            $table->dropColumn(['contact_day', 'last_contact_method', 'next_contact_at', 'outreach_started_at']);
        });
    }
};
