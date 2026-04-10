<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            return; // SQLite doesn't support ENUM; column already accepts strings
        }
        DB::statement("ALTER TABLE prospects MODIFY COLUMN source ENUM('landing', 'referral', 'google', 'social', 'other', 'prospecting') DEFAULT 'landing'");
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            return;
        }
        DB::statement("ALTER TABLE prospects MODIFY COLUMN source ENUM('landing', 'referral', 'google', 'social', 'other') DEFAULT 'landing'");
    }
};
