<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * El motivo de la merma, para poder deducirla.
 *
 * Los dentistas deducen fiscalmente sus mermas como pérdidas permitidas por la
 * ley, y para eso el contador necesita sumar POR CAUSA: "merma del mes por
 * caducidad" no se puede sacar de un campo de texto libre. De ahí que el motivo
 * sea una categoría y no una nota — la nota sigue existiendo aparte, en
 * `reason`.
 *
 * Ojo con lo que NO está aquí: la merma normativa. El sobrante de amalgama y
 * los restos extraídos no son un movimiento de inventario — ese material ya
 * salió cuando se mezcló —, así que registrarlos como salida descontaría dos
 * veces lo mismo. Son un registro de cumplimiento (Convenio de Minamata), no
 * un kardex, y merecen su propia pieza.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('supply_movements', function (Blueprint $table) {
            $table->string('waste_reason', 30)->nullable()->after('reason');
        });
    }

    public function down(): void
    {
        Schema::table('supply_movements', function (Blueprint $table) {
            $table->dropColumn('waste_reason');
        });
    }
};
