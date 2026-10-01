<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Dos datos que faltaban para saber en qué va la prospección.
 *
 * `replied_at`: hoy no existe manera de saber cuántos contestaron. Está en las
 * notas, a mano, cuando alguien se acordó de anotarlo. Sin este dato no se
 * puede medir si un mensaje funciona mejor que otro, que es de lo único que
 * depende toda la campaña.
 *
 * `has_whatsapp` pasa a aceptar nulo. Nació como booleano con default `false`,
 * así que no distingue "verificamos y no tiene WhatsApp" de "nunca lo
 * verificamos". Los CSV de junio lo pusieron en 1 a todos por defecto: de los
 * 74 que en Los Mochis traen la palomita, solo 10 se verificaron de verdad, y
 * de una muestra de esos lotes 9 de cada 10 no existían. Con el nulo, la cola
 * del día puede exigir verificación en vez de confiar en un default.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('prospects', function (Blueprint $table) {
            $table->timestamp('replied_at')->nullable()->after('outreach_started_at');
            $table->boolean('has_whatsapp')->nullable()->default(null)->change();
        });
    }

    public function down(): void
    {
        Schema::table('prospects', function (Blueprint $table) {
            $table->dropColumn('replied_at');
        });

        // `has_whatsapp` se queda nullable: volver a poner el default en false
        // convertiría los "no sabemos" en "no tiene", que es justo la confusión
        // que esta migración vino a quitar.
    }
};
