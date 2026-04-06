<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE prospects MODIFY COLUMN source ENUM('landing', 'referral', 'google', 'social', 'other', 'prospecting') DEFAULT 'landing'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE prospects MODIFY COLUMN source ENUM('landing', 'referral', 'google', 'social', 'other') DEFAULT 'landing'");
    }
};
