<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Quién responde por el paciente: la mamá del niño, el papá que paga por sus
 * tres hijos (10-oct-2026). A su WhatsApp llegan los mensajes y con ella se
 * suma lo que debe la familia. Si se borra al responsable, el niño se queda.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('patients', function (Blueprint $table) {
            $table->foreignId('responsable_id')->nullable()->after('clinic_id')->constrained('patients')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('patients', function (Blueprint $table) {
            $table->dropConstrainedForeignId('responsable_id');
        });
    }
};
