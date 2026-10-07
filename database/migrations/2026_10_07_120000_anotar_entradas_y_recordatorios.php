<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Cuándo entró cada usuario y cuándo se mandó cada recordatorio.
 *
 * Al 7-oct-2026 no había forma de saber si un doctor que se registró llegó a
 * entrar, ni cuándo usó el botón de recordatorio (solo había un sí/no). Con
 * esto el embudo de ventas sigue después de "Cerraron": entró, dejó su
 * consultorio listo, lo usa esta semana.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dateTime('last_login_at')->nullable()->after('remember_token');
        });

        Schema::table('appointments', function (Blueprint $table) {
            $table->dateTime('reminder_sent_at')->nullable()->after('reminder_sent');
        });
    }

    public function down(): void
    {
        Schema::table('users', fn (Blueprint $table) => $table->dropColumn('last_login_at'));
        Schema::table('appointments', fn (Blueprint $table) => $table->dropColumn('reminder_sent_at'));
    }
};
