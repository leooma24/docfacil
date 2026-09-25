<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Cuántas veces has visto cada tip, y cuáles ya te salen solos.
 *
 * Omar lo pidió así: no quiere leer una lista de veinticinco consejos, quiere
 * que el mismo le aparezca hasta que le salga natural. Para eso hay que
 * guardar dos cosas por persona: cuántas veces lo ha visto y si ya lo dio por
 * dominado.
 *
 * El que se marca dominado deja de estorbar, pero no se borra: a las dos
 * semanas vuelve una vez, porque lo que no se practica se olvida. Si sigue
 * saliendo natural, se vuelve a marcar y ya.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tips_de_venta', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            // La clave del tip en TipsDeVenta::CATALOGO. Se guarda la clave y
            // no el texto: el texto se va a corregir con el tiempo, y lo que
            // no puede cambiar es de cuál se llevaba la cuenta.
            $table->string('clave', 40);

            $table->unsignedSmallInteger('veces_visto')->default(0);
            $table->timestamp('visto_at')->nullable();
            $table->timestamp('dominado_at')->nullable();

            $table->timestamps();

            $table->unique(['user_id', 'clave']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tips_de_venta');
    }
};
