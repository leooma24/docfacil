<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * La frase que deja un fundador a los 30 días de usar DocFácil.
 *
 * La frase va en case_study_testimonial, que ya existía. Aquí va lo que
 * faltaba para usarla sin inventar nada: cómo quiere firmarla y, sobre todo,
 * cuándo dio permiso de publicarla. Sin esa fecha no se publica.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clinics', function (Blueprint $table) {
            $table->string('testimonio_firma')->nullable()->after('case_study_testimonial');
            $table->timestamp('testimonio_permiso_at')->nullable()->after('testimonio_firma');
            $table->timestamp('testimonio_pospuesto_hasta')->nullable()->after('testimonio_permiso_at');
            $table->timestamp('testimonio_descartado_at')->nullable()->after('testimonio_pospuesto_hasta');
        });
    }

    public function down(): void
    {
        Schema::table('clinics', function (Blueprint $table) {
            $table->dropColumn([
                'testimonio_firma',
                'testimonio_permiso_at',
                'testimonio_pospuesto_hasta',
                'testimonio_descartado_at',
            ]);
        });
    }
};
