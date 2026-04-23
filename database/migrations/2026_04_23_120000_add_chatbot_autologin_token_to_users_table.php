<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('chatbot_autologin_token', 64)->nullable()->unique()->after('remember_token');
            $table->timestamp('chatbot_autologin_expires_at')->nullable()->after('chatbot_autologin_token');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['chatbot_autologin_token', 'chatbot_autologin_expires_at']);
        });
    }
};
