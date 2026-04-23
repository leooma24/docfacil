<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('patients', function (Blueprint $table) {
            // Year of the last birthday greeting sent (for idempotency — avoid
            // resending if the scheduler fires twice on the same day).
            $table->smallInteger('last_birthday_greeting_year')->nullable()->after('birth_date');
        });
    }

    public function down(): void
    {
        Schema::table('patients', function (Blueprint $table) {
            $table->dropColumn('last_birthday_greeting_year');
        });
    }
};
