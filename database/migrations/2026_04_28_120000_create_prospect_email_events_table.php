<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Eventos de tracking en correos del pipeline de prospects.
 * Por ahora solo registramos clicks (la opcion mas confiable — opens
 * vis pixel son bloqueados por Apple Mail Privacy Protection 2021+).
 *
 * Cuando un prospect da clic al CTA del correo:
 *   1. Pasa por /t/c/{token} con HMAC firmado
 *   2. TrackController valida token + crea evento aqui
 *   3. Redirige a destino real (ej. /dentistas o /register)
 *
 * Permite saber quienes son los leads mas calientes para priorizar
 * follow-up manual del rep.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('prospect_email_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('prospect_id')->constrained()->cascadeOnDelete();
            $table->string('email_type', 30); // beta_invite | followup | last_chance
            $table->string('event_type', 20)->default('click'); // click | open (futuro)
            $table->string('destination_url', 500)->nullable();
            $table->string('ip', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();

            $table->index(['prospect_id', 'event_type']);
            $table->index(['email_type', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('prospect_email_events');
    }
};
