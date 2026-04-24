<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Referral cascading — el referente gana 1 mes gratis cada vez que
 * el referido paga, hasta un maximo de 12 meses (1 año de servicio).
 *
 * cascade_rewards_granted: contador de cuantos meses ya se le han dado
 * last_cascade_reward_at: evita doble-counting si un webhook dispara 2x
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('referrals', function (Blueprint $table) {
            $table->unsignedSmallInteger('cascade_rewards_granted')->default(0)->after('rewarded_at');
            $table->timestamp('last_cascade_reward_at')->nullable()->after('cascade_rewards_granted');
        });
    }

    public function down(): void
    {
        Schema::table('referrals', function (Blueprint $table) {
            $table->dropColumn(['cascade_rewards_granted', 'last_cascade_reward_at']);
        });
    }
};
