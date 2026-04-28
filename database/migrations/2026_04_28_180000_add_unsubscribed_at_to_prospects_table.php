<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('prospects', function (Blueprint $table) {
            $table->timestamp('unsubscribed_at')->nullable()->after('next_contact_at');
            $table->index('unsubscribed_at');
        });
    }

    public function down(): void
    {
        Schema::table('prospects', function (Blueprint $table) {
            $table->dropIndex(['unsubscribed_at']);
            $table->dropColumn('unsubscribed_at');
        });
    }
};
