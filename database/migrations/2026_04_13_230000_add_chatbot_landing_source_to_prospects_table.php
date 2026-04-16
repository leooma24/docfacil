<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // SQLite (tests) no soporta MODIFY COLUMN ENUM — skip, ahí la columna es texto libre.
        if (DB::connection()->getDriverName() !== 'mysql') {
            return;
        }
        DB::statement("ALTER TABLE prospects MODIFY COLUMN source ENUM('landing', 'referral', 'google', 'social', 'other', 'prospecting', 'chatbot_landing') DEFAULT 'landing'");
    }

    public function down(): void
    {
        if (DB::connection()->getDriverName() !== 'mysql') {
            return;
        }
        DB::statement("ALTER TABLE prospects MODIFY COLUMN source ENUM('landing', 'referral', 'google', 'social', 'other', 'prospecting') DEFAULT 'landing'");
    }
};
