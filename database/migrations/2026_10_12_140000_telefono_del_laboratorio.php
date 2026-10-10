<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * El WhatsApp del laboratorio, para preguntarle "¿cómo va?" a un toque
 * (12-oct-2026). Se captura una vez; la siguiente orden al mismo laboratorio
 * lo trae sola.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lab_orders', function (Blueprint $table) {
            $table->string('telefono_laboratorio', 30)->nullable()->after('laboratorio');
        });
    }

    public function down(): void
    {
        Schema::table('lab_orders', fn (Blueprint $table) => $table->dropColumn('telefono_laboratorio'));
    }
};
