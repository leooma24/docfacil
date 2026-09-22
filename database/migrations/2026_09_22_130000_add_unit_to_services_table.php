<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * La unidad de cobro del servicio.
 *
 * Hasta ahora un servicio solo tenía precio, así que "Curetaje (por cuadrante)"
 * a $800 se cobraba una vez aunque el doctor hiciera dos cuadrantes. La pantalla
 * no sabía que el precio era por cuadrante.
 *
 * El default es `visit` a propósito: es como se comportaban los servicios que
 * ya existen (una línea, un cobro). Poner `tooth` cambiaría el significado de
 * los precios que los doctores ya tienen capturados.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $table->string('unit', 20)->default('visit')->after('price');
        });
    }

    public function down(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $table->dropColumn('unit');
        });
    }
};
