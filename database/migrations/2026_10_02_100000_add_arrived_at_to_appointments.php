<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// La hora en que el paciente llegó a la sala de espera (por el QR o porque
// recepción lo marcó). Sin esto la cita no sabía que ya estaba ahí.
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->timestamp('arrived_at')->nullable()->after('confirmed_at');
        });
    }

    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->dropColumn('arrived_at');
        });
    }
};
