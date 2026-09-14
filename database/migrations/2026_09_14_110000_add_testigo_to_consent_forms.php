<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * El testigo del consentimiento.
 *
 * La NOM-013 (9.6.9.11) pide que la carta de consentimiento dental lleve
 * nombre completo y firma del estomatólogo, del paciente y de un testigo.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('consent_forms', function (Blueprint $table) {
            $table->string('testigo_nombre', 150)->nullable()->after('signed_ip');
            $table->longText('testigo_firma')->nullable()->after('testigo_nombre');
        });
    }

    public function down(): void
    {
        Schema::table('consent_forms', function (Blueprint $table) {
            $table->dropColumn(['testigo_nombre', 'testigo_firma']);
        });
    }
};
